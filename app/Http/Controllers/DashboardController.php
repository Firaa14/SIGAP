<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use App\Models\UploadHistory;
use Illuminate\Http\JsonResponse;
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

    /**
     * Data peta: 13 PLTA lengkap dengan koordinat dan status agregat
     * (dihitung dari status_operasi seluruh equipment miliknya).
     *
     * Status prioritas (mengikuti urutan keparahan):
     *   1. abnormal  → jika ada minimal 1 equipment Abnormal
     *   2. not_ready → jika tidak ada Abnormal tapi ada minimal 1 Not Ready
     *   3. normal    → jika semua equipment Normal (atau PLTA belum punya equipment)
     *
     * @return array<int, array<string, mixed>>
     */
    public static function pltaMapData(): array
    {
        $pltas = Plta::query()->orderBy('id')->with('equipments.wo')->get();

        return $pltas->map(function (Plta $plta): array {
            $equipments = $plta->equipments;

            $normal = $equipments->filter(fn (Equipment $e) => $e->status_operasi === 'Normal')->count();
            $abnormal = $equipments->filter(fn (Equipment $e) => $e->status_operasi === 'Abnormal')->count();
            $notReady = $equipments->filter(fn (Equipment $e) => $e->status_operasi === 'Not Ready')->count();

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
                'report_date' => $wo->report_date?->format('d M Y') ?? '—',
                'total_durasi' => $wo->total_durasi !== null ? $wo->total_durasi.' hari' : '—',
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
            'mapData',
            'stats',
            'recentActivity',
            'lastUpload',
            'pltaDistribution',
        ));
    }

    /**
     * Endpoint JSON untuk auto-refresh peta + stat card secara berkala (polling)
     * tanpa perlu reload seluruh halaman.
     */
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
