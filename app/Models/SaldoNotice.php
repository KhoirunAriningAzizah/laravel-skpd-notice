<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoNotice extends Model
{
    use HasFactory;

    protected $table = 'saldo_notices';

    protected $fillable = [
        'penerimaan_id',
        'pengeluaran_id',
        'nomor_awal',
        'nomor_akhir',
        'jumlah',
    ];

    protected $casts = [
        'nomor_awal' => 'integer',
        'nomor_akhir' => 'integer',
        'jumlah' => 'integer',
    ];

    /**
     * Get the penerimaan that owns this saldo.
     */
    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanNotice::class, 'penerimaan_id');
    }

    /**
     * Get the pengeluaran that owns this saldo.
     */
    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(PengeluaranNotice::class, 'pengeluaran_id');
    }
}
