<?php

namespace App\Exports\Sheets;

use App\Models\PenerimaanNotice;
use App\Models\SaldoNotice;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PenerimaanNoticeMonthSheet implements FromCollection, WithTitle, WithStyles, WithEvents
{
    protected $userId;
    protected $role;
    protected $layananId;
    protected $year;
    protected $month;
    protected $kasirId;
    protected $currentRow = 11; // Data mulai dari row 11
    protected $lastDataRow = 11;

    // Kolom layout: A-O (15 kolom)
    // PENERIMAAN: A(NO), B(TGL), C(NOMOR), D(JML)
    // PENGELUARAN: E(TANGGAL), F(PEMAKAIAN NOMOR), G(JUMLAH), H(BATAL/RUSAK NOMOR), I(BATAL/RUSAK JML), J(LOKAL), K(LINK), L(BUKTI KAS JML), M(JML TOTAL)
    // SALDO: N(NOMOR), O(JML)

    public function __construct($userId, $role, $layananId, $year, $month, $kasirId = null)
    {
        $this->userId = $userId;
        $this->role = $role;
        $this->layananId = $layananId;
        $this->year = $year;
        $this->month = $month;
        $this->kasirId = $kasirId;
    }

    /**
     * Nama tab sheet
     */
    public function title(): string
    {
        $monthNames = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGT', 'SEP', 'OKT', 'NOV', 'DES'];
        $yearShort = substr($this->year, -2);
        return $monthNames[$this->month - 1] . ' ' . $yearShort;
    }

    /**
     * Get data collection (kosong karena kita generate manual di events)
     */
    public function collection()
    {
        return collect([]);
    }

    /**
     * Apply styles ke worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [];
    }

    /**
     * Register events untuk generate konten dan styling
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Generate header
                $this->generateHeader($sheet);

                // Generate data rows
                $this->generateDataRows($sheet);

                // Generate footer
                $this->generateFooter($sheet);

                // Apply borders dan styling
                $this->applyBordersAndStyling($sheet);

                // Set column widths
                $this->setColumnWidths($sheet);
            },
        ];
    }

    /**
     * Generate header section (row 1-10)
     */
    protected function generateHeader($sheet)
    {
        $monthNames = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $kasirInfo = $this->getKasirInfo();

        // ============ TITLE SECTION (Row 1-5) ============
        // LEFT SIDE (A-D): Organisasi
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'UPT PELAKSANA TEKNIS');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'PENGELOLAAN PENDAPATAN DAERAH');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', 'NGANJUK');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // RIGHT SIDE (J-O): Info Laporan
        $sheet->mergeCells('J1:O1');
        $sheet->setCellValue('J1', 'LAPORAN : PERSEDIAAN/PENERIMAAN DAN PENGGUNAAN SKPD NOTICE PAJAK');
        $sheet->getStyle('J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('J2:O2');
        $sheet->setCellValue('J2', 'PADA ' . $kasirInfo);
        $sheet->getStyle('J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('J3:O3');
        $sheet->setCellValue('J3', 'BULAN : ' . $monthNames[$this->month - 1] . ' ' . $this->year);
        $sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('J4:O4');
        $sheet->setCellValue('J4', 'BADAN PENDAPATAN DAERAH PROVINSI JAWA TIMUR NGANJUK');
        $sheet->getStyle('J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Style untuk title
        $sheet->getStyle('A1:D3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
        ]);
        $sheet->getStyle('J1:O4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
        ]);

        // ============ ROW 6: Main Group Headers ============
        // PENERIMAAN (A-D) - 4 columns
        $sheet->mergeCells('A6:D6');
        $sheet->setCellValue('A6', 'PENERIMAAN');

        // PENGELUARAN (E-M) - 9 columns
        $sheet->mergeCells('E6:M6');
        $sheet->setCellValue('E6', 'PENGELUARAN');

        // SALDO (N-O) - 2 columns
        $sheet->mergeCells('N6:O6');
        $sheet->setCellValue('N6', 'SALDO');

        // ============ ROW 7: Sub Headers Level 1 ============
        // PENERIMAAN simple sub-headers (NO, TGL, NOMOR, JML)
        $sheet->setCellValue('A7', 'NO');
        $sheet->setCellValue('B7', 'TGL');
        $sheet->setCellValue('C7', 'NOMOR');
        $sheet->setCellValue('D7', 'JML');

        // PENGELUARAN sub-headers
        // PEMAKAIAN spans 3 columns (E-G): TANGGAL, NOMOR, JUMLAH
        $sheet->mergeCells('E7:G7');
        $sheet->setCellValue('E7', 'PEMAKAIAN');
        $sheet->mergeCells('H7:I7');
        $sheet->setCellValue('H7', 'BATAL / RUSAK');
        $sheet->mergeCells('J7:L7');
        $sheet->setCellValue('J7', 'BUKTI KAS');
        $sheet->setCellValue('M7', 'JML TOTAL');

        // SALDO sub-headers
        $sheet->setCellValue('N7', 'NOMOR');
        $sheet->setCellValue('O7', 'JML');

        // ============ ROW 8: Sub Headers Level 2 ============
        // PENERIMAAN - column numbers
        $sheet->setCellValue('A8', '1');
        $sheet->setCellValue('B8', '2');
        $sheet->setCellValue('C8', '3');
        $sheet->setCellValue('D8', '4');

        // PENGELUARAN - sub headers under PEMAKAIAN group
        $sheet->setCellValue('E8', 'TANGGAL');
        $sheet->setCellValue('F8', 'NOMOR');
        $sheet->setCellValue('G8', 'JUMLAH');
        // BATAL/RUSAK sub headers
        $sheet->setCellValue('H8', 'NOMOR');
        $sheet->setCellValue('I8', 'JML');
        // BUKTI KAS sub headers
        $sheet->setCellValue('J8', 'LOKAL');
        $sheet->setCellValue('K8', 'LINK');
        $sheet->setCellValue('L8', 'JML');
        $sheet->setCellValue('M8', '');

        // SALDO - column numbers
        $sheet->setCellValue('N8', '13');
        $sheet->setCellValue('O8', '14');

        // ============ ROW 9: Column Numbers ============
        $sheet->setCellValue('A9', '1');
        $sheet->setCellValue('B9', '2');
        $sheet->setCellValue('C9', '3');
        $sheet->setCellValue('D9', '4');
        // PEMAKAIAN column numbers
        $sheet->setCellValue('E9', '5');
        $sheet->setCellValue('F9', '6');
        $sheet->setCellValue('G9', '7');
        // BATAL/RUSAK column numbers
        $sheet->setCellValue('H9', '8');
        $sheet->setCellValue('I9', '9');
        // BUKTI KAS column numbers
        $sheet->setCellValue('J9', '10');
        $sheet->setCellValue('K9', '11');
        $sheet->setCellValue('L9', '12');
        $sheet->setCellValue('M9', '');
        $sheet->setCellValue('N9', '');
        $sheet->setCellValue('O9', '');

        // Merge vertikal untuk header yang perlu
        $sheet->mergeCells('A7:A9'); // NO
        $sheet->mergeCells('B7:B9'); // TGL
        $sheet->mergeCells('C7:C9'); // NOMOR penerimaan
        $sheet->mergeCells('D7:D9'); // JML penerimaan
        $sheet->mergeCells('M7:M9'); // JML TOTAL pengeluaran
        $sheet->mergeCells('N7:N9'); // SALDO NOMOR
        $sheet->mergeCells('O7:O9'); // SALDO JML

        // Style untuk header rows
        $sheet->getStyle('A6:O9')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Row 10: Saldo Bulan Lalu
        $saldoBulanLalu = $this->getSaldoBulanLalu();
        $saldoNomor = $this->getSaldoNomorBulanLalu();

        $sheet->setCellValue('A10', '');
        $sheet->mergeCells('B10:D10');
        $sheet->setCellValue('B10', 'Saldo Bulan lalu');
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Saldo values
        if ($saldoBulanLalu > 0) {
            $sheet->setCellValue('M10', $saldoBulanLalu);
            $sheet->setCellValue('N10', $saldoNomor);
            $sheet->setCellValue('O10', $saldoBulanLalu);
        }

        $this->currentRow = 11;
    }

    /**
     * Generate data rows
     * Each penerimaan can have multiple pengeluaran, so we use nested loop
     */
    protected function generateDataRows($sheet)
    {
        $row = $this->currentRow;

        // Get data penerimaan
        $penerimaanData = $this->getPenerimaanData();
        $no = 1;

        foreach ($penerimaanData as $penerimaan) {
            // Get ALL pengeluaran for this penerimaan
            $pengeluaranList = $this->getPengeluaranForPenerimaan($penerimaan->id);

            // If no pengeluaran, show penerimaan with empty pengeluaran columns
            if (empty($pengeluaranList)) {
                // Kolom A: No urut
                $sheet->setCellValue('A' . $row, $no);

                // Kolom B: TGL (Tanggal penerimaan)
                $tanggal = Carbon::parse($penerimaan->tanggal)->format('j/n/Y');
                $sheet->setCellValue('B' . $row, $tanggal);

                // Kolom C: Nomor penerimaan
                $nomorPenerimaan = $penerimaan->nomor_awal . ' - ' . $penerimaan->nomor_akhir;
                $sheet->setCellValue('C' . $row, $nomorPenerimaan);

                // Kolom D: Jumlah penerimaan
                $sheet->setCellValue('D' . $row, $penerimaan->jumlah);

                // Empty pengeluaran columns (E-M)
                $sheet->setCellValue('H' . $row, '-');
                $sheet->setCellValue('K' . $row, '-');
                $sheet->setCellValue('M' . $row, 0);

                // Saldo nomor (full penerimaan range since no pengeluaran yet)
                $saldoNomor = $penerimaan->nomor_awal . ' - ' . $penerimaan->nomor_akhir;
                $sheet->setCellValue('N' . $row, $saldoNomor);

                // Saldo jumlah (full penerimaan jumlah since no pengeluaran yet)
                $sheet->setCellValue('O' . $row, $penerimaan->jumlah);

                // Apply row styling
                $this->applyDataRowStyle($sheet, $row);

                $row++;
            } else {
                // Loop through each pengeluaran
                $pengeluaranIndex = 0;
                $totalPengeluaranAllRows = 0;

                foreach ($pengeluaranList as $pengeluaran) {
                    $isFirstPengeluaran = ($pengeluaranIndex === 0);

                    // Kolom A-D: Show penerimaan data ONLY in first row
                    if ($isFirstPengeluaran) {
                        $sheet->setCellValue('A' . $row, $no);
                        $tanggal = Carbon::parse($penerimaan->tanggal)->format('j/n/Y');
                        $sheet->setCellValue('B' . $row, $tanggal);
                        $nomorPenerimaan = $penerimaan->nomor_awal . ' - ' . $penerimaan->nomor_akhir;
                        $sheet->setCellValue('C' . $row, $nomorPenerimaan);
                        $sheet->setCellValue('D' . $row, $penerimaan->jumlah);
                    } else {
                        // Leave columns A-D blank for subsequent pengeluaran rows
                        $sheet->setCellValue('A' . $row, '');
                        $sheet->setCellValue('B' . $row, '');
                        $sheet->setCellValue('C' . $row, '');
                        $sheet->setCellValue('D' . $row, '');
                    }

                    // Kolom E: Tanggal pengeluaran
                    if (!empty($pengeluaran['tanggal'])) {
                        $sheet->setCellValue('E' . $row, $pengeluaran['tanggal']);
                    }

                    // Kolom F: Pemakaian NOMOR
                    if (!empty($pengeluaran['pemakaian_nomor'])) {
                        $sheet->setCellValue('F' . $row, $pengeluaran['pemakaian_nomor']);
                    }

                    // Kolom G: JUMLAH (pemakaian)
                    if (!empty($pengeluaran['pemakaian_jumlah'])) {
                        $sheet->setCellValue('G' . $row, $pengeluaran['pemakaian_jumlah']);
                    }

                    // Kolom H: Batal/Rusak NOMOR
                    $sheet->setCellValue('H' . $row, $pengeluaran['batal_nomor'] ?? '-');

                    // Kolom I: Batal/Rusak JML
                    if (!empty($pengeluaran['batal_jumlah'])) {
                        $sheet->setCellValue('I' . $row, $pengeluaran['batal_jumlah']);
                    }

                    // Kolom J: LOKAL
                    if (!empty($pengeluaran['bukti_kas_lokal'])) {
                        $sheet->setCellValue('J' . $row, $pengeluaran['bukti_kas_lokal']);
                    }

                    // Kolom K: LINK
                    if (!empty($pengeluaran['bukti_kas_link'])) {
                        $sheet->setCellValue('K' . $row, $pengeluaran['bukti_kas_link']);
                    }

                    // Kolom L: Bukti Kas JML
                    if (!empty($pengeluaran['bukti_kas_jumlah'])) {
                        $sheet->setCellValue('L' . $row, $pengeluaran['bukti_kas_jumlah']);
                    }

                    // Hitung total pengeluaran untuk row ini
                    $totalPengeluaran = ($pengeluaran['pemakaian_jumlah'] ?? 0) +
                        ($pengeluaran['batal_jumlah'] ?? 0) +
                        ($pengeluaran['bukti_kas_jumlah'] ?? 0);

                    // Kolom M: JML TOTAL
                    $sheet->setCellValue('M' . $row, $totalPengeluaran);

                    // Track total pengeluaran across all rows
                    $totalPengeluaranAllRows += $totalPengeluaran;

                    // Kolom N: SALDO NOMOR (from database)
                    if (!empty($pengeluaran['saldo_nomor'])) {
                        $sheet->setCellValue('N' . $row, $pengeluaran['saldo_nomor']);
                    }

                    // Kolom O: SALDO JML (from database)
                    if (!empty($pengeluaran['saldo_jumlah'])) {
                        $sheet->setCellValue('O' . $row, $pengeluaran['saldo_jumlah']);
                    }

                    // Apply row styling
                    $this->applyDataRowStyle($sheet, $row);

                    $row++;
                    $pengeluaranIndex++;
                }
            }

            $no++;
        }

        $this->currentRow = $row;
        $this->lastDataRow = $row - 1;
    }

    /**
     * Apply consistent styling to data rows
     */
    protected function applyDataRowStyle($sheet, $row)
    {
        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Right align for numbers
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('M' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('O' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * Generate footer section
     */
    protected function generateFooter($sheet)
    {
        $row = $this->currentRow + 1;

        // Hitung totals
        $totals = $this->calculateTotals();

        // ============ SUMMARY ROWS ============
        // Jumlah Bulan Ini
        $sheet->mergeCells('B' . $row . ':C' . $row);
        $sheet->setCellValue('B' . $row, 'Jumlah Bulan Ini');
        $sheet->setCellValue('D' . $row, $totals['penerimaan_bulan_ini']);
        $sheet->setCellValue('G' . $row, $totals['pengeluaran_pemakaian']);
        $sheet->setCellValue('I' . $row, $totals['pengeluaran_batal']);
        $sheet->setCellValue('M' . $row, $totals['pengeluaran_total']);
        $sheet->getStyle('B' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
        ]);
        $row++;

        // Jumlah s/d Bulan Lalu
        $sheet->mergeCells('B' . $row . ':C' . $row);
        $sheet->setCellValue('B' . $row, 'Jumlah s/d Bulan Lalu');
        $sheet->setCellValue('D' . $row, $totals['penerimaan_sd_bulan_lalu']);
        $sheet->setCellValue('G' . $row, $totals['pengeluaran_sd_bulan_lalu']);
        $sheet->setCellValue('I' . $row, $totals['batal_sd_bulan_lalu']);
        $sheet->setCellValue('M' . $row, $totals['total_sd_bulan_lalu']);
        $sheet->getStyle('B' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
        ]);
        $row++;

        // Jumlah s/d Bulan Ini
        $sheet->mergeCells('B' . $row . ':C' . $row);
        $sheet->setCellValue('B' . $row, 'Jumlah s/d Bulan Ini');
        $sheet->setCellValue('D' . $row, $totals['penerimaan_sd_bulan_ini']);
        $sheet->setCellValue('G' . $row, $totals['pengeluaran_sd_bulan_ini']);
        $sheet->setCellValue('I' . $row, $totals['batal_sd_bulan_ini']);
        $sheet->setCellValue('M' . $row, $totals['total_sd_bulan_ini']);
        $sheet->getStyle('B' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
        ]);
        $row++;

        // ============ SIGNATURE SECTION ============
        $row += 2;
        $signatureStartRow = $row;

        $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // Tanggal akhir bulan untuk tanda tangan
        $lastDay = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->day;
        $tanggalStr = 'Nganjuk, ' . $lastDay . ' ' . $monthNames[$this->month - 1] . ' ' . $this->year;

        // ============ LEFT SIGNATURE (Kolom B-D) ============
        $leftRow = $signatureStartRow;

        $sheet->mergeCells('B' . $leftRow . ':D' . $leftRow);
        $sheet->setCellValue('B' . $leftRow, 'Kepala Unit Pelaksana Teknis');
        $sheet->getStyle('B' . $leftRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $leftRow++;

        $sheet->mergeCells('B' . $leftRow . ':D' . $leftRow);
        $sheet->setCellValue('B' . $leftRow, 'Pengelolaan Pendapatan Daerah');
        $sheet->getStyle('B' . $leftRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $leftRow++;

        $sheet->mergeCells('B' . $leftRow . ':D' . $leftRow);
        $sheet->setCellValue('B' . $leftRow, 'Nganjuk');
        $sheet->getStyle('B' . $leftRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $leftRow++;

        // Space untuk tanda tangan kiri
        $leftRow += 3;

        $sheet->mergeCells('B' . $leftRow . ':D' . $leftRow);
        $sheet->setCellValue('B' . $leftRow, "R I F A ' I, S.E.");
        $sheet->getStyle('B' . $leftRow)->applyFromArray([
            'font' => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $leftRow++;

        $sheet->mergeCells('B' . $leftRow . ':D' . $leftRow);
        $sheet->setCellValue('B' . $leftRow, 'NIP. 19650412 198903 1 011');
        $sheet->getStyle('B' . $leftRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ============ RIGHT SIGNATURE (Kolom K-O) ============
        $rightRow = $signatureStartRow;

        $sheet->mergeCells('K' . $rightRow . ':O' . $rightRow);
        $sheet->setCellValue('K' . $rightRow, $tanggalStr);
        $sheet->getStyle('K' . $rightRow)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $rightRow++;

        $sheet->mergeCells('K' . $rightRow . ':O' . $rightRow);
        $sheet->setCellValue('K' . $rightRow, 'Pengelola Pelayanan Perpajakan');
        $sheet->getStyle('K' . $rightRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $rightRow++;

        $sheet->mergeCells('K' . $rightRow . ':O' . $rightRow);
        $sheet->setCellValue('K' . $rightRow, 'Nganjuk');
        $sheet->getStyle('K' . $rightRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $rightRow++;

        // Space untuk tanda tangan kanan
        $rightRow += 3;

        $sheet->mergeCells('K' . $rightRow . ':O' . $rightRow);
        $sheet->setCellValue('K' . $rightRow, 'ATIDHURTA ANGGAKARA P, S.Sos');
        $sheet->getStyle('K' . $rightRow)->applyFromArray([
            'font' => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $rightRow++;

        $sheet->mergeCells('K' . $rightRow . ':O' . $rightRow);
        $sheet->setCellValue('K' . $rightRow, 'NIP. 19811901 201101 1 009');
        $sheet->getStyle('K' . $rightRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Apply borders untuk semua cells yang ada data
     */
    protected function applyBordersAndStyling($sheet)
    {
        // Borders untuk header dan data area (row 6-lastDataRow)
        $sheet->getStyle('A6:O' . $this->lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Border untuk row 10 (saldo bulan lalu)
        $sheet->getStyle('A10:O10')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * Set column widths
     */
    protected function setColumnWidths($sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(5);   // NO
        $sheet->getColumnDimension('B')->setWidth(10);  // TGL (tanggal penerimaan)
        $sheet->getColumnDimension('C')->setWidth(18);  // NOMOR (range)
        $sheet->getColumnDimension('D')->setWidth(8);   // JML
        $sheet->getColumnDimension('E')->setWidth(10);  // TANGGAL pengeluaran
        $sheet->getColumnDimension('F')->setWidth(18);  // PEMAKAIAN NOMOR
        $sheet->getColumnDimension('G')->setWidth(8);   // JUMLAH
        $sheet->getColumnDimension('H')->setWidth(12);  // BATAL NOMOR
        $sheet->getColumnDimension('I')->setWidth(6);   // BATAL JML
        $sheet->getColumnDimension('J')->setWidth(8);   // LOKAL
        $sheet->getColumnDimension('K')->setWidth(8);   // LINK
        $sheet->getColumnDimension('L')->setWidth(6);   // BUKTI KAS JML
        $sheet->getColumnDimension('M')->setWidth(8);   // JML TOTAL
        $sheet->getColumnDimension('N')->setWidth(18);  // SALDO NOMOR
        $sheet->getColumnDimension('O')->setWidth(8);   // SALDO JML
    }

    /**
     * Get kasir info untuk header
     */
    protected function getKasirInfo()
    {
        $sample = PenerimaanNotice::query()
            ->with(['lokasi.layanan', 'creator'])
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->first();

        if ($sample && $sample->lokasi && $sample->lokasi->layanan) {
            $layananNama = $sample->lokasi->layanan->nama ?? '4103';
            return 'KASIR ' . $layananNama . ' KANTOR BERSAMA SAMSAT NGANJUK';
        }

        return 'KASIR 4103 KANTOR BERSAMA SAMSAT NGANJUK';
    }

    /**
     * Get saldo bulan lalu (jumlah)
     */
    protected function getSaldoBulanLalu()
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;

        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $lastDayPrevMonth = Carbon::createFromDate($prevYear, $prevMonth, 1)->endOfMonth();

        // Total penerimaan sampai bulan lalu
        $totalPenerimaan = PenerimaanNotice::query()
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->where('tanggal', '<=', $lastDayPrevMonth)
            ->sum('jumlah');

        // TODO: Kurangi dengan total pengeluaran sampai bulan lalu
        return $totalPenerimaan;
    }

    /**
     * Get saldo nomor bulan lalu (range format)
     */
    protected function getSaldoNomorBulanLalu()
    {
        $info = $this->getSaldoNomorInfoBulanLalu();
        if ($info) {
            return $info['nomor_awal'] . ' - ' . $info['nomor_akhir'];
        }
        return '';
    }

    /**
     * Get saldo nomor info bulan lalu
     */
    protected function getSaldoNomorInfoBulanLalu()
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;

        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $lastDayPrevMonth = Carbon::createFromDate($prevYear, $prevMonth, 1)->endOfMonth();

        // Ambil penerimaan terakhir dari bulan sebelumnya
        $lastPenerimaan = PenerimaanNotice::query()
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->where('tanggal', '<=', $lastDayPrevMonth)
            ->orderBy('nomor_akhir', 'desc')
            ->first();

        if ($lastPenerimaan) {
            return [
                'nomor_awal' => $lastPenerimaan->nomor_awal,
                'nomor_akhir' => $lastPenerimaan->nomor_akhir,
            ];
        }

        return null;
    }

    /**
     * Get penerimaan data untuk bulan ini
     */
    protected function getPenerimaanData()
    {
        return PenerimaanNotice::query()
            ->with(['lokasi.layanan', 'creator', 'pengeluaran'])
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->orderBy('tanggal', 'asc')
            ->orderBy('nomor_awal', 'asc')
            ->get();
    }

    /**
     * Get pengeluaran untuk penerimaan tertentu
     * Returns array of ALL pengeluaran transactions with aggregated data
     */
    protected function getPengeluaranForPenerimaan($penerimaanId)
    {
        // Get ALL pengeluaran for this penerimaan
        $pengeluaranRecords = \App\Models\PengeluaranNotice::where('penerimaan_id', $penerimaanId)
            ->orderBy('tanggal', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($pengeluaranRecords->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($pengeluaranRecords as $pengeluaran) {
            $pengeluaranData = [];

            // Date from pengeluaran
            $pengeluaranData['tanggal'] = Carbon::parse($pengeluaran->tanggal)->format('j/n/Y');

            // Aggregate ALL pemakaian ranges for this pengeluaran
            $pemakaianRanges = \App\Models\PengeluaranPemakaianRange::where('pengeluaran_id', $pengeluaran->id)
                ->orderBy('nomor_awal', 'asc')
                ->get();

            if ($pemakaianRanges->isNotEmpty()) {
                $minNomor = $pemakaianRanges->min('nomor_awal');
                $maxNomor = $pemakaianRanges->max('nomor_akhir');
                $totalJumlah = $pemakaianRanges->sum('jumlah');

                $pengeluaranData['pemakaian_nomor'] = $minNomor . '-' . $maxNomor;
                $pengeluaranData['pemakaian_jumlah'] = $totalJumlah;
            } else {
                $pengeluaranData['pemakaian_nomor'] = '';
                $pengeluaranData['pemakaian_jumlah'] = 0;
            }

            // Aggregate ALL batal/rusak for this pengeluaran
            $batalRusakRecords = \App\Models\PengeluaranBatalRusak::where('pengeluaran_id', $pengeluaran->id)->get();
            if ($batalRusakRecords->isNotEmpty()) {
                $nomorList = $batalRusakRecords->pluck('nomor_notice')->filter()->toArray();
                $pengeluaranData['batal_nomor'] = !empty($nomorList) ? implode(', ', $nomorList) : '-';
                $pengeluaranData['batal_jumlah'] = $batalRusakRecords->count();
            } else {
                $pengeluaranData['batal_nomor'] = '-';
                $pengeluaranData['batal_jumlah'] = 0;
            }

            // Aggregate ALL bukti kas for this pengeluaran
            $buktiKasRecords = \App\Models\PengeluaranBuktiKas::where('pengeluaran_id', $pengeluaran->id)->get();
            if ($buktiKasRecords->isNotEmpty()) {
                $pengeluaranData['bukti_kas_lokal'] = $buktiKasRecords->sum('lokal');
                $pengeluaranData['bukti_kas_link'] = $buktiKasRecords->sum('link');
                $pengeluaranData['bukti_kas_jumlah'] = $buktiKasRecords->sum('jumlah');
            } else {
                $pengeluaranData['bukti_kas_lokal'] = 0;
                $pengeluaranData['bukti_kas_link'] = 0;
                $pengeluaranData['bukti_kas_jumlah'] = 0;
            }

            // Get saldo from database (already saved)
            $saldo = \App\Models\SaldoNotice::where('pengeluaran_id', $pengeluaran->id)->first();
            if ($saldo) {
                $pengeluaranData['saldo_nomor'] = $saldo->nomor_awal . ' - ' . $saldo->nomor_akhir;
                $pengeluaranData['saldo_jumlah'] = $saldo->jumlah;
            } else {
                $pengeluaranData['saldo_nomor'] = '-';
                $pengeluaranData['saldo_jumlah'] = 0;
            }

            $result[] = $pengeluaranData;
        }

        return $result;
    }

    /**
     * Calculate totals untuk footer
     */
    protected function calculateTotals()
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;

        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $lastDayPrevMonth = Carbon::createFromDate($prevYear, $prevMonth, 1)->endOfMonth();

        // Penerimaan bulan ini
        $penerimaanBulanIni = PenerimaanNotice::query()
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->sum('jumlah');

        // Penerimaan s/d bulan lalu
        $penerimaanSdBulanLalu = PenerimaanNotice::query()
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->where('tanggal', '<=', $lastDayPrevMonth)
            ->sum('jumlah');

        // Calculate pengeluaran totals for bulan ini
        $penerimaanBulanIniIds = PenerimaanNotice::query()
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->pluck('id');

        $pengeluaranBulanIniIds = \App\Models\PengeluaranNotice::whereIn('penerimaan_id', $penerimaanBulanIniIds)->pluck('id');

        $pengeluaranPemakaian = \App\Models\PengeluaranPemakaianRange::whereIn('pengeluaran_id', $pengeluaranBulanIniIds)->sum('jumlah');
        $pengeluaranBatal = \App\Models\PengeluaranBatalRusak::whereIn('pengeluaran_id', $pengeluaranBulanIniIds)->count();
        $pengeluaranBuktiKas = \App\Models\PengeluaranBuktiKas::whereIn('pengeluaran_id', $pengeluaranBulanIniIds)->sum('jumlah');
        $pengeluaranTotal = $pengeluaranPemakaian + $pengeluaranBatal + $pengeluaranBuktiKas;

        // Calculate pengeluaran totals s/d bulan lalu
        $penerimaanSdBulanLaluIds = PenerimaanNotice::query()
            ->when($this->role === 'kasir', function ($q) {
                $q->where('created_by', $this->userId);
            })
            ->when($this->role === 'admin' && $this->layananId, function ($q) {
                $q->whereHas('lokasi', function ($q2) {
                    $q2->where('layanan_id', $this->layananId);
                });
            })
            ->when($this->kasirId, function ($q) {
                $q->where('created_by', $this->kasirId);
            })
            ->where('tanggal', '<=', $lastDayPrevMonth)
            ->pluck('id');

        $pengeluaranSdBulanLaluIds = \App\Models\PengeluaranNotice::whereIn('penerimaan_id', $penerimaanSdBulanLaluIds)->pluck('id');

        $pengeluaranPemakaianSdBulanLalu = \App\Models\PengeluaranPemakaianRange::whereIn('pengeluaran_id', $pengeluaranSdBulanLaluIds)->sum('jumlah');
        $pengeluaranBatalSdBulanLalu = \App\Models\PengeluaranBatalRusak::whereIn('pengeluaran_id', $pengeluaranSdBulanLaluIds)->count();
        $pengeluaranBuktiKasSdBulanLalu = \App\Models\PengeluaranBuktiKas::whereIn('pengeluaran_id', $pengeluaranSdBulanLaluIds)->sum('jumlah');
        $totalSdBulanLalu = $pengeluaranPemakaianSdBulanLalu + $pengeluaranBatalSdBulanLalu + $pengeluaranBuktiKasSdBulanLalu;

        // Calculate s/d bulan ini (cumulative)
        $pengeluaranPemakaianSdBulanIni = $pengeluaranPemakaianSdBulanLalu + $pengeluaranPemakaian;
        $pengeluaranBatalSdBulanIni = $pengeluaranBatalSdBulanLalu + $pengeluaranBatal;
        $totalSdBulanIni = $totalSdBulanLalu + $pengeluaranTotal;

        return [
            'penerimaan_bulan_ini' => $penerimaanBulanIni,
            'penerimaan_sd_bulan_lalu' => $penerimaanSdBulanLalu,
            'penerimaan_sd_bulan_ini' => $penerimaanSdBulanLalu + $penerimaanBulanIni,
            'pengeluaran_pemakaian' => $pengeluaranPemakaian,
            'pengeluaran_batal' => $pengeluaranBatal,
            'pengeluaran_total' => $pengeluaranTotal,
            'pengeluaran_sd_bulan_lalu' => $pengeluaranPemakaianSdBulanLalu,
            'batal_sd_bulan_lalu' => $pengeluaranBatalSdBulanLalu,
            'total_sd_bulan_lalu' => $totalSdBulanLalu,
            'pengeluaran_sd_bulan_ini' => $pengeluaranPemakaianSdBulanIni,
            'batal_sd_bulan_ini' => $pengeluaranBatalSdBulanIni,
            'total_sd_bulan_ini' => $totalSdBulanIni,
        ];
    }
}
