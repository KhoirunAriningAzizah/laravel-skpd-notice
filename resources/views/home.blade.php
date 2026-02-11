@extends('layouts.app')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard</h1>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @if (Auth::user()->role === 'superadmin')
                {{-- Dashboard Superadmin --}}
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Admin</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalAdmin }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Kasir</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalKasir }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Layanan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalLayanan }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Lokasi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalLokasi }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-info-circle"></i> Informasi Sistem</h4>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Sistem SKPD Notice Pajak berjalan dengan baik. Anda memiliki kontrol penuh atas semua
                                    data dan pengguna.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif(Auth::user()->role === 'admin')
                {{-- Dashboard Admin --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4><i class="fas fa-building"></i> Layanan Anda</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">{{ $layanan ? $layanan->nama : '-' }}</h5>
                                        <small class="text-muted">
                                            Kode: {{ $layanan ? $layanan->kode_kasir : '-' }}
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="badge badge-primary badge-lg">
                                            <i class="fas fa-users"></i> {{ $totalKasir }} Kasir
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Penggunaan Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($penggunaanHariIni ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Penggunaan Bulan Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($penggunaanBulanIni ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Sisa Saldo Notice</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($sisaSaldoNotice ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-line"></i> Ringkasan Aktivitas</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-file-alt text-info"></i> Penggunaan Notice Hari Ini
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($penggunaanHariIni ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-calendar text-warning"></i> Penggunaan Notice Bulan Ini
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($penggunaanBulanIni ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-wallet text-success"></i> Sisa Saldo Notice Aktif</td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($sisaSaldoNotice ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif(Auth::user()->role === 'kasir')
                {{-- Dashboard Kasir --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card card-success">
                            <div class="card-header">
                                <h4><i class="fas fa-building"></i> Layanan Anda</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">{{ $layanan ? $layanan->nama : '-' }}</h5>
                                        <small class="text-muted">
                                            Kode: {{ $layanan ? $layanan->kode_kasir : '-' }}
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="badge badge-success badge-lg">
                                            <i class="fas fa-users"></i> {{ $totalKasir }} Kasir
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Penggunaan Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($penggunaanHariIni ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Penggunaan Bulan Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($penggunaanBulanIni ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Sisa Saldo Notice</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($sisaSaldoNotice ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4><i class="fas fa-file-invoice"></i> Nomor Notice Tersedia</h4>
                            </div>
                            <div class="card-body">
                                @if ($saldoNomorAwal && $saldoNomorAkhir)
                                    <div class="text-center mb-3">
                                        <h5 class="mb-2">Range Nomor Saldo Aktif</h5>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="badge badge-lg badge-primary mr-2"
                                                style="font-size: 1.1rem; padding: 10px 15px;">
                                                {{ $saldoNomorAwal }}
                                            </div>
                                            <i class="fas fa-arrow-right text-muted mx-2"></i>
                                            <div class="badge badge-lg badge-primary ml-2"
                                                style="font-size: 1.1rem; padding: 10px 15px;">
                                                {{ $saldoNomorAkhir }}
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6 text-center">
                                            <small class="text-muted">Nomor Awal</small>
                                            <h6>{{ $saldoNomorAwal }}</h6>
                                        </div>
                                        <div class="col-6 text-center">
                                            <small class="text-muted">Nomor Akhir</small>
                                            <h6>{{ $saldoNomorAkhir }}</h6>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada saldo notice aktif</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-line"></i> Ringkasan Aktivitas</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-file-alt text-info"></i> Penggunaan Notice Hari Ini
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($penggunaanHariIni ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-calendar text-warning"></i> Penggunaan Notice Bulan
                                                    Ini
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($penggunaanBulanIni ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-wallet text-success"></i> Sisa Saldo Notice Aktif</td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($sisaSaldoNotice ?? 0, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection
