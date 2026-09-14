<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use App\Models\UploadHistory;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Daftar 13 PLTA dengan kode prefix ASSETNUM.
     *
     * @return array<int, array{slug: string, name: string, code: string, location: string, capacity: string}>
     */
    public static function pltaList(): array
    {
        return Plta::query()->orderBy('id')->get()->map(fn (Plta $plta): array => [
            'slug' => $plta->slug,
            'name' => $plta->nama_plta,
            'code' => $plta->kode_prefix,
            'location' => $plta->location,
            'capacity' => $plta->capacity,
        ])->all();
    }

    public function index(): View
    {
        $pltaList = self::pltaList();

        // ── Stats dari DB ─────────────────────────────────────────────────────
        $totalEquipment = Equipment::count();

        // Hitung status operasi: ambil semua equipment dengan relasi wo
        $allEquipments = Equipment::with('wo')->get();

        $normal = $allEquipments->filter(fn (Equipment $e) => $e->status_operasi === 'Normal')->count();
        $abnormal = $allEquipments->filter(fn (Equipment $e) => $e->status_operasi === 'Abnormal')->count();
        $notReady = $allEquipments->filter(fn (Equipment $e) => $e->status_operasi === 'Not Ready')->count();

        $stats = [
            'total_plta' => count($pltaList),
            'total_equipment' => $totalEquipment,
            'normal' => $normal,
            'abnormal' => $abnormal,
            'not_ready' => $notReady,
        ];

        // ── Recent Activity dari DB (10 WO terbaru berdasarkan uploaded_at) ───
        $recentWos = EquipmentWo::with(['equipment.plta'])
            ->whereNotNull('uploaded_at')
            ->orderByDesc('uploaded_at')
            ->limit(10)
            ->get();

        $recentActivity = $recentWos->map(function (EquipmentWo $wo): array {
            $equipment = $wo->equipment;
            $plta = $equipment?->plta;

            // Tentukan status_operasi melalui accessor di Equipment
            $statusOperasi = $equipment?->status_operasi ?? 'Normal';

            return [
                'time' => $wo->uploaded_at?->format('d M Y H:i') ?? '—',
                'plta' => $plta?->nama_plta ?? '—',
                'assetnum' => $equipment?->assetnum ?? '—',
                'status' => $statusOperasi,
                'wo' => $wo->no_wo ?? '—',
            ];
        })->all();

        // ── Upload terakhir ───────────────────────────────────────────────────
        $lastUpload = UploadHistory::with('user')
            ->orderByDesc('uploaded_at')
            ->first();

        // Distribusi PLTA dari upload terakhir dalam urutan canonical
        $pltaDistribution = null;
        if ($lastUpload) {
            $canonicalNames = collect($pltaList)->pluck('name');
            $rawDistribution = $lastUpload->plta_distribution ?? [];
            $pltaDistribution = $canonicalNames->map(fn (string $name) => [
                'name' => $name,
                'count' => $rawDistribution[$name] ?? 0,
            ])->filter(fn (array $item) => $item['count'] > 0)->values();
        }

        return view('dashboard', compact(
            'pltaList',
            'stats',
            'recentActivity',
            'lastUpload',
            'pltaDistribution',
        ));
    }
}
