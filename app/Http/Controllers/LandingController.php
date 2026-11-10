<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\UploadHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Tampilkan landing page DIKKP.
     * Data statistik diambil langsung dari database (sama seperti DashboardController).
     */
    public function index(): View
    {
        // ── Stats dari DB ─────────────────────────────────────────────────────
        $totalEquipment = Equipment::count();

        $allEquipments = Equipment::with('wos')->get();

        $normal   = $allEquipments->filter(fn(Equipment $e) => $e->status_operasi === 'Normal')->count();
        $abnormal = $allEquipments->filter(fn(Equipment $e) => $e->status_operasi === 'Abnormal')->count();
        $notReady = $allEquipments->filter(fn(Equipment $e) => $e->status_operasi === 'Not Ready')->count();

        // Ambil data PLTA + koordinat + status untuk peta interaktif
        $mapData = DashboardController::pltaMapData();

        $totalPlta = count($mapData);

        // Waktu pembaruan data terakhir (berdasarkan upload history terakhir)
        $lastUpload = UploadHistory::orderByDesc('uploaded_at')->first();
        $lastUpdatedAt = $lastUpload?->uploaded_at?->translatedFormat('D, d M Y H:i') ?? null;

        $stats = [
            'total_plta'      => $totalPlta,
            'total_equipment' => $totalEquipment,
            'normal'          => $normal,
            'abnormal'        => $abnormal,
            'not_ready'       => $notReady,
        ];

        $hasData = $totalEquipment > 0;

        // ── Hasil uji CBM per parameter (Thermograph, Vibrasi, Oil Analysis, MCA, PD, DGA) ──
        $cbmParameterStats = DashboardController::cbmParameterStats();
        $cbmUpdatedAt = self::cbmUpdatedAt();

        return view('landing', compact(
            'stats',
            'mapData',
            'lastUpdatedAt',
            'hasData',
            'cbmParameterStats',
            'cbmUpdatedAt',
        ));
    }

    /**
     * Endpoint JSON untuk refresh statistik + peta di landing page (polling berkala).
     * Menggunakan data yang sama dengan endpoint dashboard/map-data.
     */
    public function statsData(): \Illuminate\Http\JsonResponse
    {
        $mapData = DashboardController::pltaMapData();

        $stats = [
            'total_plta'      => count($mapData),
            'total_equipment' => array_sum(array_column($mapData, 'total_equipment')),
            'normal'          => array_sum(array_column($mapData, 'normal')),
            'abnormal'        => array_sum(array_column($mapData, 'abnormal')),
            'not_ready'       => array_sum(array_column($mapData, 'not_ready')),
        ];

        $lastUpload = UploadHistory::orderByDesc('uploaded_at')->first();

        // Jumlah equipment yang sudah diuji per parameter CBM: ['thermograph' => 394, ...]
        $cbm = collect(DashboardController::cbmParameterStats())->pluck('total', 'key')->all();

        return response()->json([
            'stats'      => $stats,
            'plta'       => $mapData,
            'updated_at' => now()->translatedFormat('D, d M Y H:i:s'),
            'last_upload' => $lastUpload?->uploaded_at?->translatedFormat('D, d M Y H:i') ?? null,
            'cbm'        => $cbm,
            'cbm_updated_at' => self::cbmUpdatedAt(),
        ]);
    }

    /** Waktu data CBM terakhir diperbarui (null bila belum ada data CBM). */
    private static function cbmUpdatedAt(): ?string
    {
        if (! Schema::hasTable('cbm_measurements')) {
            return null;
        }

        $at = DB::table('cbm_measurements')->max('updated_at');

        return $at ? Carbon::parse($at)->translatedFormat('D, d M Y H:i') : null;
    }
}