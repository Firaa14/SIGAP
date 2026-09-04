<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentWo extends Model
{
    protected $table = 'equipment_wo';

    protected $fillable = [
        'equipment_id',
        'no_wo',
        'description',
        'worktype',
        'wo_status',
        'status_otomatis',
        'status_manual',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Format keterangan untuk presentation layer:
     * "{no_wo} — {description} [{wo_status}]"
     */
    public function getKeteranganAttribute(): string
    {
        if (! $this->no_wo && ! $this->description) {
            return '—';
        }

        $parts = array_filter([
            $this->no_wo,
            $this->description,
            $this->wo_status ? "[{$this->wo_status}]" : null,
        ]);

        return implode(' — ', $parts);
    }
}
