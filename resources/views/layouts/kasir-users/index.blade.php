@extends('layouts.app')

@section('title', 'Manage Kasir Users')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Manajemen User Kasir</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Manajemen User Kasir</div>
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
                                <h4>Kasir Users List</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('kasir-users.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Tambah Kasir
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form action="{{ route('kasir-users.index') }}" method="GET">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control"
                                                    placeholder="Cari berdasarkan nama atau email..."
                                                    value="{{ request('search') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="submit">
                                                        <i class="fas fa-search"></i> Cari
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px">#</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Layanan</th>
                                                <th>Dibuat</th>
                                                <th style="width: 200px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($kasirs as $index => $kasir)
                                                <tr>
                                                    <td>{{ $kasirs->firstItem() + $index }}</td>
                                                    <td>
                                                        <strong>{{ $kasir->name }}</strong>
                                                    </td>
                                                    <td>{{ $kasir->email }}</td>
                                                    <td>
                                                        @if ($kasir->layanan)
                                                            <span
                                                                class="badge badge-info">{{ $kasir->layanan->nama }}</span>
                                                        @else
                                                            <span class="badge badge-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $kasir->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('kasir-users.edit', $kasir->id) }}"
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('kasir-users.destroy', $kasir->id) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus kasir ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        <div class="empty-state" style="padding: 40px 0;">
                                                            <div class="empty-state-icon bg-primary">
                                                                <i class="fas fa-users"></i>
                                                            </div>
                                                            <h2>Belum Ada Data Kasir</h2>
                                                            <p class="lead">
                                                                Anda belum memiliki data kasir. Silakan tambahkan kasir
                                                                baru.
                                                            </p>
                                                            <a href="{{ route('kasir-users.create') }}"
                                                                class="btn btn-primary mt-3">
                                                                <i class="fas fa-plus"></i> Tambah Kasir
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($kasirs->hasPages())
                                    <div class="mt-3">
                                        {{ $kasirs->links() }}
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
