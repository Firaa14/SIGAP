<?php

namespace App\Http\Controllers;

use App\Models\EquipmentWo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentStatusController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'normal');

        if (! in_array($status, ['normal', 'abnormal'], true)) {
            $status = 'normal';
        }

        $statusOperasi = $status === 'normal'
            ? 'Normal'
            : 'Abnormal';

        $equipmentWos = EquipmentWo::with(['equipment.plta'])
            ->whereNotNull('uploaded_at')
            ->orderByDesc('uploaded_at')
            ->get();

        $data = $equipmentWos
            ->filter(function (EquipmentWo $wo) use ($statusOperasi) {
                return ($wo->equipment?->status_operasi ?? 'Normal') === $statusOperasi;
            })
            ->values()
            ->map(function (EquipmentWo $wo): array {
                $equipment = $wo->equipment;
                $plta = $equipment?->plta;

                return [
                    'time' => $wo->uploaded_at?->format('d M Y H:i') ?? '—',
                    'plta' => $plta?->nama_plta ?? '—',
                    'assetnum' => $equipment?->assetnum ?? '—',
                    'status' => $equipment?->status_operasi ?? '—',
                    'wo' => $wo->no_wo ?? '—',
                    'report_date' => $wo->report_date?->format('d M Y') ?? '—',
                    'total_durasi' => $wo->total_durasi !== null ? $wo->total_durasi.' hari' : '—',
                ];
            });

        return view('equipment-status.index', [
            'status' => $status,
            'data' => $data,
        ]);
    }
}
