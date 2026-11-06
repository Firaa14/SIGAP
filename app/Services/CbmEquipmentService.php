<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CbmEquipmentService
{
    public const PARAMETERS = [
        'thermograph' => 'Thermograph',
        'vibrasi' => 'Vibrasi',
        'oil_analysis' => 'Oil Analysis',
        'mca' => 'MCA',
        'pd' => 'PD',
        'dga' => 'DGA',
    ];

    /**
     * Status terbaru per parameter per equipment dari cbm_measurements.
     * Parameter yang belum punya data tampil 'SOON'.
     *
     * @return Collection<int, array<string, string>>
     */
    public function forEquipment(Collection $equipment): Collection
    {
        $assetnums = $equipment->pluck('assetnum')->filter()->unique()->values()->all();

        $latest = collect();

        if ($assetnums !== []) {
            $latest = DB::table('cbm_measurements as m')
                ->joinSub(
                    DB::table('cbm_measurements')
                        ->select('assetnum', 'parameter', DB::raw('MAX(measured_at) as max_at'))
                        ->whereIn('assetnum', $assetnums)
                        ->groupBy('assetnum', 'parameter'),
                    'x',
                    fn ($join) => $join->on('m.assetnum', '=', 'x.assetnum')
                        ->on('m.parameter', '=', 'x.parameter')
                        ->on('m.measured_at', '=', 'x.max_at')
                )
                ->select('m.assetnum', 'm.parameter', 'm.status_code')
                ->get()
                ->groupBy('assetnum');
        }

        return $equipment->values()->map(function (Equipment $item) use ($latest): array {
            $rows = ($latest[$item->assetnum] ?? collect())->pluck('status_code', 'parameter');
            $get = fn (string $p): string => $rows[$p] ?? 'SOON';

            $thermo = $get('thermograph');

            return [
                'unit' => $item->unit ?? '—',
                'system' => $item->system ?? '—',
                'equipment' => $item->equipment ?? '—',
                'kks' => $item->kks ?? '—',
                'assetnum' => $item->assetnum,
                'thermograph' => $thermo,
                'vibrasi' => $get('vibrasi'),
                'oil_analysis' => $get('oil_analysis'),
                'mca' => $get('mca'),
                'pd' => $get('pd'),
                'dga' => $get('dga'),
                'status' => match ($thermo) {
                    'C', 'H' => 'Abnormal',
                    'M', 'SOON' => 'Not Ready',
                    default => 'Normal',
                },
            ];
        });
    }

    /**
     * @return array<string, array<string, array{label: string, color: string}>>
     */
    public function statusLegend(): array
    {
        return [
            'thermograph' => [
                'N' => ['label' => 'NORMAL', 'color' => '#0F766E'],
                'L' => ['label' => 'LOW', 'color' => '#16A34A'],
                'M' => ['label' => 'MEDIUM', 'color' => '#EAB308'],
                'H' => ['label' => 'HIGH', 'color' => '#7C3AED'],
                'C' => ['label' => 'CRITICAL', 'color' => '#DC2626'],
            ],
            'vibrasi' => [
                'A' => ['label' => 'Normal', 'color' => '#16A34A'],
                'B' => ['label' => 'Long-term allowable', 'color' => '#EAB308'],
                'C' => ['label' => 'Short-term allowable', 'color' => '#F97316'],
                'D' => ['label' => 'Cause damage', 'color' => '#DC2626'],
            ],
            'oil_analysis' => [
                'N' => ['label' => 'Normal', 'color' => '#16A34A'],
                'LA' => ['label' => 'Low Alert', 'color' => '#0F766E'],
                'HA' => ['label' => 'High Alert', 'color' => '#EAB308'],
                'LF' => ['label' => 'Low Fault', 'color' => '#7C3AED'],
                'HF' => ['label' => 'High Fault', 'color' => '#DC2626'],
            ],
            'mca' => [
                'H' => ['label' => 'Good (>54 dB)', 'color' => '#16A34A'],
                'K' => ['label' => 'Fair (45-54 dB)', 'color' => '#EAB308'],
                'O' => ['label' => 'Poor (40-45 dB)', 'color' => '#F97316'],
                'M' => ['label' => 'Bad (<40 dB)', 'color' => '#DC2626'],
            ],
            'pd' => [
                'N' => ['label' => 'Normal', 'color' => '#16A34A'],
                'L' => ['label' => 'Low', 'color' => '#0F766E'],
                'H' => ['label' => 'High', 'color' => '#7C3AED'],
                'C' => ['label' => 'Caution (delta >125%)', 'color' => '#EAB308'],
                'D' => ['label' => 'Danger (delta >200%)', 'color' => '#DC2626'],
            ],
            'dga' => [
                'N' => ['label' => 'Normal / Good', 'color' => '#16A34A'],
                'C' => ['label' => 'Caution', 'color' => '#0F766E'],
                'A' => ['label' => 'Abnormal / Fair', 'color' => '#EAB308'],
                'D' => ['label' => 'Danger / Poor', 'color' => '#DC2626'],
            ],
        ];
    }
}