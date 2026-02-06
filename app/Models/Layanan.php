<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $fillable = [
        'kode_kasir',
        'nama',
    ];

    /**
     * Get all lokasi that belong to this layanan.
     */
    public function lokasis(): HasMany
    {
        return $this->hasMany(Lokasi::class);
    }

    /**
     * Get all users that belong to this layanan.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
