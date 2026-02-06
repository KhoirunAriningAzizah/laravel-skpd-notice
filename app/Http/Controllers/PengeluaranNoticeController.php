<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanNotice;
use App\Models\PengeluaranNotice;
use App\Models\PengeluaranPemakaianRange;
use App\Models\PengeluaranBatalRusak;
use App\Models\PengeluaranBuktiKas;
use App\Models\SaldoNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengeluaranNoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Kasir can only see their own pengeluaran
        $pengeluaranNotices = PengeluaranNotice::with(['penerimaan', 'lokasi', 'creator'])
            ->where('created_by', Auth::id())
            ->latest('tanggal')
            ->paginate(10);

        return view('layouts.pengeluaran-notices.index', compact('pengeluaranNotices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $penerimaanId = $request->query('penerimaan_id');

        if (!$penerimaanId) {
            return redirect()->route('penerimaan-notices.index')
                ->with('error', 'ID Penerimaan tidak ditemukan.');
        }

        $penerimaanNotice = PenerimaanNotice::with(['lokasi', 'saldo'])
            ->where('id', $penerimaanId)
            ->where('created_by', Auth::id())
            ->first();

        if (!$penerimaanNotice) {
            abort(403, 'Unauthorized action.');
        }

        // Validasi: Kasir tidak bisa menambah pengeluaran jika saldo sudah 0
        if (!$penerimaanNotice->hasSaldoRemaining()) {
            return redirect()->route('penerimaan-notices.show', $penerimaanNotice->id)
                ->with('error', 'Tidak dapat menambah pengeluaran. Saldo penerimaan sudah habis (0).');
        }

        // Validasi: Kasir hanya bisa maksimal 2 pengeluaran per hari untuk penerimaan yang sama
        $todayDate = now()->format('Y-m-d');
        $todayPengeluaranCount = PengeluaranNotice::countTodayPengeluaranByUser(Auth::id(), $todayDate, $penerimaanNotice->id);
        if ($todayPengeluaranCount >= 2) {
            return redirect()->route('penerimaan-notices.show', $penerimaanNotice->id)
                ->with('error', 'Tidak dapat menambah pengeluaran. Anda sudah mencapai batas maksimal 2 pengeluaran per hari untuk penerimaan ini.');
        }

        // Get available saldo
        $saldoNotices = $penerimaanNotice->saldo()->get();

        return view('layouts.pengeluaran-notices.create', compact('penerimaanNotice', 'saldoNotices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'penerimaan_id' => ['required', 'exists:penerimaan_notices,id'],
            'tanggal' => ['required', 'date'],

            // Pemakaian section
            'pemakaian' => ['required', 'array', 'min:1'],
            'pemakaian.*.nomor_awal' => ['required', 'integer', 'min:1'],
            'pemakaian.*.nomor_akhir' => ['required', 'integer', 'min:1'],

            // Batal/Rusak section
            'batal_rusak' => ['nullable', 'array'],
            'batal_rusak.*' => ['integer', 'min:1'],

            // Bukti Kas section
            'lokal' => ['required', 'integer', 'min:0'],
            'link' => ['required', 'integer', 'min:0'],

            // Saldo section
            'saldo_nomor_awal' => ['required', 'integer', 'min:1'],
            'saldo_nomor_akhir' => ['required', 'integer', 'min:1'],
            'saldo_jumlah' => ['required', 'integer', 'min:0'],
        ]);

        // Check ownership of penerimaan
        $penerimaanNotice = PenerimaanNotice::where('id', $validated['penerimaan_id'])
            ->where('created_by', Auth::id())
            ->firstOrFail();

        // Validasi: Kasir tidak bisa menambah pengeluaran jika saldo sudah 0
        if (!$penerimaanNotice->hasSaldoRemaining()) {
            return redirect()->route('penerimaan-notices.show', $penerimaanNotice->id)
                ->with('error', 'Tidak dapat menambah pengeluaran. Saldo penerimaan sudah habis (0).');
        }

        // Validasi: Kasir hanya bisa maksimal 2 pengeluaran per hari untuk penerimaan yang sama
        $todayDate = now()->format('Y-m-d');
        $todayPengeluaranCount = PengeluaranNotice::countTodayPengeluaranByUser(Auth::id(), $todayDate, $penerimaanNotice->id);
        if ($todayPengeluaranCount >= 2) {
            return redirect()->route('penerimaan-notices.show', $penerimaanNotice->id)
                ->with('error', 'Tidak dapat menambah pengeluaran. Anda sudah mencapai batas maksimal 2 pengeluaran per hari untuk penerimaan ini.');
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $totalPemakaian = 0;
            foreach ($validated['pemakaian'] as $pemakaian) {
                $jumlah = $pemakaian['nomor_akhir'] - $pemakaian['nomor_awal'] + 1;

                // Validate that nomor_akhir >= nomor_awal
                if ($jumlah < 1) {
                    return back()->withInput()->with('error', 'Nomor akhir harus lebih besar atau sama dengan nomor awal.');
                }

                $totalPemakaian += $jumlah;
            }

            $totalBatalRusak = isset($validated['batal_rusak']) ? count($validated['batal_rusak']) : 0;
            $totalBuktiKas = $validated['lokal'] + $validated['link'];

            // Validate that bukti kas equals to total pemakaian raw (including batal/rusak)
            if ($totalBuktiKas != $totalPemakaian) {
                return back()->withInput()->with(
                    'error',
                    "Total Bukti Kas ($totalBuktiKas) harus sama dengan total pemakaian ($totalPemakaian) yang termasuk $totalBatalRusak Batal/Rusak"
                );
            }

            // Validate numbers are within penerimaan range
            foreach ($validated['pemakaian'] as $pemakaian) {
                if (
                    $pemakaian['nomor_awal'] < $penerimaanNotice->nomor_awal ||
                    $pemakaian['nomor_akhir'] > $penerimaanNotice->nomor_akhir
                ) {
                    return back()->withInput()->with(
                        'error',
                        'Nomor notice pemakaian harus dalam range ' .
                            number_format($penerimaanNotice->nomor_awal, 0, ',', '.') . ' - ' .
                            number_format($penerimaanNotice->nomor_akhir, 0, ',', '.')
                    );
                }
            }

            // Validate batal/rusak numbers
            if (isset($validated['batal_rusak'])) {
                foreach ($validated['batal_rusak'] as $nomorBatal) {
                    if (
                        $nomorBatal < $penerimaanNotice->nomor_awal ||
                        $nomorBatal > $penerimaanNotice->nomor_akhir
                    ) {
                        return back()->withInput()->with(
                            'error',
                            "Nomor notice batal/rusak ($nomorBatal) harus dalam range penerimaan."
                        );
                    }
                }
            }

            // Create pengeluaran notice
            $pengeluaran = PengeluaranNotice::create([
                'penerimaan_id' => $validated['penerimaan_id'],
                'tanggal' => $validated['tanggal'],
                'jumlah_total' => $totalPemakaian,
                'lokasi_id' => $penerimaanNotice->lokasi_id,
                'created_by' => Auth::id(),
            ]);

            // Create pemakaian ranges
            $jumlahPemakaianBersih = $totalPemakaian - $totalBatalRusak;

            foreach ($validated['pemakaian'] as $pemakaian) {
                PengeluaranPemakaianRange::create([
                    'pengeluaran_id' => $pengeluaran->id,
                    'nomor_awal' => $pemakaian['nomor_awal'],
                    'nomor_akhir' => $pemakaian['nomor_akhir'],
                    'jumlah' => $jumlahPemakaianBersih,
                ]);
            }

            // Create batal/rusak entries
            if (isset($validated['batal_rusak']) && count($validated['batal_rusak']) > 0) {
                foreach ($validated['batal_rusak'] as $nomorBatal) {
                    PengeluaranBatalRusak::create([
                        'pengeluaran_id' => $pengeluaran->id,
                        'nomor_notice' => $nomorBatal,
                    ]);
                }
            }

            // Create bukti kas
            PengeluaranBuktiKas::create([
                'pengeluaran_id' => $pengeluaran->id,
                'lokal' => $validated['lokal'],
                'link' => $validated['link'],
                'jumlah' => $totalBuktiKas,
            ]);

            // Create or update saldo notice
            // Delete existing saldo for this penerimaan that has no pengeluaran_id
            SaldoNotice::where('penerimaan_id', $validated['penerimaan_id'])
                ->whereNull('pengeluaran_id')
                ->delete();

            // Create new saldo for this pengeluaran if there's remaining notices
            if ($validated['saldo_jumlah'] > 0) {
                SaldoNotice::create([
                    'penerimaan_id' => $validated['penerimaan_id'],
                    'pengeluaran_id' => $pengeluaran->id,
                    'nomor_awal' => $validated['saldo_nomor_awal'],
                    'nomor_akhir' => $validated['saldo_nomor_akhir'],
                    'jumlah' => $validated['saldo_jumlah'],
                ]);
            }

            DB::commit();

            return redirect()->route('penerimaan-notices.show', $validated['penerimaan_id'])
                ->with('success', 'Pengeluaran notice berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
