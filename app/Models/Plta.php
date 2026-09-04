<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plta extends Model
{
    protected $fillable = [
        'nama_plta',
        'kode_prefix',
        'slug',
        'location',
        'capacity',
    ];

    /**
     * @return HasMany<Equipment, $this>
     */
    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
