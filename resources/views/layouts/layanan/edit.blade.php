@extends('layouts.app')

@section('title', 'Edit Layanan')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('layanan.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Edit Layanan</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('layanan.index') }}">Manage Layanan</a></div>
                    <div class="breadcrumb-item">Edit Layanan</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Form Edit Layanan</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('layanan.update', $layanan->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label for="kode_kasir">Kode Kasir <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('kode_kasir') is-invalid @enderror"
                                            id="kode_kasir" name="kode_kasir"
                                            value="{{ old('kode_kasir', $layanan->kode_kasir) }}" required
                                            placeholder="Masukkan kode kasir">
                                        @error('kode_kasir')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="nama">Nama Layanan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                            id="nama" name="nama" value="{{ old('nama', $layanan->nama) }}" required
                                            placeholder="Masukkan nama layanan">
                                        @error('nama')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                        <a href="{{ route('layanan.index') }}" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    </div>
                                </form>
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
@endpush
