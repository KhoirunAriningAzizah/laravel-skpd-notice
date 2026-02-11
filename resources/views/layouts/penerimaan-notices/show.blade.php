@extends('layouts.app')

@section('title', 'Detail Penerimaan Notice')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ $isReadOnly ? route('admin.penerimaan-notices.index') : route('penerimaan-notices.index') }}"
                        class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Detail Penerimaan Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a
                            href="{{ $isReadOnly ? route('admin.penerimaan-notices.index') : route('penerimaan-notices.index') }}">Penerimaan
                            Notice</a>
                    </div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Penerimaan Notice</h4>
                                <div class="card-header-action">
                                    @if (!$isReadOnly)
                                        @if (Auth::user()->role == 'kasir')
                                            @if (!$canAddPengeluaran)
                                                <button type="button" class="btn btn-success mr-2" disabled
                                                    data-toggle="tooltip"
                                                    title="{{ $pengeluaranBlockReason == 'saldo_habis' ? 'Saldo sudah habis (0)' : 'Sudah mencapai batas 2 pengeluaran per hari' }}">
                                                    <i class="fas fa-plus"></i> Tambahkan Pengeluaran
                                                </button>
                                            @else
                                                <a href="{{ route('pengeluaran-notices.create', ['penerimaan_id' => $penerimaanNotice->id]) }}"
                                                    class="btn btn-success mr-2">
                                                    <i class="fas fa-plus"></i> Tambahkan Pengeluaran
                                                </a>
                                            @endif
                                        @endif
                                        <a href="{{ route('penerimaan-notices.edit', $penerimaanNotice->id) }}"
                                            class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @else
                                        <span class="badge badge-info badge-lg">
                                            <i class="fas fa-eye"></i> Mode Tampilan (Read-Only)
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if (Auth::user()->role == 'kasir' && !$canAddPengeluaran)
                                    @if ($pengeluaranBlockReason == 'saldo_habis')
                                        <div class="alert alert-info alert-has-icon mb-3">
                                            <div class="alert-icon"><i class="fas fa-info-circle"></i></div>
                                            <div class="alert-body">
                                                <div class="alert-title">Informasi</div>
                                                Anda tidak dapat menambahkan pengeluaran lagi untuk penerimaan ini karena
                                                <strong>saldo sudah habis (0)</strong>.
                                                Semua notice telah dikeluarkan.
                                            </div>
                                        </div>
                                    @elseif($pengeluaranBlockReason == 'limit_harian')
                                        <div class="alert alert-warning alert-has-icon mb-3">
                                            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                            <div class="alert-body">
                                                <div class="alert-title">Batas Harian Tercapai</div>
                                                Anda tidak dapat menambahkan pengeluaran lagi karena <strong>sudah mencapai
                                                    batas maksimal 2 pengeluaran per hari</strong>.
                                                Silakan coba lagi besok atau hubungi admin jika ada kebutuhan mendesak.
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 200px">Tanggal</th>
                                                <td>{{ $penerimaanNotice->tanggal->format('d F Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Nomor Awal</th>
                                                {{-- <td>{{ number_format($penerimaanNotice->nomor_awal, 0, ',', '.') }}</td> --}}
                                                <td>{{ $penerimaanNotice->nomor_awal }}</td>
                                            </tr>
                                            <tr>
                                                <th>Nomor Akhir</th>
                                                <td>{{ $penerimaanNotice->nomor_akhir }}</td>

                                            </tr>
                                            <tr>
                                                <th>Jumlah</th>
                                                <td>
                                                    <span class="badge badge-primary badge-lg">
                                                        {{ number_format($penerimaanNotice->jumlah, 0, ',', '.') }} Notice
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 200px">Layanan</th>
                                                <td>
                                                    @if ($penerimaanNotice->lokasi)
                                                        <span
                                                            class="badge badge-info">{{ $penerimaanNotice->lokasi->nama }}</span>
                                                        <br>
                                                        <small class="text-muted">Kode:
                                                            {{ $penerimaanNotice->lokasi->layanan->kode_kasir }}</small>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Dibuat Oleh</th>
                                                <td>{{ $penerimaanNotice->creator->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dibuat Pada</th>
                                                <td>{{ $penerimaanNotice->created_at->format('d F Y, H:i') }} WIB</td>
                                            </tr>
                                            <tr>
                                                <th>Terakhir Diupdate</th>
                                                <td>{{ $penerimaanNotice->updated_at->format('d F Y, H:i') }} WIB</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengeluaran Section -->
                        @if ($penerimaanNotice->pengeluaran->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Riwayat Pengeluaran</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive"
                                        style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                        <table class="table table-bordered table-striped table-md"
                                            style="min-width: 1400px; white-space: nowrap;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center;">
                                                        TANGGAL</th>
                                                    <th colspan="2"
                                                        style="text-align: center; background-color: #f0f0f0;">PEMAKAIAN
                                                    </th>
                                                    <th colspan="2"
                                                        style="text-align: center; background-color: #fff3cd;">BATAL / RUSAK
                                                    </th>
                                                    <th colspan="3"
                                                        style="text-align: center; background-color: #d4edda;">BUKTI KAS
                                                    </th>
                                                    <th rowspan="2"
                                                        style="vertical-align: middle; text-align: center; background-color: #cce5ff;">
                                                        JML TOTAL</th>
                                                    <th colspan="2"
                                                        style="text-align: center; background-color: #f8f9fa;">SALDO</th>
                                                    <th rowspan="2"
                                                        style="vertical-align: middle; text-align: center; background-color: #e8f4f8;">
                                                        DIBUAT OLEH</th>
                                                    <th rowspan="2"
                                                        style="vertical-align: middle; text-align: center; background-color: #ffe8f0;">
                                                        LOKASI</th>
                                                </tr>
                                                <tr>
                                                    <th style="text-align: center; background-color: #f0f0f0;">NOMOR</th>
                                                    <th style="text-align: center; background-color: #f0f0f0;">JUMLAH</th>
                                                    <th style="text-align: center; background-color: #fff3cd;">NOMOR</th>
                                                    <th style="text-align: center; background-color: #fff3cd;">JML</th>
                                                    <th style="text-align: center; background-color: #d4edda;">LOKAL</th>
                                                    <th style="text-align: center; background-color: #d4edda;">LINK</th>
                                                    <th style="text-align: center; background-color: #d4edda;">JML</th>
                                                    <th style="text-align: center; background-color: #f8f9fa;">NOMOR</th>
                                                    <th style="text-align: center; background-color: #f8f9fa;">JML</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($penerimaanNotice->pengeluaran as $pengeluaran)
                                                    @php
                                                        $pemakaianRanges = $pengeluaran->pemakaianRanges;
                                                        $batalRusak = $pengeluaran->batalRusak;
                                                        $buktiKas = $pengeluaran->buktiKas->first();
                                                        $saldo = $pengeluaran->saldo;
                                                        $maxRows = max($pemakaianRanges->count(), 1);

                                                        // dd($pengeluaran->lokasi);

                                                    @endphp

                                                    @for ($i = 0; $i < $maxRows; $i++)
                                                        <tr>
                                                            @if ($i === 0)
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center;">
                                                                    {{ $pengeluaran->tanggal->format('d/m/Y') }}
                                                                </td>
                                                            @endif

                                                            @if ($i < $pemakaianRanges->count())
                                                                @php $pemakaian = $pemakaianRanges[$i]; @endphp
                                                                <td style="text-align: center;">
                                                                    {{ $pemakaian->nomor_awal }}
                                                                    -
                                                                    {{ $pemakaian->nomor_akhir }}
                                                                </td>
                                                                <td style="text-align: center;">
                                                                    <span class="badge badge-primary">
                                                                        {{ number_format($pemakaian->jumlah, 0, ',', '.') }}
                                                                    </span>
                                                                </td>
                                                            @else
                                                                <td style="text-align: center;">-</td>
                                                                <td style="text-align: center;">-</td>
                                                            @endif

                                                            @if ($i === 0)
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center;">
                                                                    @if ($batalRusak->count() > 0)
                                                                        @foreach ($batalRusak as $batal)
                                                                            <span class="badge badge-danger mb-1">
                                                                                {{ $batal->nomor_notice }}
                                                                            </span>{{ !$loop->last ? ', ' : '' }}
                                                                        @endforeach
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center;">
                                                                    @if ($batalRusak->count() > 0)
                                                                        <span class="badge badge-warning">
                                                                            {{ $batalRusak->count() }}
                                                                        </span>
                                                                    @else
                                                                        0
                                                                    @endif
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center;">
                                                                    {{ $buktiKas ? number_format($buktiKas->lokal, 0, ',', '.') : 0 }}
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center;">
                                                                    {{ $buktiKas ? number_format($buktiKas->link, 0, ',', '.') : 0 }}
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center;">
                                                                    <span class="badge badge-success">
                                                                        {{ $buktiKas ? number_format($buktiKas->jumlah, 0, ',', '.') : 0 }}
                                                                    </span>
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center; font-weight: bold; background-color: #e7f3ff;">
                                                                    {{ number_format($pengeluaran->jumlah_total, 0, ',', '.') }}
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center; background-color: #f8f9fa;">
                                                                    @if ($saldo)
                                                                        {{ $saldo->nomor_awal }} -
                                                                        {{ $saldo->nomor_akhir }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center; background-color: #f8f9fa;">
                                                                    @if ($saldo)
                                                                        <span class="badge badge-secondary">
                                                                            {{ number_format($saldo->jumlah, 0, ',', '.') }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-secondary">0</span>
                                                                    @endif
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center; background-color: #e8f4f8;">
                                                                    {{ $pengeluaran->creator ? $pengeluaran->creator->name : '-' }}
                                                                </td>
                                                                <td rowspan="{{ $maxRows }}"
                                                                    style="vertical-align: middle; text-align: center; background-color: #ffe8f0;">
                                                                    {{ $pengeluaran->lokasi ? $pengeluaran->lokasi->nama : '-' }}
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endfor
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Saldo Section -->
                        <div class="card">
                            <div class="card-header">
                                <h4>Saldo Notice</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nomor Awal</th>
                                                <th>Nomor Akhir</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($penerimaanNotice->saldo->count() > 0)
                                                @foreach ($penerimaanNotice->saldo as $index => $saldo)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $saldo->nomor_awal }}</td>
                                                        <td>{{ $saldo->nomor_akhir }}</td>
                                                        <td>
                                                            <span class="badge badge-primary">
                                                                {{ number_format($saldo->jumlah, 0, ',', '.') }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center">
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-check-circle"></i> Saldo Habis (0) - Semua
                                                            notice telah dikeluarkan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
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
@endpush
