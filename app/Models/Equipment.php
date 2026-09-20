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
     * 1. status_manual (override manual oleh SO/CBM) — prioritas tertinggi
     *    Nilai yang valid: 'normal', 'abnormal', 'not_ready'
     * 2. status_otomatis dari Excel (normal / abnormal)
     * 3. Default: Normal
     *
     * @return 'Normal'|'Abnormal'|'Not Ready'
     */
    public function getStatusOperasiAttribute(): string
    {
        $wos = $this->relationLoaded('wos') ? $this->getRelation('wos') : $this->wos;

        if ($wos->isEmpty()) {
            return 'Normal';
        }

        /** Cek apakah ada WO dengan status_manual yang di-set (override manual SO/CBM). */
        $manualWo = $wos->first(fn ($wo) => $wo->status_manual !== null);

        if ($manualWo !== null) {
            return match ($manualWo->status_manual) {
                'normal' => 'Normal',
                'abnormal' => 'Abnormal',
                'not_ready' => 'Not Ready',
                default => 'Normal',
            };
        }

        /** Fallback ke status otomatis dari data upload Excel. */
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
