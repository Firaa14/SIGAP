<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Collection;

class CbmEquipmentService
{
    /**
     * Dummy CBM readings kept separate from SO work-order data.
     *
     * @return Collection<int, array<string, string>>
     */
    public function forEquipment(Collection $equipment): Collection
    {
        $thermograph = ['N', 'L', 'M', 'H', 'C'];
        $vibration = ['A', 'B', 'C', 'D'];
        $oilAnalysis = ['N', 'LA', 'HA', 'LF', 'HF'];
        $dga = ['N', 'C', 'A', 'D'];

        return $equipment->values()->map(function (Equipment $item, int $index) use (
            $thermograph,
            $vibration,
            $oilAnalysis,
            $dga
        ): array {
            return [
                'unit' => $item->unit ?? '—',
                'system' => $item->system ?? '—',
                'equipment' => $item->equipment ?? '—',
                'kks' => $item->kks ?? '—',
                'assetnum' => $item->assetnum,
                'thermograph' => $thermograph[$index % count($thermograph)],
                'vibrasi' => $vibration[$index % count($vibration)],
                'oil_analysis' => $oilAnalysis[$index % count($oilAnalysis)],
                'mca' => 'SOON',
                'pd' => 'SOON',
                'dga' => $dga[$index % count($dga)],
                'status' => match ($thermograph[$index % count($thermograph)]) {
                    'C', 'H' => 'Abnormal',
                    'M' => 'Not Ready',
                    default => 'Normal',
                },
            ];
        });
    }

    /**
     * @return array<string, array{label: string, color: string}>
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
            'dga' => [
                'N' => ['label' => 'Normal / Good', 'color' => '#16A34A'],
                'C' => ['label' => 'Caution', 'color' => '#0F766E'],
                'A' => ['label' => 'Abnormal / Fair', 'color' => '#EAB308'],
                'D' => ['label' => 'Danger / Poor', 'color' => '#DC2626'],
            ],
        ];
    }
}
