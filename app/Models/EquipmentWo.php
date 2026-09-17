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
        'report_date',
        'durasi_hari',
        'uploaded_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'durasi_hari' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function getDurasiHariAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        if ($this->report_date === null || $this->uploaded_at === null) {
            return null;
        }

        return $this->report_date->copy()->startOfDay()->diffInDays(
            $this->uploaded_at->copy()->startOfDay()
        );
    }

    public function getKeteranganAttribute(): string
    {
        if (!$this->no_wo && !$this->description) {
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
