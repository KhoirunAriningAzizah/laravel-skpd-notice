@extends('layouts.app')

@section('title', 'Manage Lokasi')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Manage Lokasi</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Manage Lokasi</div>
                </div>
            </div>

            @if (session('message'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                        {{ session('message') }}
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
                                <h4>Lokasi List</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('lokasi.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Tambah Lokasi
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form action="{{ route('lokasi.index') }}" method="GET">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control"
                                                    placeholder="Cari berdasarkan nama lokasi..."
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
                                                <th>Nama Lokasi</th>
                                                {{-- <th>Jumlah Layanan</th> --}}
                                                <th>Dibuat</th>
                                                <th style="width: 200px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($lokasis as $index => $lokasi)
                                                <tr>
                                                    <td>{{ $lokasis->firstItem() + $index }}</td>
                                                    <td>
                                                        <strong>{{ $lokasi->nama }}</strong>
                                                    </td>
                                                    {{-- <td>
                                                        <span class="badge badge-info">{{ $lokasi->layanan_count }}
                                                            Layanan</span>
                                                    </td> --}}
                                                    <td>{{ $lokasi->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('lokasi.edit', $lokasi->id) }}"
                                                            class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('lokasi.destroy', $lokasi->id) }}"
                                                            method="POST" style="display: inline-block;"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        <div class="empty-state" style="padding: 40px 0;">
                                                            <div class="empty-state-icon">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                            </div>
                                                            <h2>Tidak ada lokasi</h2>
                                                            <p class="lead">
                                                                Belum ada lokasi yang terdaftar.
                                                            </p>
                                                            <a href="{{ route('lokasi.create') }}"
                                                                class="btn btn-primary mt-3">
                                                                <i class="fas fa-plus"></i> Tambah Lokasi
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($lokasis->hasPages())
                                    <div class="float-right">
                                        {{ $lokasis->links() }}
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
    <!-- JS Libraries -->
    <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>
@endpush
