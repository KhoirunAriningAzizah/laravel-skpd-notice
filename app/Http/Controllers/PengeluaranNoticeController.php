<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use App\Models\PenerimaanNotice;
use App\Models\PengeluaranNotice;
use App\Models\PengeluaranPemakaianRange;
use App\Models\PengeluaranBatalRusak;
use App\Models\PengeluaranBuktiKas;
use App\Models\SaldoNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengeluaranNoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PengeluaranNotice::with(['penerimaan', 'lokasi', 'creator', 'pemakaianRanges', 'batalRusak', 'buktiKas']);
        $isReadOnly = false;
        $kasirList = [];

        // Role-based filtering
        if (Auth::user()->role == 'kasir') {
            // Kasir can only see their own pengeluaran
            $query->where('created_by', Auth::id());
        } elseif (Auth::user()->role == 'admin') {
            // Admin can see all pengeluaran from kasir with same layanan
            $isReadOnly = false;
            $adminLayanan = Auth::user()->layanan;

            if ($adminLayanan) {
                // Get all kasir users with same layanan
                $kasirList = \App\Models\User::where('role', 'kasir')
                    ->where('layanan_id', $adminLayanan->id)
                    ->get();

                $kasirIds = $kasirList->pluck('id')->toArray();
                $query->whereIn('created_by', $kasirIds);

                // Filter by specific kasir if selected
                if ($request->has('kasir_id') && $request->kasir_id != '') {
                    $query->where('created_by', $request->kasir_id);
                }
            } else {
                // Admin has no layanan, show nothing
                $query->whereRaw('1 = 0');
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('penerimaan', function ($q) use ($search) {
                    $q->where('nomor_awal', 'like', '%' . $search . '%')
                        ->orWhere('nomor_akhir', 'like', '%' . $search . '%');
                })->orWhereHas('lokasi', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
            });
        }

        // Filter by date range if provided
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter by lokasi if provided
        if ($request->has('lokasi_id') && $request->lokasi_id != '') {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $pengeluaranNotices = $query->latest('tanggal')->paginate(10);

        // Get lokasi list based on role
        $lokasiList = collect();
        if (Auth::user()->role == 'admin' || Auth::user()->role == 'kasir') {
            $userLayananId = Auth::user()->layanan_id;
            if ($userLayananId) {
                $lokasiList = Lokasi::where('layanan_id', $userLayananId)
                    ->with('layanan')
                    ->orderBy('nama')
                    ->get();
            }
        }

        return view('layouts.pengeluaran-notices.index', compact('pengeluaranNotices', 'isReadOnly', 'kasirList', 'lokasiList'));
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

        // Get filtered lokasi by user's layanan_id
        $userLayananId = Auth::user()->layanan_id;
        $lokasis = Lokasi::where('layanan_id', $userLayananId)->get();

        return view('layouts.pengeluaran-notices.create', compact('penerimaanNotice', 'saldoNotices', 'lokasis'));
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

            // Validate that pemakaian ranges don't overlap with existing usage from other pengeluaran
            foreach ($validated['pemakaian'] as $pemakaian) {
                $existingUsage = PengeluaranPemakaianRange::whereHas('pengeluaran', function ($query) use ($penerimaanNotice) {
                    $query->where('penerimaan_id', $penerimaanNotice->id);
                })
                    ->where(function ($query) use ($pemakaian) {
                        // Check for any overlap: (existing_start <= new_end) AND (existing_end >= new_start)
                        $query->where('nomor_awal', '<=', $pemakaian['nomor_akhir'])
                            ->where('nomor_akhir', '>=', $pemakaian['nomor_awal']);
                    })
                    ->first();

                if ($existingUsage) {
                    Log::info('Duplicate range detected', [
                        'input_range' => $pemakaian['nomor_awal'] . '-' . $pemakaian['nomor_akhir'],
                        'existing_range' => $existingUsage->nomor_awal . '-' . $existingUsage->nomor_akhir
                    ]);

                    return back()->withInput()->with(
                        'error',
                        'Nomor notice ' . number_format($pemakaian['nomor_awal'], 0, ',', '.') .
                            ' - ' . number_format($pemakaian['nomor_akhir'], 0, ',', '.') .
                            ' sudah digunakan di pengeluaran lain. Silakan gunakan nomor yang belum terpakai.'
                    );
                }
            }

            // Validate that batal/rusak numbers aren't already marked in other pengeluaran
            if (isset($validated['batal_rusak'])) {
                foreach ($validated['batal_rusak'] as $nomorBatal) {
                    $existingBatal = PengeluaranBatalRusak::whereHas('pengeluaran', function ($query) use ($penerimaanNotice) {
                        $query->where('penerimaan_id', $penerimaanNotice->id);
                    })
                        ->where('nomor_notice', $nomorBatal)
                        ->exists();

                    if ($existingBatal) {
                        Log::info('Duplicate batal/rusak detected', [
                            'nomor_notice' => $nomorBatal
                        ]);

                        return back()->withInput()->with(
                            'error',
                            'Nomor notice ' . number_format($nomorBatal, 0, ',', '.') .
                                ' sudah ditandai batal/rusak di pengeluaran lain.'
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

    /**
     * Display the specified pengeluaran notice (For Admin).
     */
    public function show(PengeluaranNotice $pengeluaranNotice)
    {
        // Only admin can view details
        if (Auth::user()->role != 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Admin can only view pengeluaran from kasir with same layanan
        $adminLayanan = Auth::user()->layanan;
        if ($adminLayanan) {
            $pengeluaranCreator = $pengeluaranNotice->creator;
            if (!$pengeluaranCreator || $pengeluaranCreator->layanan_id != $adminLayanan->id) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }

        $pengeluaranNotice->load([
            'penerimaan',
            'lokasi',
            'creator',
            'pemakaianRanges',
            'batalRusak',
            'buktiKas'
        ]);

        $isReadOnly = false;

        return view('layouts.pengeluaran-notices.show', compact('pengeluaranNotice', 'isReadOnly'));
    }

    /**
     * Show the form for editing the specified pengeluaran notice (For Admin).
     */
    public function edit(PengeluaranNotice $pengeluaranNotice)
    {
        // Only admin can edit
        if (Auth::user()->role != 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Admin can only edit pengeluaran from kasir with same layanan
        $adminLayanan = Auth::user()->layanan;
        if ($adminLayanan) {
            $pengeluaranCreator = $pengeluaranNotice->creator;
            if (!$pengeluaranCreator || $pengeluaranCreator->layanan_id != $adminLayanan->id) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }

        $pengeluaranNotice->load([
            'penerimaan',
            'lokasi',
            'creator',
            'pemakaianRanges',
            'batalRusak',
            'buktiKas',
            'penerimaan.saldo'
        ]);

        // Get filtered lokasi by user's layanan_id
        $userLayananId = Auth::user()->layanan_id;
        $lokasis = Lokasi::where('layanan_id', $userLayananId)->get();

        return view('layouts.pengeluaran-notices.edit', compact('pengeluaranNotice', 'lokasis'));
    }

    /**
     * Update the specified pengeluaran notice in storage (For Admin).
     */
    public function update(Request $request, PengeluaranNotice $pengeluaranNotice)
    {
        // Only admin can update
        if (Auth::user()->role != 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Admin can only update pengeluaran from kasir with same layanan
        $adminLayanan = Auth::user()->layanan;
        if ($adminLayanan) {
            $pengeluaranCreator = $pengeluaranNotice->creator;
            if (!$pengeluaranCreator || $pengeluaranCreator->layanan_id != $adminLayanan->id) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],

            // Pemakaian section
            'pemakaian' => ['required', 'array', 'min:1'],
            'pemakaian.*.id' => ['nullable', 'exists:pengeluaran_pemakaian_ranges,id'],
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

        $penerimaanNotice = $pengeluaranNotice->penerimaan;

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

            // Validate that pemakaian ranges don't overlap with existing usage from OTHER pengeluaran
            foreach ($validated['pemakaian'] as $pemakaian) {
                $existingUsage = PengeluaranPemakaianRange::whereHas('pengeluaran', function ($query) use ($penerimaanNotice, $pengeluaranNotice) {
                    $query->where('penerimaan_id', $penerimaanNotice->id)
                        ->where('id', '!=', $pengeluaranNotice->id); // Exclude current pengeluaran
                })
                    ->where(function ($query) use ($pemakaian) {
                        // Check for any overlap: (existing_start <= new_end) AND (existing_end >= new_start)
                        $query->where('nomor_awal', '<=', $pemakaian['nomor_akhir'])
                            ->where('nomor_akhir', '>=', $pemakaian['nomor_awal']);
                    })
                    ->first();

                if ($existingUsage) {
                    Log::info('Duplicate range detected in update', [
                        'input_range' => $pemakaian['nomor_awal'] . '-' . $pemakaian['nomor_akhir'],
                        'existing_range' => $existingUsage->nomor_awal . '-' . $existingUsage->nomor_akhir,
                        'excluding_pengeluaran_id' => $pengeluaranNotice->id
                    ]);

                    return redirect()->back()->withInput()->with(
                        'error',
                        'Nomor notice ' . number_format($pemakaian['nomor_awal'], 0, ',', '.') .
                            ' - ' . number_format($pemakaian['nomor_akhir'], 0, ',', '.') .
                            ' sudah digunakan di pengeluaran lain. Silakan gunakan nomor yang belum terpakai.'
                    );
                }
            }

            // Validate that batal/rusak numbers aren't already marked in OTHER pengeluaran
            if (isset($validated['batal_rusak'])) {
                foreach ($validated['batal_rusak'] as $nomorBatal) {
                    $existingBatal = PengeluaranBatalRusak::whereHas('pengeluaran', function ($query) use ($penerimaanNotice, $pengeluaranNotice) {
                        $query->where('penerimaan_id', $penerimaanNotice->id)
                            ->where('id', '!=', $pengeluaranNotice->id); // Exclude current pengeluaran
                    })
                        ->where('nomor_notice', $nomorBatal)
                        ->exists();

                    if ($existingBatal) {
                        Log::info('Duplicate batal/rusak detected in update', [
                            'nomor_notice' => $nomorBatal,
                            'excluding_pengeluaran_id' => $pengeluaranNotice->id
                        ]);

                        return redirect()->back()->withInput()->with(
                            'error',
                            'Nomor notice ' . number_format($nomorBatal, 0, ',', '.') .
                                ' sudah ditandai batal/rusak di pengeluaran lain.'
                        );
                    }
                }
            }

            // Update pengeluaran notice
            $jumlahPemakaianBersih = $totalPemakaian - $totalBatalRusak;
            $pengeluaranNotice->update([
                'tanggal' => $validated['tanggal'],
                'jumlah_total' => $totalPemakaian,
            ]);

            // Delete existing pemakaian ranges and create new ones
            $pengeluaranNotice->pemakaianRanges()->delete();
            foreach ($validated['pemakaian'] as $pemakaian) {
                PengeluaranPemakaianRange::create([
                    'pengeluaran_id' => $pengeluaranNotice->id,
                    'nomor_awal' => $pemakaian['nomor_awal'],
                    'nomor_akhir' => $pemakaian['nomor_akhir'],
                    'jumlah' => $jumlahPemakaianBersih,
                ]);
            }

            // Delete existing batal/rusak and create new ones
            $pengeluaranNotice->batalRusak()->delete();
            if (isset($validated['batal_rusak']) && count($validated['batal_rusak']) > 0) {
                foreach ($validated['batal_rusak'] as $nomorBatal) {
                    PengeluaranBatalRusak::create([
                        'pengeluaran_id' => $pengeluaranNotice->id,
                        'nomor_notice' => $nomorBatal,
                    ]);
                }
            }

            // Update bukti kas
            $pengeluaranNotice->buktiKas()->updateOrCreate(
                ['pengeluaran_id' => $pengeluaranNotice->id],
                [
                    'lokal' => $validated['lokal'],
                    'link' => $validated['link'],
                    'jumlah' => $totalBuktiKas,
                ]
            );

            // Update saldo notice for this pengeluaran
            SaldoNotice::where('pengeluaran_id', $pengeluaranNotice->id)->delete();

            // Create new saldo for this pengeluaran if there's remaining notices
            if ($validated['saldo_jumlah'] > 0) {
                SaldoNotice::create([
                    'penerimaan_id' => $pengeluaranNotice->penerimaan_id,
                    'pengeluaran_id' => $pengeluaranNotice->id,
                    'nomor_awal' => $validated['saldo_nomor_awal'],
                    'nomor_akhir' => $validated['saldo_nomor_akhir'],
                    'jumlah' => $validated['saldo_jumlah'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.pengeluaran-notices.index')
                ->with('success', 'Pengeluaran notice berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified pengeluaran notice from storage (For Admin).
     */
    public function destroy(PengeluaranNotice $pengeluaranNotice)
    {
        // Only admin can delete
        if (Auth::user()->role != 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Admin can only delete pengeluaran from kasir with same layanan
        $adminLayanan = Auth::user()->layanan;
        if ($adminLayanan) {
            $pengeluaranCreator = $pengeluaranNotice->creator;
            if (!$pengeluaranCreator || $pengeluaranCreator->layanan_id != $adminLayanan->id) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $penerimaanId = $pengeluaranNotice->penerimaan_id;

            // Delete related records
            $pengeluaranNotice->pemakaianRanges()->delete();
            $pengeluaranNotice->batalRusak()->delete();
            $pengeluaranNotice->buktiKas()->delete();
            SaldoNotice::where('pengeluaran_id', $pengeluaranNotice->id)->delete();

            // Delete the pengeluaran
            $pengeluaranNotice->delete();

            // Recalculate saldo for penerimaan
            $penerimaan = PenerimaanNotice::find($penerimaanId);
            if ($penerimaan) {
                // Get the latest pengeluaran
                $latestPengeluaran = $penerimaan->pengeluaran()->latest('tanggal')->first();

                if ($latestPengeluaran) {
                    // Check if latest pengeluaran has saldo
                    $latestSaldo = SaldoNotice::where('pengeluaran_id', $latestPengeluaran->id)->first();

                    if (!$latestSaldo) {
                        // If no saldo exists for latest pengeluaran, create one with 0
                        SaldoNotice::create([
                            'penerimaan_id' => $penerimaanId,
                            'pengeluaran_id' => $latestPengeluaran->id,
                            'nomor_awal' => $penerimaan->nomor_awal,
                            'nomor_akhir' => $penerimaan->nomor_akhir,
                            'jumlah' => 0,
                        ]);
                    }
                } else {
                    // No pengeluaran left, restore full saldo to penerimaan
                    SaldoNotice::create([
                        'penerimaan_id' => $penerimaanId,
                        'pengeluaran_id' => null,
                        'nomor_awal' => $penerimaan->nomor_awal,
                        'nomor_akhir' => $penerimaan->nomor_akhir,
                        'jumlah' => $penerimaan->jumlah,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.pengeluaran-notices.index')
                ->with('success', 'Pengeluaran notice berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pengeluaran-notices.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
