<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'plta_id',
        'unit',
        'system',
        'equipment',
        'kks',
        'assetnum',
    ];

    /**
     * @return BelongsTo<Plta, $this>
     */
    public function plta(): BelongsTo
    {
        return $this->belongsTo(Plta::class);
    }

    /**
     * @return HasMany<EquipmentWo, $this>
     */
    public function wos(): HasMany
    {
        return $this->hasMany(EquipmentWo::class);
    }

    /**
     * Hitung status operasi yang ditampilkan berdasarkan:
     * 1. status_manual (not_ready) — prioritas tertinggi
     * 2. status_otomatis dari Excel (normal / abnormal)
     * 3. Default: normal
     *
     * @return 'Normal'|'Abnormal'|'Not Ready'
     */
    public function getStatusOperasiAttribute(): string
    {
        $wos = $this->relationLoaded('wos') ? $this->getRelation('wos') : $this->wos;

        if ($wos->isEmpty()) {
            return 'Normal';
        }

        if ($wos->contains(fn ($wo) => $wo->status_manual === 'not_ready')) {
            return 'Not Ready';
        }

        if ($wos->contains(fn ($wo) => $wo->status_otomatis === 'abnormal')) {
            return 'Abnormal';
        }

        return 'Normal';
    }

    /**
     * Scope: cari berdasarkan ASSETNUM (exact, case-insensitive DB level).
     *
     * @param  Builder<Equipment>  $query
     */
    public function scopeByAssetnum(Builder $query, string $assetnum): Builder
    {
        return $query->where('assetnum', $assetnum);
    }
}