<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengeluaranBuktiKas extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_bukti_kas';

    protected $fillable = [
        'pengeluaran_id',
        'lokal',
        'link',
        'jumlah',
    ];

    protected $casts = [
        'lokal' => 'integer',
        'link' => 'integer',
        'jumlah' => 'integer',
    ];

    /**
     * Get the pengeluaran that owns this bukti kas.
     */
    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(PengeluaranNotice::class, 'pengeluaran_id');
    }
}
