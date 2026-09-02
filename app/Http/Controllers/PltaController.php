<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PltaController extends Controller
{
    /**
     * Dummy equipment data per PLTA.
     *
     * @return array<string, array<int, array{unit: string, system: string, equipment: string, kks: string, assetnum: string, status_operasi: string, keterangan: array{no_wo: string, description: string, status: string}}>>
     */
    private function getEquipmentData(): array
    {
        return [
            'sengguruh' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE', 'equipment' => 'Governor Valve',          'kks' => 'BSGR-TG1-MAV10', 'assetnum' => 'BSGR010078', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0892', 'description' => 'Governor Valve Leaking', 'status' => 'INPRG']],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR', 'equipment' => 'Main Transformer',      'kks' => 'BSGR-TG1-EBT10', 'assetnum' => 'BSGR010023', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0875', 'description' => 'Routine Inspection', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Draft Tube Gate',      'kks' => 'BSGR-TG1-MAF20', 'assetnum' => 'BSGR010031', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0861', 'description' => 'Gate Inspection', 'status' => 'COMP']],
                ['unit' => 'UNIT 2', 'system' => 'TURBINE', 'equipment' => 'Runner',                  'kks' => 'BSGR-TG2-MAT10', 'assetnum' => 'BSGR020015', 'status_operasi' => 'Not Ready', 'keterangan' => ['no_wo' => '-', 'description' => 'Penggantian Runner terjadwal', 'status' => 'NOT READY']],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'Excitation System',   'kks' => 'BSGR-TG2-EEX10', 'assetnum' => 'BSGR020044', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0901', 'description' => 'Excitation Fault', 'status' => 'APPR']],
                ['unit' => 'COMMON', 'system' => 'AUXILIARY', 'equipment' => 'Cooling Water Pump',    'kks' => 'BSGR-AUX-MAK10', 'assetnum' => 'BSGR000056', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0845', 'description' => 'Pump Overhaul', 'status' => 'CLOSE']],
                ['unit' => 'COMMON', 'system' => 'PROTECTION', 'equipment' => 'Relay Protection Panel', 'kks' => 'BSGR-AUX-ERP10', 'assetnum' => 'BSGR000067', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0833', 'description' => 'Relay Testing', 'status' => 'COMP']],
            ],
            'sutami' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Penstock Valve',        'kks' => 'BSTM-TG1-MAV10', 'assetnum' => 'BSTM010045', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0888', 'description' => 'Penstock Valve Leak', 'status' => 'INPRG']],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Stator Winding',        'kks' => 'BSTM-TG1-EGS10', 'assetnum' => 'BSTM010062', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0876', 'description' => 'Stator Inspection', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 2', 'system' => 'TURBINE',    'equipment' => 'Spiral Casing',         'kks' => 'BSTM-TG2-MAT20', 'assetnum' => 'BSTM020018', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0862', 'description' => 'Casing Check', 'status' => 'COMP']],
                ['unit' => 'UNIT 2', 'system' => 'MECHANICAL', 'equipment' => 'Thrust Bearing',        'kks' => 'BSTM-TG2-MAB10', 'assetnum' => 'BSTM020033', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0905', 'description' => 'Bearing Vibration High', 'status' => 'WPTW']],
                ['unit' => 'UNIT 3', 'system' => 'TURBINE',    'equipment' => 'Guide Vane',             'kks' => 'BSTM-TG3-MAV20', 'assetnum' => 'BSTM030071', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0841', 'description' => 'Guide Vane Maintenance', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 3', 'system' => 'ELECTRICAL', 'equipment' => 'Power Transformer 3',  'kks' => 'BSTM-TG3-EBT10', 'assetnum' => 'BSTM030088', 'status_operasi' => 'Not Ready', 'keterangan' => ['no_wo' => '-', 'description' => 'Transformator perlu pengujian', 'status' => 'NOT READY']],
                ['unit' => 'COMMON', 'system' => 'AUXILIARY',  'equipment' => 'Air Compressor',        'kks' => 'BSTM-AUX-MAC10', 'assetnum' => 'BSTM000019', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0829', 'description' => 'Compressor PM', 'status' => 'COMP']],
            ],
            'wlingi' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Runner',        'kks' => 'BWLG-TG1-MAT10', 'assetnum' => 'BWLG010012', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0891', 'description' => 'Runner Balancing', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Rotor Winding',         'kks' => 'BWLG-TG1-EGR10', 'assetnum' => 'BWLG010027', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0903', 'description' => 'Rotor Winding Temp High', 'status' => 'PTWCL']],
                ['unit' => 'UNIT 2', 'system' => 'TURBINE',    'equipment' => 'Intake Gate',           'kks' => 'BWLG-TG2-MAF10', 'assetnum' => 'BWLG020034', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0878', 'description' => 'Gate Seal Replacement', 'status' => 'COMP']],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'GIS Panel',             'kks' => 'BWLG-TG2-EGI10', 'assetnum' => 'BWLG020049', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0864', 'description' => 'GIS Maintenance', 'status' => 'CLOSE']],
                ['unit' => 'COMMON', 'system' => 'PROTECTION', 'equipment' => 'SCADA System',          'kks' => 'BWLG-AUX-ESC10', 'assetnum' => 'BWLG000058', 'status_operasi' => 'Not Ready', 'keterangan' => ['no_wo' => '-', 'description' => 'Upgrade SCADA terjadwal', 'status' => 'NOT READY']],
            ],
            'lodoyo' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Shaft',         'kks' => 'BLDY-TG1-MAS10', 'assetnum' => 'BLDY010008', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0853', 'description' => 'Shaft Alignment Check', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Guide Bearing',         'kks' => 'BLDY-TG1-MAB20', 'assetnum' => 'BLDY010022', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0907', 'description' => 'Bearing Oil Leak', 'status' => 'INPRG']],
                ['unit' => 'UNIT 2', 'system' => 'GENERATOR',  'equipment' => 'Generator Stator',      'kks' => 'BLDY-TG2-EGS10', 'assetnum' => 'BLDY020016', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0831', 'description' => 'Stator PM', 'status' => 'COMP']],
            ],
            'tulungagung' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Pelton Wheel',          'kks' => 'BTLA-TG1-MAT10', 'assetnum' => 'BTLA010019', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0867', 'description' => 'Pelton Bucket Inspection', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Deflector',             'kks' => 'BTLA-TG1-MAD10', 'assetnum' => 'BTLA010031', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0894', 'description' => 'Deflector Actuator Fault', 'status' => 'APPR']],
            ],
            'mendalan' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Francis Runner',        'kks' => 'BMDL-TG1-MAT10', 'assetnum' => 'BMDL010014', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0844', 'description' => 'Runner Inspection', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'AVR System',            'kks' => 'BMDL-TG2-EAV10', 'assetnum' => 'BMDL020037', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0898', 'description' => 'AVR Voltage Fluctuation', 'status' => 'PTWR']],
            ],
            'siman' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Gate Valve',    'kks' => 'BSMN-TG1-MAV10', 'assetnum' => 'BSMN010011', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0856', 'description' => 'Valve Maintenance', 'status' => 'COMP']],
                ['unit' => 'UNIT 1', 'system' => 'GENERATOR',  'equipment' => 'Power Factor Relay',    'kks' => 'BSMN-TG1-EPF10', 'assetnum' => 'BSMN010028', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0839', 'description' => 'Relay Calibration', 'status' => 'CLOSE']],
            ],
            'wonorejo' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Kaplan Runner',         'kks' => 'BWRJ-TG1-MAT10', 'assetnum' => 'BWRJ010021', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0911', 'description' => 'Runner Cavitation', 'status' => 'INPRG']],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Oil Pressure Unit',     'kks' => 'BWRJ-TG1-MAO10', 'assetnum' => 'BWRJ010039', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0883', 'description' => 'Oil Pressure Check', 'status' => 'CLOSE']],
            ],
            'plengan' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Nozzle Assembly',       'kks' => 'BPLG-TG1-MAN10', 'assetnum' => 'BPLG010009', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0888', 'description' => 'Nozzle Cleaning', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 2', 'system' => 'ELECTRICAL', 'equipment' => 'Bus Bar Protection',    'kks' => 'BPLG-TG2-EBP10', 'assetnum' => 'BPLG020026', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0872', 'description' => 'Bus Bar Inspection', 'status' => 'COMP']],
            ],
            'lamajan' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Turbine Bearing',       'kks' => 'BLMJ-TG1-MAB10', 'assetnum' => 'BLMJ030021', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0889', 'description' => 'Bearing Temperature High', 'status' => 'INPRG']],
                ['unit' => 'UNIT 2', 'system' => 'GENERATOR',  'equipment' => 'Generator Cooling',     'kks' => 'BLMJ-TG2-EGC10', 'assetnum' => 'BLMJ020047', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0865', 'description' => 'Cooling System PM', 'status' => 'CLOSE']],
            ],
            'cikalong' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Stay Vane',             'kks' => 'BCKG-TG1-MAV30', 'assetnum' => 'BCKG010013', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0848', 'description' => 'Stay Vane Inspection', 'status' => 'COMP']],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Wicket Gate',           'kks' => 'BCKG-TG1-MAW10', 'assetnum' => 'BCKG010029', 'status_operasi' => 'Not Ready', 'keterangan' => ['no_wo' => '-', 'description' => 'Gate Overhaul terjadwal', 'status' => 'NOT READY']],
            ],
            'bengkok' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Crossflow Runner',      'kks' => 'BBGK-TG1-MAT10', 'assetnum' => 'BBGK010007', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0836', 'description' => 'Runner Maintenance', 'status' => 'CLOSE']],
                ['unit' => 'UNIT 1', 'system' => 'ELECTRICAL', 'equipment' => 'Control Panel',         'kks' => 'BBGK-TG1-ECP10', 'assetnum' => 'BBGK010018', 'status_operasi' => 'Abnormal',  'keterangan' => ['no_wo' => 'WO-2024-0915', 'description' => 'Control Panel Fault', 'status' => 'APPR']],
            ],
            'dago' => [
                ['unit' => 'UNIT 1', 'system' => 'TURBINE',    'equipment' => 'Pelton Buckets',        'kks' => 'BDGO-TG1-MAT10', 'assetnum' => 'BDGO010005', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0858', 'description' => 'Bucket Erosion Check', 'status' => 'COMP']],
                ['unit' => 'UNIT 1', 'system' => 'MECHANICAL', 'equipment' => 'Main Stop Valve',       'kks' => 'BDGO-TG1-MAV10', 'assetnum' => 'BDGO010016', 'status_operasi' => 'Normal',    'keterangan' => ['no_wo' => 'WO-2024-0841', 'description' => 'Valve Testing', 'status' => 'CLOSE']],
            ],
        ];
    }

    public function show(string $slug): View|RedirectResponse
    {
        $pltaList = DashboardController::pltaList();

        $currentPlta = collect($pltaList)->firstWhere('slug', $slug);

        if (! $currentPlta) {
            return redirect()->route('dashboard');
        }

        $equipmentData = $this->getEquipmentData();
        $equipments = $equipmentData[$slug] ?? [];

        $statusSummary = [
            'normal' => collect($equipments)->where('status_operasi', 'Normal')->count(),
            'abnormal' => collect($equipments)->where('status_operasi', 'Abnormal')->count(),
            'not_ready' => collect($equipments)->where('status_operasi', 'Not Ready')->count(),
        ];

        return view('plta.show', compact('currentPlta', 'pltaList', 'equipments', 'statusSummary'));
    }
}
