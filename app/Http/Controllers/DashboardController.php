<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Plta;
use App\Models\UploadHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public static function pltaList(): array
    {
        return Plta::query()
            ->orderBy('id')
            ->get()
            ->map(fn (Plta $plta): array => [
                'slug' => $plta->slug,
                'name' => $plta->nama_plta,
                'code' => $plta->kode_prefix,
                'location' => $plta->location,
                'capacity' => $plta->capacity,
            ])
            ->all();
    }

    public static function pltaMapData(): array
    {
        $pltas = Plta::query()
            ->orderBy('id')
            ->with('equipments.wo')
            ->get();

        return $pltas->map(function (Plta $plta): array {
            $equipments = $plta->equipments;

            $normal = $equipments
                ->filter(fn (Equipment $e) => $e->status_operasi === 'Normal')
                ->count();

            $abnormal = $equipments
                ->filter(fn (Equipment $e) => $e->status_operasi === 'Abnormal')
                ->count();

            $notReady = $equipments
                ->filter(fn (Equipment $e) => $e->status_operasi === 'Not Ready')
                ->count();

            $overallStatus = match (true) {
                $abnormal > 0 => 'abnormal',
                $notReady > 0 => 'not_ready',
                default => 'normal',
            };

            return [
                'slug' => $plta->slug,
                'name' => $plta->nama_plta,
                'short_name' => str_replace('PLTA ', '', $plta->nama_plta),
                'code' => $plta->kode_prefix,
                'location' => $plta->location,
                'capacity' => $plta->capacity,
                'latitude' => $plta->latitude,
                'longitude' => $plta->longitude,
                'total_equipment' => $equipments->count(),
                'normal' => $normal,
                'abnormal' => $abnormal,
                'not_ready' => $notReady,
                'status' => $overallStatus,
                'url' => route('plta.show', $plta->slug),
            ];
        })->all();
    }

    public function index(): View
    {
        $pltaList = self::pltaList();
        $mapData = self::pltaMapData();

        $totalEquipment = Equipment::count();

        $allEquipments = Equipment::with('wo')->get();

        $normal = $allEquipments
            ->filter(fn (Equipment $e) => $e->status_operasi === 'Normal')
            ->count();

        $abnormal = $allEquipments
            ->filter(fn (Equipment $e) => $e->status_operasi === 'Abnormal')
            ->count();

        $notReady = $allEquipments
            ->filter(fn (Equipment $e) => $e->status_operasi === 'Not Ready')
            ->count();

        $stats = [
            'total_plta' => count($pltaList),
            'total_equipment' => $totalEquipment,
            'normal' => $normal,
            'abnormal' => $abnormal,
            'not_ready' => $notReady,
        ];

        $uploadResult = [];

        $uploadHistoryId = session()->pull('show_upload_result');

        if ($uploadHistoryId) {
            $uploadHistory = UploadHistory::find($uploadHistoryId);

            if ($uploadHistory && $uploadHistory->uploaded_at) {
                $uploadRows = \App\Models\EquipmentWo::with(['equipment.plta'])
                    ->where('uploaded_at', $uploadHistory->uploaded_at)
                    ->orderBy('id')
                    ->get();

                $uploadResult = $uploadRows->map(function ($wo): array {
                    $equipment = $wo->equipment;
                    $plta = $equipment?->plta;

                    return [
                        'time' => $wo->uploaded_at?->format('d M Y H:i') ?? '—',
                        'plta' => $plta?->nama_plta ?? '—',
                        'assetnum' => $equipment?->assetnum ?? '—',
                        'status' => $equipment?->status_operasi ?? '—',
                        'wo' => $wo->no_wo ?? '—',
                        'report_date' => $wo->report_date?->format('d M Y') ?? '—',
                        'total_durasi' => $wo->total_durasi,
                    ];
                })->all();
            }
        }

        return view('dashboard', compact(
            'pltaList',
            'mapData',
            'stats',
            'uploadResult',
        ));
    }

    public function mapData(): JsonResponse
    {
        $mapData = self::pltaMapData();

        $stats = [
            'total_plta' => count($mapData),
            'total_equipment' => array_sum(array_column($mapData, 'total_equipment')),
            'normal' => array_sum(array_column($mapData, 'normal')),
            'abnormal' => array_sum(array_column($mapData, 'abnormal')),
            'not_ready' => array_sum(array_column($mapData, 'not_ready')),
        ];

        return response()->json([
            'stats' => $stats,
            'plta' => $mapData,
            'updated_at' => now()->translatedFormat('D, d M Y H:i:s'),
        ]);
    }
}