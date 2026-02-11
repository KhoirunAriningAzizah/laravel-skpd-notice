<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Layanan;
use App\Models\Lokasi;
use App\Models\PengeluaranNotice;
use App\Models\SaldoNotice;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->role === 'superadmin') {
            // Dashboard Superadmin
            $data['totalAdmin'] = User::where('role', 'admin')->count();
            $data['totalKasir'] = User::where('role', 'kasir')->count();
            $data['totalLayanan'] = Layanan::count();
            $data['totalLokasi'] = Lokasi::count();
        } elseif ($user->role === 'admin') {
            // Dashboard Admin
            $layanan = $user->layanan;
            $data['layanan'] = $layanan;
            $data['totalKasir'] = User::where('role', 'kasir')
                ->where('layanan_id', $user->layanan_id)
                ->count();

            // Penggunaan notice hari ini dan bulan ini (dari pengeluaran notice)
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();

            $data['penggunaanHariIni'] = PengeluaranNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->whereDate('tanggal', $today)
                ->sum('jumlah_total');

            $data['penggunaanBulanIni'] = PengeluaranNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->where('tanggal', '>=', $thisMonth)
                ->sum('jumlah_total');

            // Sisa saldo notice aktif
            $data['sisaSaldoNotice'] = SaldoNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->sum('jumlah');
        } elseif ($user->role === 'kasir') {
            // Dashboard Kasir (sama seperti admin + nomor saldo)
            $layanan = $user->layanan;
            $data['layanan'] = $layanan;
            $data['totalKasir'] = User::where('role', 'kasir')
                ->where('layanan_id', $user->layanan_id)
                ->count();

            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();

            $data['penggunaanHariIni'] = PengeluaranNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->whereDate('tanggal', $today)
                ->sum('jumlah_total');

            $data['penggunaanBulanIni'] = PengeluaranNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->where('tanggal', '>=', $thisMonth)
                ->sum('jumlah_total');

            $data['sisaSaldoNotice'] = SaldoNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->sum('jumlah');

            // Nomor saldo awal dan akhir
            $saldoAwal = SaldoNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->orderBy('nomor_awal', 'asc')
                ->first();

            $saldoAkhir = SaldoNotice::whereHas('penerimaan.lokasi', function ($query) use ($user) {
                $query->where('layanan_id', $user->layanan_id);
            })
                ->orderBy('nomor_akhir', 'desc')
                ->first();

            $data['saldoNomorAwal'] = $saldoAwal ? $saldoAwal->nomor_awal : null;
            $data['saldoNomorAkhir'] = $saldoAkhir ? $saldoAkhir->nomor_akhir : null;
        }

        return view('home', $data);
    }

    public function blank()
    {
        return view('layouts.blank-page');
    }
}
