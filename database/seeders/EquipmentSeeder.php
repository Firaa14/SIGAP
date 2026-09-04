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
            'BSGR' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Governor Valve',       'kks' => 'BSGR-TG1-MAV10', 'assetnum' => 'BSGR010078', 'no_wo' => 'WO-2024-0892', 'description' => 'Governor Valve Leaking',        'wo_status' => 'INPRG',  'worktype' => 'CM'],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Main Transformer',     'kks' => 'BSGR-TG1-EBT10', 'assetnum' => 'BSGR010023', 'no_wo' => 'WO-2024-0875', 'description' => 'Routine Inspection',           'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Draft Tube Gate',      'kks' => 'BSGR-TG1-MAF20', 'assetnum' => 'BSGR010031', 'no_wo' => 'WO-2024-0861', 'description' => 'Gate Inspection',              'wo_status' => 'COMP',   'worktype' => 'EV'],
                ['unit' => 'UNIT 2', 'system' => 'TURBINE',    'equipment' => 'Runner',               'kks' => 'BSGR-TG2-MAT10', 'assetnum' => 'BSGR020015', 'no_wo' => null,           'description' => null,                           'wo_status' => null,     'worktype' => null],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'Excitation System',    'kks' => 'BSGR-TG2-EEX10', 'assetnum' => 'BSGR020044', 'no_wo' => 'WO-2024-0901', 'description' => 'Excitation Fault',             'wo_status' => 'APPR',   'worktype' => 'CM'],
                ['unit' => 'COMMON', 'system' => 'AUXILIARY',  'equipment' => 'Cooling Water Pump',   'kks' => 'BSGR-AUX-MAK10', 'assetnum' => 'BSGR000056', 'no_wo' => 'WO-2024-0845', 'description' => 'Pump Overhaul',                'wo_status' => 'CLOSE',  'worktype' => 'EJ'],
            ],
            'BSTM' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Penstock Valve',       'kks' => 'BSTM-TG1-MAV10', 'assetnum' => 'BSTM010045', 'no_wo' => 'WO-2024-0888', 'description' => 'Penstock Valve Leak',          'wo_status' => 'INPRG',  'worktype' => 'CM'],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Stator Winding',       'kks' => 'BSTM-TG1-EGS10', 'assetnum' => 'BSTM010062', 'no_wo' => 'WO-2024-0876', 'description' => 'Stator Inspection',            'wo_status' => 'CLOSE',  'worktype' => 'EV'],
                ['unit' => 'UNIT 2', 'system' => 'TURBINE',    'equipment' => 'Spiral Casing',        'kks' => 'BSTM-TG2-MAT20', 'assetnum' => 'BSTM020018', 'no_wo' => 'WO-2024-0862', 'description' => 'Casing Check',                 'wo_status' => 'COMP',   'worktype' => 'PAM'],
                ['unit' => 'UNIT 2', 'system' => 'MECHANICAL', 'equipment' => 'Thrust Bearing',       'kks' => 'BSTM-TG2-MAB10', 'assetnum' => 'BSTM020033', 'no_wo' => 'WO-2024-0905', 'description' => 'Bearing Vibration High',        'wo_status' => 'WPTW',   'worktype' => 'CM'],
                ['unit' => 'UNIT 3', 'system' => 'TURBINE',    'equipment' => 'Guide Vane',           'kks' => 'BSTM-TG3-MAV20', 'assetnum' => 'BSTM030071', 'no_wo' => 'WO-2024-0841', 'description' => 'Guide Vane Maintenance',        'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
                ['unit' => 'UNIT 3', 'system' => 'ELECTRICAL', 'equipment' => 'Power Transformer 3',  'kks' => 'BSTM-TG3-EBT10', 'assetnum' => 'BSTM030088', 'no_wo' => null,           'description' => null,                           'wo_status' => null,     'worktype' => null],
                ['unit' => 'COMMON', 'system' => 'AUXILIARY',  'equipment' => 'Air Compressor',       'kks' => 'BSTM-AUX-MAC10', 'assetnum' => 'BSTM000019', 'no_wo' => 'WO-2024-0829', 'description' => 'Compressor PM',                'wo_status' => 'COMP',   'worktype' => 'PAM'],
            ],
            'BWLG' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Runner',       'kks' => 'BWLG-TG1-MAT10', 'assetnum' => 'BWLG010012', 'no_wo' => 'WO-2024-0891', 'description' => 'Runner Balancing',             'wo_status' => 'CLOSE',  'worktype' => 'EV'],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Rotor Winding',        'kks' => 'BWLG-TG1-EGR10', 'assetnum' => 'BWLG010027', 'no_wo' => 'WO-2024-0903', 'description' => 'Rotor Winding Temp High',      'wo_status' => 'PTWCL',  'worktype' => 'CM'],
                ['unit' => 'UNIT 2', 'system' => 'TURBINE',    'equipment' => 'Intake Gate',          'kks' => 'BWLG-TG2-MAF10', 'assetnum' => 'BWLG020034', 'no_wo' => 'WO-2024-0878', 'description' => 'Gate Seal Replacement',        'wo_status' => 'COMP',   'worktype' => 'EJ'],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'GIS Panel',            'kks' => 'BWLG-TG2-EGI10', 'assetnum' => 'BWLG020049', 'no_wo' => 'WO-2024-0864', 'description' => 'GIS Maintenance',              'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
                ['unit' => 'COMMON', 'system' => 'PROTECTION', 'equipment' => 'SCADA System',         'kks' => 'BWLG-AUX-ESC10', 'assetnum' => 'BWLG000058', 'no_wo' => null,           'description' => null,                           'wo_status' => null,     'worktype' => null],
            ],
            'BLDY' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Shaft',        'kks' => 'BLDY-TG1-MAS10', 'assetnum' => 'BLDY010008', 'no_wo' => 'WO-2024-0853', 'description' => 'Shaft Alignment Check',         'wo_status' => 'CLOSE',  'worktype' => 'EV'],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Guide Bearing',        'kks' => 'BLDY-TG1-MAB20', 'assetnum' => 'BLDY010022', 'no_wo' => 'WO-2024-0907', 'description' => 'Bearing Oil Leak',             'wo_status' => 'INPRG',  'worktype' => 'CM'],
                ['unit' => 'UNIT 2', 'system' => 'GENERATOR',  'equipment' => 'Generator Stator',     'kks' => 'BLDY-TG2-EGS10', 'assetnum' => 'BLDY020016', 'no_wo' => 'WO-2024-0831', 'description' => 'Stator PM',                    'wo_status' => 'COMP',   'worktype' => 'PAM'],
            ],
            'BSMN' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Gate Valve',   'kks' => 'BSMN-TG1-MAV10', 'assetnum' => 'BSMN010011', 'no_wo' => 'WO-2024-0856', 'description' => 'Valve Maintenance',            'wo_status' => 'COMP',   'worktype' => 'EJ'],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Power Factor Relay',   'kks' => 'BSMN-TG1-EPF10', 'assetnum' => 'BSMN010028', 'no_wo' => 'WO-2024-0839', 'description' => 'Relay Calibration',            'wo_status' => 'CLOSE',  'worktype' => 'EV'],
            ],
            'BMDL' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Francis Runner',       'kks' => 'BMDL-TG1-MAT10', 'assetnum' => 'BMDL010014', 'no_wo' => 'WO-2024-0844', 'description' => 'Runner Inspection',            'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'AVR System',           'kks' => 'BMDL-TG2-EAV10', 'assetnum' => 'BMDL020037', 'no_wo' => 'WO-2024-0898', 'description' => 'AVR Voltage Fluctuation',       'wo_status' => 'PTWR',   'worktype' => 'CM'],
            ],
            'BWNJ' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Kaplan Runner',        'kks' => 'BWNJ-TG1-MAT10', 'assetnum' => 'BWNJ010021', 'no_wo' => 'WO-2024-0911', 'description' => 'Runner Cavitation',            'wo_status' => 'INPRG',  'worktype' => 'EJ'],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Oil Pressure Unit',    'kks' => 'BWNJ-TG1-MAO10', 'assetnum' => 'BWNJ010039', 'no_wo' => 'WO-2024-0883', 'description' => 'Oil Pressure Check',           'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
            ],
            'BTLG' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Pelton Wheel',         'kks' => 'BTLG-TG1-MAT10', 'assetnum' => 'BTLG010019', 'no_wo' => 'WO-2024-0867', 'description' => 'Pelton Bucket Inspection',      'wo_status' => 'CLOSE',  'worktype' => 'EV'],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Deflector',            'kks' => 'BTLG-TG1-MAD10', 'assetnum' => 'BTLG010031', 'no_wo' => 'WO-2024-0894', 'description' => 'Deflector Actuator Fault',      'wo_status' => 'APPR',   'worktype' => 'CM'],
            ],
            'BNBL' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Pelton Runner',        'kks' => 'BNBL-TG1-MAT10', 'assetnum' => 'BNBL010009', 'no_wo' => 'WO-2024-0871', 'description' => 'Runner Inspection',            'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
                ['unit' => 'UNIT 1', 'system' => 'ELECTRICAL', 'equipment' => 'Control Relay Panel',  'kks' => 'BNBL-TG1-ERP10', 'assetnum' => 'BNBL010017', 'no_wo' => 'WO-2024-0913', 'description' => 'Relay Fault',                  'wo_status' => 'APPR',   'worktype' => 'CM'],
            ],
            'BGLG' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Francis Turbine',      'kks' => 'BGLG-TG1-MAT10', 'assetnum' => 'BGLG010011', 'no_wo' => 'WO-2024-0855', 'description' => 'Turbine Overhaul',             'wo_status' => 'COMP',   'worktype' => 'EJ'],
                ['unit' => 'UNIT 2', 'system' => 'GENERATOR',  'equipment' => 'Generator Rotor',      'kks' => 'BGLG-TG2-EGR10', 'assetnum' => 'BGLG020024', 'no_wo' => 'WO-2024-0909', 'description' => 'Rotor Vibration',              'wo_status' => 'INPRG',  'worktype' => 'CM'],
            ],
            'BGRG' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Kaplan Turbine',       'kks' => 'BGRG-TG1-MAT10', 'assetnum' => 'BGRG010007', 'no_wo' => 'WO-2024-0847', 'description' => 'Turbine Blade Inspection',     'wo_status' => 'CLOSE',  'worktype' => 'EV'],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Thrust Bearing',       'kks' => 'BGRG-TG1-MAB10', 'assetnum' => 'BGRG010019', 'no_wo' => 'WO-2024-0916', 'description' => 'Bearing Oil Temperature High', 'wo_status' => 'WPTW',   'worktype' => 'CM'],
            ],
            'BAMG' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Crossflow Turbine',    'kks' => 'BAMG-TG1-MAT10', 'assetnum' => 'BAMG010005', 'no_wo' => 'WO-2024-0863', 'description' => 'Turbine Maintenance',          'wo_status' => 'CLOSE',  'worktype' => 'PAM'],
                ['unit' => 'UNIT 1', 'system' => 'ELECTRICAL', 'equipment' => 'AVR System',           'kks' => 'BAMG-TG1-EAV10', 'assetnum' => 'BAMG010018', 'no_wo' => null,           'description' => null,                           'wo_status' => null,     'worktype' => null],
            ],
            'BSLJ' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Pelton Turbine',       'kks' => 'BSLJ-TG1-MAT10', 'assetnum' => 'BSLJ010003', 'no_wo' => 'WO-2024-0879', 'description' => 'Pelton Nozzle Service',         'wo_status' => 'COMP',   'worktype' => 'EJ'],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Main Generator',       'kks' => 'BSLJ-TG1-EGM10', 'assetnum' => 'BSLJ010014', 'no_wo' => 'WO-2024-0919', 'description' => 'Generator Insulation Test',    'wo_status' => 'APPR',   'worktype' => 'EV'],
            ],
        ];
    }

    public function run(): void
    {
        $validWorktypes = ['CM', 'EJ', 'EV', 'PAM'];
        $abnormalWoStatuses = ['APPR', 'INPRG', 'PTWCL', 'PTWR', 'WPTW'];
        $normalWoStatuses = ['CLOSE', 'COMP'];

        foreach ($this->equipmentData() as $prefix => $items) {
            $plta = Plta::where('kode_prefix', $prefix)->first();

            if (! $plta) {
                continue;
            }

            foreach ($items as $item) {
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
                if ($item['worktype'] && in_array($item['worktype'], $validWorktypes) && $item['wo_status']) {
                    if (in_array($item['wo_status'], $abnormalWoStatuses)) {
                        $statusOtomatis = 'abnormal';
                    } elseif (in_array($item['wo_status'], $normalWoStatuses)) {
                        $statusOtomatis = 'normal';
                    }
                }

                EquipmentWo::updateOrCreate(
                    ['equipment_id' => $equipment->id],
                    [
                        'no_wo' => $item['no_wo'],
                        'description' => $item['description'],
                        'worktype' => $item['worktype'],
                        'wo_status' => $item['wo_status'],
                        'status_otomatis' => $statusOtomatis,
                        'status_manual' => null,
                        'uploaded_at' => $item['no_wo'] ? now() : null,
                    ]
                );
            }
        }
    }
}
