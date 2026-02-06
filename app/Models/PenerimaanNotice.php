<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanNotice extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_notices';

    protected $fillable = [
        'tanggal',
        'nomor_awal',
        'nomor_akhir',
        'jumlah',
        'lokasi_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nomor_awal' => 'integer',
        'nomor_akhir' => 'integer',
        'jumlah' => 'integer',
        'lokasi_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Get the user who created this penerimaan (kasir).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the layanan/lokasi for this penerimaan.
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    /**
     * Get all pengeluaran for this penerimaan.
     */
    public function pengeluaran(): HasMany
    {
        return $this->hasMany(PengeluaranNotice::class, 'penerimaan_id');
    }

    /**
     * Get all saldo for this penerimaan.
     */
    public function saldo(): HasMany
    {
        return $this->hasMany(SaldoNotice::class, 'penerimaan_id');
    }

    /**
     * Get the latest penerimaan for a specific user (kasir).
     */
    public static function getLatestPenerimaanForUser(int $userId)
    {
        return self::where('created_by', $userId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Check if this penerimaan has remaining saldo.
     */
    public function hasSaldoRemaining(): bool
    {
        // Cek apakah ada saldo_notices
        $saldoExists = $this->saldo()->exists();

        if ($saldoExists) {
            // Jika ada saldo_notices, cek jumlahnya
            $totalSaldo = $this->saldo()->sum('jumlah');
            return $totalSaldo > 0;
        } else {
            // Jika belum ada saldo_notices, berarti belum pernah ada pengeluaran
            // Cek apakah ada pengeluaran untuk penerimaan ini
            $hasPengeluaran = $this->pengeluaran()->exists();

            if (!$hasPengeluaran) {
                // Belum ada pengeluaran sama sekali, berarti saldo penuh
                return true;
            }

            // Ada pengeluaran tapi tidak ada saldo, berarti habis
            return false;
        }
    }

    /**
     * Get total saldo for this penerimaan.
     */
    public function getTotalSaldo(): int
    {
        // Cek apakah ada saldo_notices
        $saldoExists = $this->saldo()->exists();

        if ($saldoExists) {
            // Jika ada saldo_notices, hitung jumlahnya
            return $this->saldo()->sum('jumlah');
        } else {
            // Jika belum ada saldo_notices, cek apakah belum ada pengeluaran
            $hasPengeluaran = $this->pengeluaran()->exists();

            if (!$hasPengeluaran) {
                // Belum ada pengeluaran, berarti saldo = jumlah penerimaan
                return $this->jumlah;
            }

            // Ada pengeluaran tapi tidak ada saldo, berarti 0
            return 0;
        }
    }
}
