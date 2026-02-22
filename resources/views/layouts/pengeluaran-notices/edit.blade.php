@extends('layouts.app')

@section('title', 'Edit Pengeluaran Notice')

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
                    <a href="{{ route('admin.pengeluaran-notices.index') }}" class="btn btn-icon">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <h1>Edit Pengeluaran Notice</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.pengeluaran-notices.index') }}">Pengeluaran
                            Notice</a></div>
                    <div class="breadcrumb-item">Edit</div>
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                        <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="section-body">
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
                                        {{ $pengeluaranNotice->penerimaan->tanggal->format('d F Y') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Nomor Awal:</strong><br>
                                        {{ number_format($pengeluaranNotice->penerimaan->nomor_awal, 0, ',', '.') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Nomor Akhir:</strong><br>
                                        {{ number_format($pengeluaranNotice->penerimaan->nomor_akhir, 0, ',', '.') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Jumlah:</strong><br>
                                        <span class="badge badge-primary badge-lg">
                                            {{ number_format($pengeluaranNotice->penerimaan->jumlah, 0, ',', '.') }} Notice
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Edit Pengeluaran -->
                        <form action="{{ route('admin.pengeluaran-notices.update', $pengeluaranNotice->id) }}"
                            method="POST" id="formPengeluaran">
                            @csrf
                            @method('PUT')

                            <div class="card">
                                <div class="card-body">
                                    <!-- Tanggal -->
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                            id="tanggal" name="tanggal"
                                            value="{{ old('tanggal', $pengeluaranNotice->tanggal->format('Y-m-d')) }}"
                                            required>
                                        @error('tanggal')
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
                                            @foreach ($pengeluaranNotice->pemakaianRanges as $index => $range)
                                                <div class="pemakaian-range-item" data-index="{{ $index }}">
                                                    @if ($index > 0)
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <strong>Range {{ $index + 1 }}</strong>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger btn-remove-pemakaian">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <div class="mb-2">
                                                            <strong>Range 1</strong>
                                                        </div>
                                                    @endif
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label>Nomor Awal <span class="text-danger">*</span></label>
                                                                <input type="number" class="form-control nomor-awal"
                                                                    name="pemakaian[{{ $index }}][nomor_awal]"
                                                                    value="{{ old('pemakaian.' . $index . '.nomor_awal', $range->nomor_awal) }}"
                                                                    min="{{ $pengeluaranNotice->penerimaan->nomor_awal }}"
                                                                    max="{{ $pengeluaranNotice->penerimaan->nomor_akhir }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label>Nomor Akhir <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="number" class="form-control nomor-akhir"
                                                                    name="pemakaian[{{ $index }}][nomor_akhir]"
                                                                    value="{{ old('pemakaian.' . $index . '.nomor_akhir', $range->nomor_akhir) }}"
                                                                    min="{{ $pengeluaranNotice->penerimaan->nomor_awal }}"
                                                                    max="{{ $pengeluaranNotice->penerimaan->nomor_akhir }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Jumlah</label>
                                                                <input type="text" class="form-control jumlah-pemakaian"
                                                                    readonly
                                                                    value="{{ $range->nomor_akhir - $range->nomor_awal + 1 }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <button type="button" class="btn btn-success btn-add-range" id="btnAddPemakaian">
                                            <i class="fas fa-plus"></i> Tambah Range Pemakaian
                                        </button>

                                        <div class="alert alert-info mt-3">
                                            <strong>Total Pemakaian:</strong> <span id="totalPemakaian">0</span> notice
                                        </div>
                                    </div>

                                    <!-- SECTION 2: BATAL / RUSAK -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-exclamation-triangle"></i> SECTION 2: BATAL / RUSAK (Opsional)
                                        </div>

                                        <div id="batalRusakContainer">
                                            @foreach ($pengeluaranNotice->batalRusak as $index => $batal)
                                                <div class="batal-rusak-item" data-index="{{ $index }}">
                                                    <div style="flex: 1;">
                                                        <input type="number" class="form-control batal-rusak-input"
                                                            name="batal_rusak[]" placeholder="Nomor Notice"
                                                            value="{{ old('batal_rusak.' . $index, $batal->nomor_notice) }}"
                                                            min="{{ $pengeluaranNotice->penerimaan->nomor_awal }}"
                                                            max="{{ $pengeluaranNotice->penerimaan->nomor_akhir }}"
                                                            required>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-danger btn-remove-batal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>

                                        <button type="button" class="btn btn-warning btn-add-range"
                                            id="btnAddBatalRusak">
                                            <i class="fas fa-plus"></i> Tambah Nomor Batal/Rusak
                                        </button>

                                        <div class="alert alert-warning mt-3">
                                            <strong>Total Batal/Rusak:</strong> <span id="totalBatalRusak">0</span> notice
                                        </div>
                                    </div>

                                    <!-- SECTION 3: BUKTI KAS -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-receipt"></i> SECTION 3: BUKTI KAS
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="lokal">Lokal <span class="text-danger">*</span></label>
                                                    <input type="number"
                                                        class="form-control @error('lokal') is-invalid @enderror"
                                                        id="lokal" name="lokal"
                                                        value="{{ old('lokal', $pengeluaranNotice->buktiKas->lokal ?? 0) }}"
                                                        min="0" required>
                                                    @error('lokal')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="link">Link <span class="text-danger">*</span></label>
                                                    <input type="number"
                                                        class="form-control @error('link') is-invalid @enderror"
                                                        id="link" name="link"
                                                        value="{{ old('link', $pengeluaranNotice->buktiKas->link ?? 0) }}"
                                                        min="0" required>
                                                    @error('link')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="jumlahBuktiKas">Jumlah Total</label>
                                                    <input type="number" class="form-control" id="jumlahBuktiKas"
                                                        readonly
                                                        value="{{ ($pengeluaranNotice->buktiKas->lokal ?? 0) + ($pengeluaranNotice->buktiKas->link ?? 0) }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-success">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Catatan:</strong> Total Bukti Kas (Lokal + Link) harus sama dengan
                                            Jumlah Total
                                            Pengeluaran (termasuk batal/rusak)
                                        </div>
                                    </div>

                                    <!-- SECTION 4: SALDO -->
                                    <div class="section-form">
                                        <div class="section-title">
                                            <i class="fas fa-calculator"></i> SECTION 4: SALDO
                                        </div>

                                        @php
                                            $currentSaldo = \App\Models\SaldoNotice::where(
                                                'pengeluaran_id',
                                                $pengeluaranNotice->id,
                                            )->first();
                                        @endphp

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="saldo_nomor_awal">Nomor Awal</label>
                                                    <input type="number" class="form-control" id="saldo_nomor_awal"
                                                        name="saldo_nomor_awal" readonly
                                                        value="{{ $currentSaldo->nomor_awal ?? $pengeluaranNotice->penerimaan->nomor_awal }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="saldo_nomor_akhir">Nomor Akhir</label>
                                                    <input type="number" class="form-control" id="saldo_nomor_akhir"
                                                        name="saldo_nomor_akhir" readonly
                                                        value="{{ $pengeluaranNotice->penerimaan->nomor_akhir }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="saldo_jumlah">Jumlah</label>
                                                    <input type="number" class="form-control" id="saldo_jumlah"
                                                        name="saldo_jumlah" readonly
                                                        value="{{ $currentSaldo->jumlah ?? 0 }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- JUMLAH TOTAL -->
                                    <div class="summary-box">
                                        <h4>JUMLAH TOTAL PENGELUARAN</h4>
                                        <div class="total-value" id="jumlahTotalPengeluaran">
                                            {{ $pengeluaranNotice->jumlah_total }}</div>
                                        <small>Notice</small>
                                        <div class="mt-2">
                                            <small id="formulaDisplay">(Pemakaian: 0 - Batal/Rusak: 0)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <a href="{{ route('admin.pengeluaran-notices.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Pengeluaran
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
        let pemakaianIndex = {{ count($pengeluaranNotice->pemakaianRanges) }};
        let batalRusakIndex = {{ count($pengeluaranNotice->batalRusak) }};

        const penerimaanNomorAwal = {{ $pengeluaranNotice->penerimaan->nomor_awal }};
        const penerimaanNomorAkhir = {{ $pengeluaranNotice->penerimaan->nomor_akhir }};

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
            $('.jumlah-pemakaian').each(function() {
                const item = $(this).closest('.pemakaian-range-item');
                calculatePemakaianJumlah(item);
            });

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
                                <input type="number" class="form-control nomor-awal"
                                       name="pemakaian[${pemakaianIndex}][nomor_awal]"
                                       min="${penerimaanNomorAwal}"
                                       max="${penerimaanNomorAkhir}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nomor Akhir <span class="text-danger">*</span></label>
                                <input type="number" class="form-control nomor-akhir"
                                       name="pemakaian[${pemakaianIndex}][nomor_akhir]"
                                       min="${penerimaanNomorAwal}"
                                       max="${penerimaanNomorAkhir}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="text" class="form-control jumlah-pemakaian" readonly value="0">
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
                        <input type="number" class="form-control batal-rusak-input"
                               name="batal_rusak[]"
                               placeholder="Nomor Notice"
                               min="${penerimaanNomorAwal}"
                               max="${penerimaanNomorAkhir}"
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
