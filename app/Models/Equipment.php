<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipment extends Model
{
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
     * @return HasOne<EquipmentWo, $this>
     */
    public function wo(): HasOne
    {
        return $this->hasOne(EquipmentWo::class);
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
        $wo = $this->relationLoaded('wo') ? $this->getRelation('wo') : $this->wo;

        if (! $wo) {
            return 'Normal';
        }

        if ($wo->status_manual === 'not_ready') {
            return 'Not Ready';
        }

        return match ($wo->status_otomatis) {
            'abnormal' => 'Abnormal',
            default => 'Normal',
        };
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
