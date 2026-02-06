@extends('layouts.app')

@section('title', 'Edit Lokasi')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('lokasi.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Edit Lokasi</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('lokasi.index') }}">Manage Lokasi</a></div>
                    <div class="breadcrumb-item">Edit Lokasi</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Form Edit Lokasi</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('lokasi.update', $lokasi->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label for="layanan_id">Layanan <span class="text-danger">*</span></label>
                                        <select class="form-control @error('layanan_id') is-invalid @enderror"
                                            id="layanan_id" name="layanan_id" required>
                                            <option value="">-- Pilih Layanan --</option>
                                            @foreach ($layanans as $layanan)
                                                <option value="{{ $layanan->id }}"
                                                    {{ old('layanan_id', $lokasi->layanan_id) == $layanan->id ? 'selected' : '' }}>
                                                    {{ $layanan->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('layanan_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="nama">Nama Lokasi <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                            id="nama" name="nama" value="{{ old('nama', $lokasi->nama) }}" required
                                            autofocus placeholder="Masukkan nama lokasi">
                                        @error('nama')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                        <a href="{{ route('lokasi.index') }}" class="btn btn-secondary">
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
