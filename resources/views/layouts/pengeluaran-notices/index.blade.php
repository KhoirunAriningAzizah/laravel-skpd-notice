@extends('layouts.app')

@section('title', 'Pengeluaran Notice')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Pengeluaran Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Pengeluaran Notice</div>
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
                        <div class="card" style="opacity: 0.93">
                            <div class="card-header">
                                <h4>Daftar Pengeluaran Notice</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <form
                                            action="{{ Auth::user()->role == 'admin' ? route('admin.pengeluaran-notices.index') : route('pengeluaran-notices.index') }}"
                                            method="GET">
                                            <div class="row">
                                                @if (Auth::user()->role == 'admin')
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
                                                <div class="{{ Auth::user()->role == 'admin' ? 'col-md-3' : 'col-md-3' }}">
                                                    <div class="form-group">
                                                        <label>Lokasi</label>
                                                        <select name="lokasi_id" class="form-control">
                                                            <option value="">Semua Lokasi</option>
                                                            @foreach ($lokasiList as $lokasi)
                                                                <option value="{{ $lokasi->id }}"
                                                                    {{ request('lokasi_id') == $lokasi->id ? 'selected' : '' }}>
                                                                    {{ $lokasi->nama }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="{{ Auth::user()->role == 'admin' ? 'col-md-2' : 'col-md-3' }}">
                                                    <div class="form-group">
                                                        <label>Tanggal Dari</label>
                                                        <input type="date" name="tanggal_dari" class="form-control"
                                                            value="{{ request('tanggal_dari') }}">
                                                    </div>
                                                </div>
                                                <div class="{{ Auth::user()->role == 'admin' ? 'col-md-2' : 'col-md-3' }}">
                                                    <div class="form-group">
                                                        <label>Tanggal Sampai</label>
                                                        <input type="date" name="tanggal_sampai" class="form-control"
                                                            value="{{ request('tanggal_sampai') }}">
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
                                                <th>Penerimaan</th>
                                                <th>Jumlah Total</th>
                                                <th>Lokasi</th>
                                                <th>Dibuat Oleh</th>
                                                <th style="width: 200px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pengeluaranNotices as $index => $pengeluaran)
                                                <tr>
                                                    <td>{{ $pengeluaranNotices->firstItem() + $index }}</td>
                                                    <td>{{ $pengeluaran->tanggal->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if ($pengeluaran->penerimaan)
                                                            {{ number_format($pengeluaran->penerimaan->nomor_awal, 0, ',', '.') }}
                                                            -
                                                            {{ number_format($pengeluaran->penerimaan->nomor_akhir, 0, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge badge-primary">{{ number_format($pengeluaran->jumlah_total, 0, ',', '.') }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($pengeluaran->lokasi)
                                                            <span
                                                                class="badge badge-info">{{ $pengeluaran->lokasi->nama }}</span>
                                                        @else
                                                            <span class="badge badge-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $pengeluaran->creator->name ?? '-' }}</td>
                                                    <td>
                                                        @if (Auth::user()->role == 'admin')
                                                            <a href="{{ route('admin.pengeluaran-notices.show', $pengeluaran->id) }}"
                                                                class="btn btn-sm btn-info" title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('admin.pengeluaran-notices.edit', $pengeluaran->id) }}"
                                                                class="btn btn-sm btn-warning" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form
                                                                action="{{ route('admin.pengeluaran-notices.destroy', $pengeluaran->id) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Yakin ingin menghapus pengeluaran ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        <div class="empty-state" style="padding: 40px 0;">
                                                            <div class="empty-state-icon bg-primary">
                                                                <i class="fas fa-file-invoice"></i>
                                                            </div>
                                                            <h2>Belum Ada Data Pengeluaran</h2>
                                                            <p class="lead">
                                                                Belum ada data pengeluaran notice.
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($pengeluaranNotices->hasPages())
                                    <div class="mt-3">
                                        {{ $pengeluaranNotices->links() }}
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
@endpush
