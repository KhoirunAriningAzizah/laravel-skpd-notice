<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengeluaranNotice extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_notices';

    protected $fillable = [
        'penerimaan_id',
        'tanggal',
        'jumlah_total',
        'lokasi_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_total' => 'integer',
        'lokasi_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Get the penerimaan that owns this pengeluaran.
     */
    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanNotice::class, 'penerimaan_id');
    }

    /**
     * Get the user who created this pengeluaran (kasir).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the layanan/lokasi for this pengeluaran.
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'lokasi_id');
    }

    /**
     * Get all pemakaian ranges for this pengeluaran.
     */
    public function pemakaianRanges(): HasMany
    {
        return $this->hasMany(PengeluaranPemakaianRange::class, 'pengeluaran_id');
    }

    /**
     * Get all batal/rusak notices for this pengeluaran.
     */
    public function batalRusak(): HasMany
    {
        return $this->hasMany(PengeluaranBatalRusak::class, 'pengeluaran_id');
    }

    /**
     * Get all bukti kas for this pengeluaran.
     */
    public function buktiKas(): HasMany
    {
        return $this->hasMany(PengeluaranBuktiKas::class, 'pengeluaran_id');
    }

    /**
     * Get the saldo for this pengeluaran.
     */
    public function saldo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SaldoNotice::class, 'pengeluaran_id');
    }

    /**
     * Count pengeluaran created by user on specific date for a specific penerimaan.
     */
    public static function countTodayPengeluaranByUser(int $userId, string $date, int $penerimaanId): int
    {
        return self::where('created_by', $userId)
            ->where('penerimaan_id', $penerimaanId)
            ->whereDate('tanggal', $date)
            ->count();
    }
}
