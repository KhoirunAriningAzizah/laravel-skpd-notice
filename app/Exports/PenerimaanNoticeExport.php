<?php

namespace App\Exports;

use App\Exports\Sheets\PenerimaanNoticeMonthSheet;
use App\Models\PenerimaanNotice;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PenerimaanNoticeExport implements WithMultipleSheets
{
    protected $userId;
    protected $role;
    protected $layananId;
    protected $dateFrom;
    protected $dateTo;
    protected $kasirId;

    /**
     * Constructor untuk inisialisasi parameter export
     *
     * @param int $userId ID user yang melakukan export
     * @param string $role Role user (kasir/admin)
     * @param int|null $layananId ID layanan untuk admin
     * @param string|null $dateFrom Tanggal awal filter
     * @param string|null $dateTo Tanggal akhir filter
     * @param int|null $kasirId ID kasir untuk filter admin
     */
    public function __construct($userId, $role, $layananId = null, $dateFrom = null, $dateTo = null, $kasirId = null)
    {
        $this->userId = $userId;
        $this->role = $role;
        $this->layananId = $layananId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->kasirId = $kasirId;
    }

    /**
     * Generate sheets berdasarkan bulan yang ada di data
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $months = $this->getAvailableMonths();

        foreach ($months as $month) {
            $sheets[] = new PenerimaanNoticeMonthSheet(
                $this->userId,
                $this->role,
                $this->layananId,
                $month['year'],
                $month['month'],
                $this->kasirId
            );
        }

        return $sheets;
    }

    /**
     * Ambil daftar bulan yang tersedia berdasarkan filter
     *
     * @return array
     */
    protected function getAvailableMonths()
    {
        $query = PenerimaanNotice::query()
            ->select(
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('MONTH(tanggal) as month')
            );

        // Apply role-based filter
        if ($this->role === 'kasir') {
            $query->where('created_by', $this->userId);
        } elseif ($this->role === 'admin' && $this->layananId) {
            $query->whereHas('lokasi', function ($q) {
                $q->where('layanan_id', $this->layananId);
            });

            // Filter by kasir jika ada
            if ($this->kasirId) {
                $query->where('created_by', $this->kasirId);
            }
        }

        // Apply date range filter jika ada
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('tanggal', [$this->dateFrom, $this->dateTo]);
        }

        $months = $query
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->toArray();

        return $months;
    }
}
