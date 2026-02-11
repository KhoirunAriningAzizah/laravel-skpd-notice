<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanNotice;
use App\Models\Layanan;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\PenerimaanNoticeExport;
use Maatwebsite\Excel\Facades\Excel;

class PenerimaanNoticeController extends Controller
{
    /**
     * Display a listing of penerimaan notices.
     */
    public function index(Request $request)
    {
        $query = PenerimaanNotice::with(['lokasi', 'creator']);
        $isReadOnly = false;
        $kasirList = [];

        // Role-based filtering
        if (Auth::user()->role == 'kasir') {
            // Kasir: only see their own data
            $query->where('created_by', Auth::id());
        } elseif (Auth::user()->role == 'admin') {
            // Admin: read-only, see data from kasir with same layanan
            $isReadOnly = true;
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
                $q->where('nomor_awal', 'like', '%' . $search . '%')
                    ->orWhere('nomor_akhir', 'like', '%' . $search . '%')
                    ->orWhereHas('lokasi', function ($q) use ($search) {
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

        $penerimaans = $query->latest('tanggal')->paginate(10);

        // Get lokasi list based on role
        $lokasiList = collect();
        if (Auth::user()->role == 'superadmin') {
            // Superadmin: see all lokasi
            $lokasiList = Lokasi::with('layanan')->orderBy('nama')->get();
        } elseif (Auth::user()->role == 'admin' || Auth::user()->role == 'kasir') {
            // Admin/Kasir: only lokasi with same layanan_id
            $userLayananId = Auth::user()->layanan_id;
            if ($userLayananId) {
                $lokasiList = Lokasi::where('layanan_id', $userLayananId)
                    ->with('layanan')
                    ->orderBy('nama')
                    ->get();
            }
        }

        // Check if kasir can add new penerimaan (previous saldo must be 0)
        $canAddPenerimaan = true;
        $latestPenerimaanSaldo = 0;
        if (Auth::user()->role == 'kasir') {
            $latestPenerimaan = PenerimaanNotice::getLatestPenerimaanForUser(Auth::id());
            if ($latestPenerimaan && $latestPenerimaan->hasSaldoRemaining()) {
                $canAddPenerimaan = false;
                $latestPenerimaanSaldo = $latestPenerimaan->getTotalSaldo();
            }
        }

        // dd($penerimaans);

        return view('layouts.penerimaan-notices.index', compact('penerimaans', 'canAddPenerimaan', 'latestPenerimaanSaldo', 'isReadOnly', 'kasirList', 'lokasiList'));
    }

    /**
     * Show the form for creating a new penerimaan notice.
     */
    public function create()
    {
        // Validasi: Kasir tidak bisa menambah penerimaan baru jika saldo lama belum 0
        if (Auth::user()->role == 'kasir') {
            $latestPenerimaan = PenerimaanNotice::getLatestPenerimaanForUser(Auth::id());
            if ($latestPenerimaan && $latestPenerimaan->hasSaldoRemaining()) {
                return redirect()->route('penerimaan-notices.index')
                    ->with('error', 'Tidak dapat menambah penerimaan baru. Saldo penerimaan sebelumnya masih tersisa: ' . $latestPenerimaan->getTotalSaldo() . ' notice.');
            }
        }

        $layanans = Layanan::all();
        $userLayananId = Auth::user()->layanan_id;

        // Get lokasi filtered by user's layanan_id
        $lokasis = \App\Models\Lokasi::where('layanan_id', $userLayananId)->get();

        return view('layouts.penerimaan-notices.create', compact('layanans', 'userLayananId', 'lokasis'));
    }

    /**
     * Store a newly created penerimaan notice in storage.
     */
    public function store(Request $request)
    {
        // Validasi: Kasir tidak bisa menambah penerimaan baru jika saldo lama belum 0
        if (Auth::user()->role == 'kasir') {
            $latestPenerimaan = PenerimaanNotice::getLatestPenerimaanForUser(Auth::id());
            if ($latestPenerimaan && $latestPenerimaan->hasSaldoRemaining()) {
                return redirect()->route('penerimaan-notices.index')
                    ->with('error', 'Tidak dapat menambah penerimaan baru. Saldo penerimaan sebelumnya masih tersisa: ' . $latestPenerimaan->getTotalSaldo() . ' notice.');
            }
        }

        $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_awal' => ['required', 'integer', 'min:1'],
            'nomor_akhir' => ['required', 'integer', 'min:1', 'gte:nomor_awal'],
            'lokasi_id' => ['required', 'exists:lokasi,id'],
        ]);

        // Calculate jumlah
        $jumlah = $request->nomor_akhir - $request->nomor_awal + 1;

        PenerimaanNotice::create([
            'tanggal' => $request->tanggal,
            'nomor_awal' => $request->nomor_awal,
            'nomor_akhir' => $request->nomor_akhir,
            'jumlah' => $jumlah,
            'lokasi_id' => $request->lokasi_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('penerimaan-notices.index')
            ->with('success', 'Penerimaan Notice berhasil ditambahkan.');
    }

    /**
     * Display the specified penerimaan notice.
     */
    public function show(PenerimaanNotice $penerimaanNotice)
    {
        $isReadOnly = false;

        // Access control
        if (Auth::user()->role == 'kasir') {
            // Kasir can only see their own data
            if ($penerimaanNotice->created_by != Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (Auth::user()->role == 'admin') {
            // Admin: read-only access, check if penerimaan is from kasir with same layanan
            $isReadOnly = true;
            $adminLayanan = Auth::user()->layanan;

            if ($adminLayanan) {
                // Check if penerimaan creator has same layanan as admin
                $penerimaanCreator = $penerimaanNotice->creator;
                if (!$penerimaanCreator || $penerimaanCreator->layanan_id != $adminLayanan->id) {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                abort(403, 'Unauthorized action.');
            }
        }

        $penerimaanNotice->load(['lokasi', 'creator', 'pengeluaran.creator', 'pengeluaran.lokasi', 'pengeluaran.pemakaianRanges', 'pengeluaran.batalRusak', 'pengeluaran.buktiKas', 'saldo']);

        // Check if kasir can add pengeluaran
        $canAddPengeluaran = false;
        $pengeluaranBlockReason = '';
        if (Auth::user()->role == 'kasir') {
            $canAddPengeluaran = true;
            // Check if saldo is 0
            if (!$penerimaanNotice->hasSaldoRemaining()) {
                $canAddPengeluaran = false;
                $pengeluaranBlockReason = 'saldo_habis';
            } else {
                // Check if already created 2 pengeluaran today for this penerimaan
                $todayDate = now()->format('Y-m-d');
                $todayPengeluaranCount = \App\Models\PengeluaranNotice::countTodayPengeluaranByUser(Auth::id(), $todayDate, $penerimaanNotice->id);
                if ($todayPengeluaranCount >= 2) {
                    $canAddPengeluaran = false;
                    $pengeluaranBlockReason = 'limit_harian';
                }
            }
        }

        return view('layouts.penerimaan-notices.show', compact('penerimaanNotice', 'canAddPengeluaran', 'pengeluaranBlockReason', 'isReadOnly'));
    }

    /**
     * Show the form for editing the specified penerimaan notice.
     */
    public function edit(PenerimaanNotice $penerimaanNotice)
    {
        // Check if kasir can only edit their own data
        if (Auth::user()->role == 'kasir' && $penerimaanNotice->created_by != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $layanans = Layanan::all();
        $userLayananId = Auth::user()->layanan_id;
        return view('layouts.penerimaan-notices.edit', compact('penerimaanNotice', 'layanans', 'userLayananId'));
    }

    /**
     * Update the specified penerimaan notice in storage.
     */
    public function update(Request $request, PenerimaanNotice $penerimaanNotice)
    {
        // Check if kasir can only update their own data
        if (Auth::user()->role == 'kasir' && $penerimaanNotice->created_by != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_awal' => ['required', 'integer', 'min:1'],
            'nomor_akhir' => ['required', 'integer', 'min:1', 'gte:nomor_awal'],
            'lokasi_id' => ['required', 'exists:layanan,id'],
        ]);

        // Calculate jumlah
        $jumlah = $request->nomor_akhir - $request->nomor_awal + 1;

        $penerimaanNotice->update([
            'tanggal' => $request->tanggal,
            'nomor_awal' => $request->nomor_awal,
            'nomor_akhir' => $request->nomor_akhir,
            'jumlah' => $jumlah,
            'lokasi_id' => $request->lokasi_id,
        ]);

        return redirect()->route('penerimaan-notices.index')
            ->with('success', 'Penerimaan Notice berhasil diperbarui.');
    }

    /**
     * Export penerimaan notices to Excel (untuk Kasir)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $dateFrom = $request->has('tanggal_dari') && $request->tanggal_dari != '' ? $request->tanggal_dari : null;
        $dateTo = $request->has('tanggal_sampai') && $request->tanggal_sampai != '' ? $request->tanggal_sampai : null;

        $fileName = 'penerimaan-notice-' . ($dateFrom ? date('Ymd', strtotime($dateFrom)) : 'all') . '.xlsx';

        return Excel::download(
            new PenerimaanNoticeExport($user->id, 'kasir', null, $dateFrom, $dateTo),
            $fileName
        );
    }

    /**
     * Export penerimaan notices to Excel (untuk Admin)
     */
    public function exportAdmin(Request $request)
    {
        $user = Auth::user();
        $layananId = $user->layanan_id;
        $kasirId = $request->has('kasir_id') && $request->kasir_id != '' ? $request->kasir_id : null;
        $dateFrom = $request->has('tanggal_dari') && $request->tanggal_dari != '' ? $request->tanggal_dari : null;
        $dateTo = $request->has('tanggal_sampai') && $request->tanggal_sampai != '' ? $request->tanggal_sampai : null;

        $fileName = 'penerimaan-notice-admin-' . ($dateFrom ? date('Ymd', strtotime($dateFrom)) : 'all') . '.xlsx';

        return Excel::download(
            new PenerimaanNoticeExport($user->id, 'admin', $layananId, $dateFrom, $dateTo, $kasirId),
            $fileName
        );
    }
}
