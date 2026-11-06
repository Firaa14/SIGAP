<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use App\Models\UploadHistory;
use App\Services\CbmEquipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Lama (menit) tab parameter CBM tampil setelah upload. */
    private const CBM_TAB_WINDOW_MINUTES = 10;

    /**
     * Daftar 13 PLTA dengan kode prefix ASSETNUM.
     *
     * @return array<int, array{slug: string, name: string, code: string, location: string, capacity: string}>
     */
    public static function pltaList(): array
    {
        return Plta::query()->orderBy('id')->get()->map(fn(Plta $plta): array => [
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
        $pltas = Plta::query()->orderBy('id')->with('equipments.wos')->get();
        $isCbm = session('login_role') === 'CBM';
        $cbmService = $isCbm ? new CbmEquipmentService() : null;

        return $pltas->map(function (Plta $plta) use ($isCbm, $cbmService): array {
            $equipments = $plta->equipments;
            $cbmEquipments = $isCbm && $cbmService
                ? $cbmService->forEquipment($equipments)
                : collect();

            $normal = $isCbm
                ? $cbmEquipments->where('status', 'Normal')->count()
                : $equipments->filter(fn(Equipment $e) => $e->status_operasi === 'Normal')->count();
            $abnormal = $isCbm
                ? $cbmEquipments->where('status', 'Abnormal')->count()
                : $equipments->filter(fn(Equipment $e) => $e->status_operasi === 'Abnormal')->count();
            $notReady = $isCbm
                ? $cbmEquipments->where('status', 'Not Ready')->count()
                : $equipments->filter(fn(Equipment $e) => $e->status_operasi === 'Not Ready')->count();

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

    /**
     * Khusus peran CBM: jumlah equipment yang sudah punya data uji per parameter
     * (Thermograph, Vibrasi, Oil Analysis, MCA, PD, DGA).
     *
     * @return array<int, array{key: string, label: string, total: int}>
     */
    private static function cbmParameterStats(): array
    {
        $counts = Schema::hasTable('cbm_measurements')
            ? DB::table('cbm_measurements')
                ->select('parameter', DB::raw('COUNT(DISTINCT assetnum) as total'))
                ->groupBy('parameter')
                ->pluck('total', 'parameter')
            : collect();

        return collect(CbmEquipmentService::PARAMETERS)
            ->map(fn($label, $key): array => [
                'key' => (string) $key,
                'label' => $label,
                'total' => (int) ($counts[$key] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Khusus peran CBM: parameter yang baru saja diupload
     * (dalam CBM_TAB_WINDOW_MINUTES menit terakhir).
     * Dipakai untuk menampilkan tab sementara di tabel aktivitas.
     *
     * @return array<int, string>
     */
    private static function cbmRecentUploadedParameters(): array
    {
        if (! Schema::hasTable('cbm_measurements')) {
            return [];
        }

        return DB::table('cbm_measurements')
            ->where('updated_at', '>=', now()->subMinutes(self::CBM_TAB_WINDOW_MINUTES))
            ->distinct()
            ->pluck('parameter')
            ->map(fn($p): string => (string) $p)
            ->all();
    }

    /**
     * Khusus peran CBM: data uji untuk tabel aktivitas.
     * - Tanpa filter  : 10 upload terbaru dari semua parameter.
     * - Dengan filter : status terakhir tiap equipment pada parameter itu (maks. 500).
     *
     * @return array<int, array<string, string>>
     */
    private static function cbmActivity(?string $filter): array
    {
        if (! Schema::hasTable('cbm_measurements')) {
            return [];
        }

        if ($filter !== null) {
            $rows = DB::table('cbm_measurements')
                ->where('parameter', $filter)
                ->orderByDesc('measured_at')
                ->orderByDesc('updated_at')
                ->get()
                ->unique('assetnum')
                ->take(500)
                ->values();
        } else {
            $rows = DB::table('cbm_measurements')
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get();
        }

        $equipments = Equipment::with('plta')
            ->whereIn('assetnum', $rows->pluck('assetnum')->all())
            ->get()
            ->keyBy('assetnum');

        $legend = (new CbmEquipmentService())->statusLegend();

        return $rows->map(function ($r) use ($equipments, $legend): array {
            $meta = $legend[$r->parameter][$r->status_code] ?? null;

            return [
                'time' => \Carbon\Carbon::parse($r->updated_at)->format('d M Y H:i'),
                'plta' => $equipments->get($r->assetnum)?->plta?->nama_plta ?? '—',
                'assetnum' => $r->assetnum,
                'parameter' => CbmEquipmentService::PARAMETERS[$r->parameter] ?? $r->parameter,
                'code' => $r->status_code,
                'label' => $meta['label'] ?? '',
                'color' => $meta['color'] ?? '#64748b',
                'measured_at' => \Carbon\Carbon::parse($r->measured_at)->format('d M Y'),
            ];
        })->all();
    }

    public function index(Request $request): View
    {
        $pltaList = self::pltaList();
        $mapData = self::pltaMapData();
        $isCbm = session('login_role') === 'CBM';

        // ── Stats dari DB ─────────────────────────────────────────────────────
        $totalEquipment = $isCbm ? array_sum(array_column($mapData, 'total_equipment')) : Equipment::count();

        // Hitung status operasi: ambil semua equipment dengan relasi wos
        $allEquipments = $isCbm ? collect() : Equipment::with('wos')->get();

        $normal = $isCbm ? array_sum(array_column($mapData, 'normal'))
            : $allEquipments->filter(fn(Equipment $e) => $e->status_operasi === 'Normal')->count();
        $abnormal = $isCbm ? array_sum(array_column($mapData, 'abnormal'))
            : $allEquipments->filter(fn(Equipment $e) => $e->status_operasi === 'Abnormal')->count();
        $notReady = $isCbm ? array_sum(array_column($mapData, 'not_ready'))
            : $allEquipments->filter(fn(Equipment $e) => $e->status_operasi === 'Not Ready')->count();

        $stats = [
            'total_plta' => count($pltaList),
            'total_equipment' => $totalEquipment,
            'normal' => $normal,
            'abnormal' => $abnormal,
            'not_ready' => $notReady,
        ];

        // Kartu per parameter CBM (null untuk peran selain CBM).
        $cbmParameterStats = $isCbm ? self::cbmParameterStats() : null;

        // Parameter yang baru saja diupload (untuk tab sementara; kosong untuk peran selain CBM).
        $cbmRecentUploads = $isCbm ? self::cbmRecentUploadedParameters() : [];

        // Parameter yang dipilih lewat kartu (hanya peran CBM, hanya nilai yang dikenal).
        $selectedParameter = null;
        if ($isCbm) {
            $requested = (string) $request->query('parameter', '');
            $selectedParameter = array_key_exists($requested, CbmEquipmentService::PARAMETERS) ? $requested : null;
        }
        $cbmActivity = $isCbm ? self::cbmActivity($selectedParameter) : null;

        // ── Recent Activity dari DB (10 WO terbaru berdasarkan uploaded_at) ───
        $recentWos = $isCbm ? collect() : EquipmentWo::with(['equipment.plta'])
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
                'durasi_hari' => $wo->durasi_hari !== null ? $wo->durasi_hari . ' hari' : '—',
            ];
        })->all();

        // ── Upload terakhir ───────────────────────────────────────────────────
        $lastUpload = $isCbm ? null : UploadHistory::with('user')
            ->orderByDesc('uploaded_at')
            ->first();

        // Distribusi PLTA dari upload terakhir dalam urutan canonical
        $pltaDistribution = null;
        if ($lastUpload) {
            $canonicalNames = collect($pltaList)->pluck('name');
            $rawDistribution = $lastUpload->plta_distribution ?? [];
            $pltaDistribution = $canonicalNames->map(fn(string $name) => [
                'name' => $name,
                'count' => $rawDistribution[$name] ?? 0,
            ])->filter(fn(array $item) => $item['count'] > 0)->values();
        }

        return view('dashboard', compact(
            'pltaList',
            'mapData',
            'stats',
            'cbmParameterStats',
            'cbmRecentUploads',
            'cbmActivity',
            'selectedParameter',
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