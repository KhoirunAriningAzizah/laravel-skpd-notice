@extends('layouts.app')

@section('title', 'Penerimaan Notice')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Penerimaan Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Penerimaan Notice</div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Daftar Penerimaan Notice</h4>
                                <div class="card-header-action">
                                    @if (!$isReadOnly)
                                        @if (Auth::user()->role == 'kasir' && !$canAddPenerimaan)
                                            <button type="button" class="btn btn-primary" disabled data-toggle="tooltip"
                                                title="Saldo penerimaan sebelumnya masih tersisa {{ $latestPenerimaanSaldo }} notice">
                                                <i class="fas fa-plus"></i> Tambah Penerimaan
                                            </button>
                                        @else
                                            <a href="{{ route('penerimaan-notices.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Tambah Penerimaan
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if (Auth::user()->role == 'kasir' && !$canAddPenerimaan)
                                    <div class="alert alert-warning alert-has-icon mb-3">
                                        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                        <div class="alert-body">
                                            <div class="alert-title">Perhatian!</div>
                                            Anda tidak dapat menambah penerimaan baru karena <strong>saldo penerimaan
                                                sebelumnya masih tersisa {{ $latestPenerimaanSaldo }} notice</strong>.
                                            Silakan lakukan pengeluaran terlebih dahulu hingga saldo menjadi 0.
                                        </div>
                                    </div>
                                @endif
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <form
                                            action="{{ $isReadOnly ? route('admin.penerimaan-notices.index') : route('penerimaan-notices.index') }}"
                                            method="GET">
                                            <div class="row">
                                                @if ($isReadOnly)
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Kasir</label>
                                                            <select name="kasir_id" class="form-control">
                                                                <option value="">Semua Kasir</option>
                                                                @foreach ($kasirList as $kasir)
                                                                    <option value="{{ $kasir->id }}"
                                                                        {{ request('kasir_id') == $kasir->id ? 'selected' : '' }}>
                                                                        {{ $kasir->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="{{ $isReadOnly ? 'col-md-2' : 'col-md-3' }}">
                                                    <div class="form-group">
                                                        <label>Tanggal Dari</label>
                                                        <input type="date" name="tanggal_dari" class="form-control"
                                                            value="{{ request('tanggal_dari') }}">
                                                    </div>
                                                </div>
                                                <div class="{{ $isReadOnly ? 'col-md-2' : 'col-md-3' }}">
                                                    <div class="form-group">
                                                        <label>Tanggal Sampai</label>
                                                        <input type="date" name="tanggal_sampai" class="form-control"
                                                            value="{{ request('tanggal_sampai') }}">
                                                    </div>
                                                </div>
                                                <div class="{{ $isReadOnly ? 'col-md-3' : 'col-md-4' }}">
                                                    <div class="form-group">
                                                        <label>Cari</label>
                                                        <input type="text" name="search" class="form-control"
                                                            placeholder="Cari nomor atau layanan..."
                                                            value="{{ request('search') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button class="btn btn-primary btn-block" type="submit">
                                                            <i class="fas fa-search"></i> Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px">No</th>
                                                <th>Tanggal</th>
                                                <th>Nomor Awal</th>
                                                <th>Nomor Akhir</th>
                                                <th>Jumlah</th>
                                                <th>Layanan</th>
                                                <th>Dibuat Oleh</th>
                                                <th style="width: 200px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($penerimaans as $index => $penerimaan)
                                                <tr>
                                                    <td>{{ $penerimaans->firstItem() + $index }}</td>
                                                    <td>{{ $penerimaan->tanggal->format('d/m/Y') }}</td>
                                                    <td>{{ number_format($penerimaan->nomor_awal, 0, ',', '.') }}</td>
                                                    <td>{{ number_format($penerimaan->nomor_akhir, 0, ',', '.') }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-primary">{{ number_format($penerimaan->jumlah, 0, ',', '.') }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($penerimaan->lokasi)
                                                            <span
                                                                class="badge badge-info">{{ $penerimaan->lokasi->layanan->nama }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $penerimaan->creator->name ?? '-' }}</td>
                                                    <td>
                                                        <a href="{{ $isReadOnly ? route('admin.penerimaan-notices.show', $penerimaan->id) : route('penerimaan-notices.show', $penerimaan->id) }}"
                                                            class="btn btn-sm btn-info" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if (!$isReadOnly)
                                                            <a href="{{ route('penerimaan-notices.edit', $penerimaan->id) }}"
                                                                class="btn btn-sm btn-warning" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">
                                                        <div class="empty-state" style="padding: 40px 0;">
                                                            <div class="empty-state-icon bg-primary">
                                                                <i class="fas fa-file-invoice"></i>
                                                            </div>
                                                            <h2>Belum Ada Data Penerimaan</h2>
                                                            <p class="lead">
                                                                @if ($isReadOnly)
                                                                    Belum ada data penerimaan notice dari kasir.
                                                                @else
                                                                    Anda belum memiliki data penerimaan notice. Silakan
                                                                    tambahkan penerimaan baru.
                                                                @endif
                                                            </p>
                                                            @if (!$isReadOnly)
                                                                <a href="{{ route('penerimaan-notices.create') }}"
                                                                    class="btn btn-primary mt-3">
                                                                    <i class="fas fa-plus"></i> Tambah Penerimaan
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($penerimaans->hasPages())
                                    <div class="mt-3">
                                        {{ $penerimaans->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>

    <!-- Page Specific JS File -->
    <script src="{{ asset('js/page/features-posts.js') }}"></script>
@endpush
