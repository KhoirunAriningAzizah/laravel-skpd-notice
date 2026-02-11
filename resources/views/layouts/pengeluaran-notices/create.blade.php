@extends('layouts.app')

@section('title', 'Tambah Pengeluaran Notice')

@push('style')
    <style>
        .section-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            color: #6777ef;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #6777ef;
            padding-bottom: 10px;
        }

        .form-group label {
            font-weight: 600;
        }

        .btn-add-range {
            margin-top: 10px;
        }

        .pemakaian-range-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #e3e6f0;
        }

        .batal-rusak-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            border: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-box {
            background: #6777ef;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-box h4 {
            color: white;
            margin-bottom: 10px;
        }

        .summary-box .total-value {
            font-size: 32px;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('penerimaan-notices.show', $penerimaanNotice->id) }}" class="btn btn-icon">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <h1>Tambah Pengeluaran Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('penerimaan-notices.index') }}">Penerimaan Notice</a>
                    </div>
                    <div class="breadcrumb-item"><a
                            href="{{ route('penerimaan-notices.show', $penerimaanNotice->id) }}">Detail</a></div>
                    <div class="breadcrumb-item">Tambah Pengeluaran</div>
                </div>
            </div>

            <div class="section-body">
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

                <div class="row">
                    <div class="col-12">
                        <!-- Info Penerimaan -->
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Penerimaan Notice</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Tanggal:</strong><br>
                                        {{ $penerimaanNotice->tanggal->format('d F Y') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Nomor Awal:</strong><br>
                                        {{ number_format($penerimaanNotice->nomor_awal, 0, ',', '.') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Nomor Akhir:</strong><br>
                                        {{ number_format($penerimaanNotice->nomor_akhir, 0, ',', '.') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Jumlah:</strong><br>
                                        <span class="badge badge-primary badge-lg">
                                            {{ number_format($penerimaanNotice->jumlah, 0, ',', '.') }} Notice
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Pengeluaran -->
                        <form action="{{ route('pengeluaran-notices.store') }}" method="POST" id="formPengeluaran">
                            @csrf
                            <input type="hidden" name="penerimaan_id" value="{{ $penerimaanNotice->id }}">

                            <div class="card">
                                <div class="card-body">
                                    <!-- Tanggal -->
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                            id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                                            required>
                                        @error('tanggal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Lokasi -->
                                    <div class="form-group">
                                        <label for="lokasi_id">Lokasi <span class="text-danger">*</span></label>
                                        <select class="form-control @error('lokasi_id') is-invalid @enderror" id="lokasi_id"
                                            name="lokasi_id" required>
                                            <option value="">-- Pilih Lokasi --</option>
                                            @foreach ($lokasis as $lokasi)
                                                <option value="{{ $lokasi->id }}"
                                                    {{ old('lokasi_id') == $lokasi->id ? 'selected' : '' }}>
                                                    {{ $lokasi->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('lokasi_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <hr>

                                    <!-- SECTION 1: PEMAKAIAN -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-file-alt"></i> SECTION 1: PEMAKAIAN
                                        </div>

                                        <div id="pemakaianContainer">
                                            <div class="pemakaian-range-item" data-index="0">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label>Nomor Awal <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control nomor-awal"
                                                                name="pemakaian[0][nomor_awal]"
                                                                value="{{ old('pemakaian.0.nomor_awal') }}"
                                                                min="{{ $penerimaanNotice->nomor_awal }}"
                                                                max="{{ $penerimaanNotice->nomor_akhir }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label>Nomor Akhir <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control nomor-akhir"
                                                                name="pemakaian[0][nomor_akhir]"
                                                                value="{{ old('pemakaian.0.nomor_akhir') }}"
                                                                min="{{ $penerimaanNotice->nomor_awal }}"
                                                                max="{{ $penerimaanNotice->nomor_akhir }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Jumlah</label>
                                                            <input type="text" class="form-control jumlah-pemakaian"
                                                                readonly value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <button type="button" class="btn btn-primary btn-add-range" id="btnAddPemakaian">
                                            <i class="fas fa-plus"></i> Tambah Range Pemakaian
                                        </button> --}}

                                        <div class="mt-3">
                                            <strong>Jumlah Pemakaian (setelah dikurangi Batal/Rusak): <span
                                                    id="totalPemakaian" class="badge badge-info badge-lg">0</span>
                                                Notice</strong>
                                        </div>
                                    </div>

                                    <!-- SECTION 2: BATAL / RUSAK -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-ban"></i> SECTION 2: BATAL / RUSAK
                                        </div>

                                        <div id="batalRusakContainer">
                                            <!-- Dynamic batal/rusak items will be added here -->
                                        </div>

                                        <button type="button" class="btn btn-warning btn-add-range mt-2"
                                            id="btnAddBatalRusak">
                                            <i class="fas fa-plus"></i> Tambah Nomor Batal/Rusak
                                        </button>

                                        <div class="mt-3">
                                            <strong>Total Batal/Rusak: <span id="totalBatalRusak"
                                                    class="badge badge-warning badge-lg">0</span> Notice</strong>
                                        </div>
                                    </div>

                                    <!-- SECTION 3: BUKTI KAS -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-receipt"></i> SECTION 3: BUKTI KAS
                                        </div>

                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="lokal">Lokal <span class="text-danger">*</span></label>
                                                    <input type="number"
                                                        class="form-control @error('lokal') is-invalid @enderror"
                                                        id="lokal" name="lokal" value="{{ old('lokal', 0) }}"
                                                        min="0" required>
                                                    @error('lokal')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="link">Link <span class="text-danger">*</span></label>
                                                    <input type="number"
                                                        class="form-control @error('link') is-invalid @enderror"
                                                        id="link" name="link" value="{{ old('link', 0) }}"
                                                        min="0" required>
                                                    @error('link')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Jumlah</label>
                                                    <input type="text" class="form-control" id="jumlahBuktiKas"
                                                        readonly value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SECTION 4: SALDO -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-coins"></i> SECTION 4: SALDO
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="saldo_nomor_awal">Nomor Awal</label>
                                                    <input type="number" class="form-control" id="saldo_nomor_awal"
                                                        name="saldo_nomor_awal" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="saldo_nomor_akhir">Nomor Akhir</label>
                                                    <input type="number" class="form-control" id="saldo_nomor_akhir"
                                                        name="saldo_nomor_akhir" readonly
                                                        value="{{ $penerimaanNotice->nomor_akhir }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="saldo_jumlah">Jumlah</label>
                                                    <input type="number" class="form-control" id="saldo_jumlah"
                                                        name="saldo_jumlah" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- JUMLAH TOTAL -->
                                    <div class="summary-box">
                                        <h4>JUMLAH TOTAL PENGELUARAN</h4>
                                        <div class="total-value" id="jumlahTotalPengeluaran">0</div>
                                        <small>Notice</small>
                                        <div class="mt-2">
                                            <small id="formulaDisplay">(Pemakaian: 0 - Batal/Rusak: 0)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <a href="{{ route('penerimaan-notices.show', $penerimaanNotice->id) }}"
                                        class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Pengeluaran
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        let pemakaianIndex = 1;
        let batalRusakIndex = 0;

        // Calculate jumlah for pemakaian range
        function calculatePemakaianJumlah(item) {
            const nomorAwal = parseInt($(item).find('.nomor-awal').val()) || 0;
            const nomorAkhir = parseInt($(item).find('.nomor-akhir').val()) || 0;
            const jumlah = nomorAkhir >= nomorAwal ? (nomorAkhir - nomorAwal + 1) : 0;
            $(item).find('.jumlah-pemakaian').val(jumlah);
            updateTotals();
        }

        // Update all totals
        function updateTotals() {
            // Total Pemakaian (raw from ranges)
            let totalPemakaianRaw = 0;
            $('.pemakaian-range-item').each(function() {
                const nomorAwal = parseInt($(this).find('.nomor-awal').val()) || 0;
                const nomorAkhir = parseInt($(this).find('.nomor-akhir').val()) || 0;
                if (nomorAkhir >= nomorAwal) {
                    totalPemakaianRaw += (nomorAkhir - nomorAwal + 1);
                }
            });

            // Total Batal/Rusak
            let totalBatalRusak = $('.batal-rusak-input').length;
            $('#totalBatalRusak').text(totalBatalRusak);

            // Total Pemakaian (setelah dikurangi batal/rusak)
            const totalPemakaian = totalPemakaianRaw - totalBatalRusak;
            $('#totalPemakaian').text(totalPemakaian);

            // Update jumlah-pemakaian input to show adjusted value (after deducting batal/rusak)
            $('.jumlah-pemakaian').val(totalPemakaian);

            // Bukti Kas (harus sama dengan raw pemakaian termasuk batal/rusak)
            const lokal = parseInt($('#lokal').val()) || 0;
            const link = parseInt($('#link').val()) || 0;
            const jumlahBuktiKas = lokal + link;
            $('#jumlahBuktiKas').val(jumlahBuktiKas);

            // Jumlah Total Pengeluaran (termasuk batal/rusak)
            $('#jumlahTotalPengeluaran').text(totalPemakaianRaw);
            $('#formulaDisplay').text(
                `(Pemakaian: ${totalPemakaian} + Batal/Rusak: ${totalBatalRusak} = ${totalPemakaianRaw})`);

            // Check if bukti kas matches total raw (including batal/rusak)
            if (jumlahBuktiKas > 0 && jumlahBuktiKas !== totalPemakaianRaw) {
                $('#jumlahBuktiKas').addClass('border-danger');
            } else {
                $('#jumlahBuktiKas').removeClass('border-danger');
            }

            // Calculate Saldo
            const penerimaanNomorAkhir = {{ $penerimaanNotice->nomor_akhir }};
            const penerimaanNomorAwal = {{ $penerimaanNotice->nomor_awal }};

            // Cari nomor terakhir yang digunakan dari range pemakaian
            let nomorTerakhirDigunakan = 0;
            $('.pemakaian-range-item').each(function() {
                const nomorAkhir = parseInt($(this).find('.nomor-akhir').val()) || 0;
                if (nomorAkhir > nomorTerakhirDigunakan) {
                    nomorTerakhirDigunakan = nomorAkhir;
                }
            });

            // Saldo nomor awal adalah nomor setelah nomor terakhir yang digunakan
            const saldoNomorAwal = nomorTerakhirDigunakan > 0 ? nomorTerakhirDigunakan + 1 : penerimaanNomorAwal;
            const saldoNomorAkhir = penerimaanNomorAkhir;
            const saldoJumlah = saldoNomorAkhir >= saldoNomorAwal ? (saldoNomorAkhir - saldoNomorAwal + 1) : 0;

            $('#saldo_nomor_awal').val(saldoNomorAwal);
            $('#saldo_nomor_akhir').val(saldoNomorAkhir);
            $('#saldo_jumlah').val(saldoJumlah);
        }

        // Add new pemakaian range
        $('#btnAddPemakaian').click(function() {
            const html = `
                <div class="pemakaian-range-item" data-index="${pemakaianIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Range ${pemakaianIndex + 1}</strong>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-pemakaian">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nomor Awal <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control nomor-awal"
                                       name="pemakaian[${pemakaianIndex}][nomor_awal]"
                                       min="{{ $penerimaanNotice->nomor_awal }}"
                                       max="{{ $penerimaanNotice->nomor_akhir }}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nomor Akhir <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control nomor-akhir"
                                       name="pemakaian[${pemakaianIndex}][nomor_akhir]"
                                       min="{{ $penerimaanNotice->nomor_awal }}"
                                       max="{{ $penerimaanNotice->nomor_akhir }}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="text"
                                       class="form-control jumlah-pemakaian"
                                       readonly
                                       value="0">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#pemakaianContainer').append(html);
            pemakaianIndex++;
        });

        // Remove pemakaian range
        $(document).on('click', '.btn-remove-pemakaian', function() {
            $(this).closest('.pemakaian-range-item').remove();
            updateTotals();
        });

        // Calculate on input change
        $(document).on('input', '.nomor-awal, .nomor-akhir', function() {
            const item = $(this).closest('.pemakaian-range-item');
            calculatePemakaianJumlah(item);
        });

        // Add batal/rusak
        $('#btnAddBatalRusak').click(function() {
            const html = `
                <div class="batal-rusak-item" data-index="${batalRusakIndex}">
                    <div style="flex: 1;">
                        <input type="number"
                               class="form-control batal-rusak-input"
                               name="batal_rusak[]"
                               placeholder="Nomor Notice"
                               min="{{ $penerimaanNotice->nomor_awal }}"
                               max="{{ $penerimaanNotice->nomor_akhir }}"
                               required>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger btn-remove-batal">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            $('#batalRusakContainer').append(html);
            batalRusakIndex++;
            updateTotals();
        });

        // Remove batal/rusak
        $(document).on('click', '.btn-remove-batal', function() {
            $(this).closest('.batal-rusak-item').remove();
            updateTotals();
        });

        // Update bukti kas
        $('#lokal, #link').on('input', function() {
            updateTotals();
        });

        // Initial calculation
        $(document).ready(function() {
            $('.pemakaian-range-item').each(function() {
                calculatePemakaianJumlah(this);
            });
            updateTotals();
        });

        // Form validation before submit
        $('#formPengeluaran').submit(function(e) {
            // Calculate raw pemakaian
            let totalPemakaianRaw = 0;
            $('.pemakaian-range-item').each(function() {
                const nomorAwal = parseInt($(this).find('.nomor-awal').val()) || 0;
                const nomorAkhir = parseInt($(this).find('.nomor-akhir').val()) || 0;
                if (nomorAkhir >= nomorAwal) {
                    totalPemakaianRaw += (nomorAkhir - nomorAwal + 1);
                }
            });

            const totalBatalRusak = parseInt($('#totalBatalRusak').text()) || 0;
            const totalPemakaian = totalPemakaianRaw - totalBatalRusak;
            const jumlahBuktiKas = parseInt($('#jumlahBuktiKas').val()) || 0;

            if (totalPemakaianRaw === 0) {
                e.preventDefault();
                alert('Harap isi minimal 1 range pemakaian!');
                return false;
            }

            if (jumlahBuktiKas !== totalPemakaianRaw) {
                e.preventDefault();
                alert(
                    `Total Bukti Kas (${jumlahBuktiKas}) harus sama dengan Jumlah Total Pengeluaran (${totalPemakaianRaw}) yang termasuk ${totalBatalRusak} Batal/Rusak!`
                );
                return false;
            }
        });
    </script>
@endpush
