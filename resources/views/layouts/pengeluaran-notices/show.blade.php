@extends('layouts.app')

@section('title', 'Detail Pengeluaran Notice')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('admin.pengeluaran-notices.index') }}" class="btn btn-icon">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <h1>Detail Pengeluaran Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.pengeluaran-notices.index') }}">Pengeluaran
                            Notice</a></div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Pengeluaran Notice</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('admin.pengeluaran-notices.edit', $pengeluaranNotice->id) }}"
                                        class="btn btn-warning mr-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.pengeluaran-notices.destroy', $pengeluaranNotice->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus pengeluaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 200px">Tanggal Pengeluaran</th>
                                                <td>{{ $pengeluaranNotice->tanggal->format('d F Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Penerimaan Notice</th>
                                                <td>
                                                    @if ($pengeluaranNotice->penerimaan)
                                                        {{ number_format($pengeluaranNotice->penerimaan->nomor_awal, 0, ',', '.') }}
                                                        -
                                                        {{ number_format($pengeluaranNotice->penerimaan->nomor_akhir, 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Total</th>
                                                <td>
                                                    <span class="badge badge-primary badge-lg">
                                                        {{ number_format($pengeluaranNotice->jumlah_total, 0, ',', '.') }}
                                                        Notice
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Lokasi</th>
                                                <td>
                                                    @if ($pengeluaranNotice->lokasi)
                                                        <span
                                                            class="badge badge-info">{{ $pengeluaranNotice->lokasi->nama }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 200px">Dibuat Oleh</th>
                                                <td>{{ $pengeluaranNotice->creator->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dibuat Pada</th>
                                                <td>{{ $pengeluaranNotice->created_at->format('d F Y, H:i') }} WIB</td>
                                            </tr>
                                            <tr>
                                                <th>Terakhir Diupdate</th>
                                                <td>{{ $pengeluaranNotice->updated_at->format('d F Y, H:i') }} WIB</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pemakaian Ranges -->
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-file-alt"></i> Pemakaian Notice</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nomor Awal</th>
                                                <th>Nomor Akhir</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pengeluaranNotice->pemakaianRanges as $index => $range)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ number_format($range->nomor_awal, 0, ',', '.') }}</td>
                                                    <td>{{ number_format($range->nomor_akhir, 0, ',', '.') }}</td>
                                                    <td>
                                                        <span class="badge badge-primary">
                                                            {{ number_format($range->nomor_akhir - $range->nomor_awal + 1, 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">Tidak ada data pemakaian</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Batal/Rusak -->
                        @if ($pengeluaranNotice->batalRusak->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="fas fa-exclamation-triangle"></i> Batal / Rusak</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nomor Notice</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pengeluaranNotice->batalRusak as $index => $batal)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ number_format($batal->nomor_notice, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Bukti Kas -->
                        @if ($pengeluaranNotice->buktiKas)
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="fas fa-receipt"></i> Bukti Kas</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-3" style="background: #d4edda; border-radius: 8px;">
                                                <h6>Lokal</h6>
                                                <h3>{{ number_format($pengeluaranNotice->buktiKas->lokal, 0, ',', '.') }}
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3" style="background: #cce5ff; border-radius: 8px;">
                                                <h6>Link</h6>
                                                <h3>{{ number_format($pengeluaranNotice->buktiKas->link, 0, ',', '.') }}
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3" style="background: #fff3cd; border-radius: 8px;">
                                                <h6>Total</h6>
                                                <h3>{{ number_format($pengeluaranNotice->buktiKas->jumlah, 0, ',', '.') }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
@endpush
