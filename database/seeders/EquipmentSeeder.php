<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Dummy equipment data per kode_prefix PLTA.
     * Format: [unit, system, equipment, kks, assetnum, no_wo?, description?, wo_status?, worktype?]
     *
     * @return array<string, array<int, array{unit: string, system: string, equipment: string, kks: string, assetnum: string, no_wo: string|null, description: string|null, wo_status: string|null, worktype: string|null}>>
     */
    private function equipmentData(): array
    {
        return [

        ];
    }

    public function run(): void
    {
        $validWorktypes = ['CM', 'EJ', 'EV', 'PAM'];
        $abnormalWoStatuses = ['APPR', 'INPRG', 'PTWCL', 'PTWR', 'WPTW'];
        $normalWoStatuses = ['CLOSE', 'COMP'];

        foreach ($this->equipmentData() as $prefix => $items) {
            $plta = Plta::where('kode_prefix', $prefix)->first();

            if (!$plta) {
                continue;
            }

            foreach ($items as $item) {
                $noWo = $item['no_wo'] ?? null;
                $description = $item['description'] ?? null;
                $worktype = $item['worktype'] ?? null;
                $woStatus = $item['wo_status'] ?? null;

                $equipment = Equipment::firstOrCreate(
                    ['assetnum' => $item['assetnum']],
                    [
                        'plta_id' => $plta->id,
                        'unit' => $item['unit'],
                        'system' => $item['system'],
                        'equipment' => $item['equipment'],
                        'kks' => $item['kks'],
                    ]
                );

                // Hitung status otomatis jika ada WO data
                $statusOtomatis = null;
                if ($worktype && in_array($worktype, $validWorktypes) && $woStatus) {
                    if (in_array($woStatus, $abnormalWoStatuses)) {
                        $statusOtomatis = 'abnormal';
                    } elseif (in_array($woStatus, $normalWoStatuses)) {
                        $statusOtomatis = 'normal';
                    }
                }

                EquipmentWo::updateOrCreate(
                    ['equipment_id' => $equipment->id],
                    [
                        'no_wo' => $noWo,
                        'description' => $description,
                        'worktype' => $worktype,
                        'wo_status' => $woStatus,
                        'status_otomatis' => $statusOtomatis,
                        'status_manual' => null,
                        'uploaded_at' => $noWo ? now() : null,
                    ]
                );
            }
        }
    }
}
