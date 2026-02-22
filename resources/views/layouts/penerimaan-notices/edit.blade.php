@extends('layouts.app')

@section('title', 'Edit Penerimaan Notice')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap-daterangepicker/daterangepicker.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ Auth::user()->role == 'admin' ? route('admin.penerimaan-notices.index') : route('penerimaan-notices.index') }}"
                        class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Edit Penerimaan Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a
                            href="{{ Auth::user()->role == 'admin' ? route('admin.penerimaan-notices.index') : route('penerimaan-notices.index') }}">Penerimaan
                            Notice</a>
                    </div>
                    <div class="breadcrumb-item">Edit</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Form Edit Penerimaan Notice</h4>
                            </div>
                            <div class="card-body">
                                <form
                                    action="{{ Auth::user()->role == 'admin' ? route('admin.penerimaan-notices.update', $penerimaanNotice->id) : route('penerimaan-notices.update', $penerimaanNotice->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" disabled
                                                    class="form-control @error('tanggal') is-invalid @enderror"
                                                    id="tanggal_display"
                                                    value="{{ old('tanggal', $penerimaanNotice->tanggal->format('Y-m-d')) }}">
                                                <!-- Hidden input untuk mengirim nilai tanggal -->
                                                <input type="hidden" name="tanggal"
                                                    value="{{ old('tanggal', $penerimaanNotice->tanggal->format('Y-m-d')) }}">
                                                @error('tanggal')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="lokasi_id">Layanan <span class="text-danger">*</span></label>
                                                <select class="form-control @error('lokasi_id') is-invalid @enderror"
                                                    disabled id="lokasi_id_display">
                                                    @if (!$userLayananId && !$penerimaanNotice->lokasi_id)
                                                        <option value="">-- Pilih Layanan --</option>
                                                    @endif
                                                    @foreach ($layanans as $layanan)
                                                        <option value="{{ $layanan->id }}"
                                                            {{ old('lokasi_id', $penerimaanNotice->lokasi_id ?? $userLayananId) == $layanan->id ? 'selected' : '' }}>
                                                            {{ $layanan->nama }} ({{ $layanan->kode_kasir }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <!-- Hidden input untuk mengirim nilai lokasi_id -->
                                                <input type="hidden" name="lokasi_id"
                                                    value="{{ old('lokasi_id', $penerimaanNotice->lokasi_id ?? $userLayananId) }}">
                                                @error('lokasi_id')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nomor_awal">Nomor Awal <span
                                                        class="text-danger">*</span></label>
                                                <input type="number"
                                                    class="form-control @error('nomor_awal') is-invalid @enderror"
                                                    id="nomor_awal" name="nomor_awal"
                                                    value="{{ old('nomor_awal', $penerimaanNotice->nomor_awal) }}" required
                                                    min="1" onchange="hitungJumlah()">
                                                @error('nomor_awal')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nomor_akhir">Nomor Akhir <span
                                                        class="text-danger">*</span></label>
                                                <input type="number"
                                                    class="form-control @error('nomor_akhir') is-invalid @enderror"
                                                    id="nomor_akhir" name="nomor_akhir"
                                                    value="{{ old('nomor_akhir', $penerimaanNotice->nomor_akhir) }}"
                                                    required min="1" onchange="hitungJumlah()">
                                                @error('nomor_akhir')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="jumlah_display">Jumlah</label>
                                                <input type="text" class="form-control bg-light" id="jumlah_display"
                                                    readonly
                                                    value="{{ number_format($penerimaanNotice->jumlah, 0, ',', '.') }}">
                                                <small class="form-text text-muted">
                                                    Otomatis terhitung dari nomor akhir - nomor awal + 1
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                        <a href="{{ route('penerimaan-notices.index') }}" class="btn btn-secondary">
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

    <script>
        function hitungJumlah() {
            const nomorAwal = parseInt(document.getElementById('nomor_awal').value) || 0;
            const nomorAkhir = parseInt(document.getElementById('nomor_akhir').value) || 0;

            if (nomorAwal > 0 && nomorAkhir > 0 && nomorAkhir >= nomorAwal) {
                const jumlah = nomorAkhir - nomorAwal + 1;
                document.getElementById('jumlah_display').value = jumlah.toLocaleString('id-ID');
            } else {
                document.getElementById('jumlah_display').value = '0';
            }
        }

        // Calculate on page load
        document.addEventListener('DOMContentLoaded', function() {
            hitungJumlah();
        });
    </script>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endpush
