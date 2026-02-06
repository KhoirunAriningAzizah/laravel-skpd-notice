<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengeluaranBatalRusak extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_batal_rusak';

    protected $fillable = [
        'pengeluaran_id',
        'nomor_notice',
    ];

    protected $casts = [
        'nomor_notice' => 'integer',
    ];

    /**
     * Get the pengeluaran that owns this batal/rusak notice.
     */
    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(PengeluaranNotice::class, 'pengeluaran_id');
    }
}
