<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengeluaranPemakaianRange extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_pemakaian_ranges';

    protected $fillable = [
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
     * Get the pengeluaran that owns this pemakaian range.
     */
    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(PengeluaranNotice::class, 'pengeluaran_id');
    }
}
