<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Plta;
use App\Services\CbmEquipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PltaController extends Controller
{
    public function show(string $slug, CbmEquipmentService $cbmService): View|RedirectResponse
    {
        $pltaList = DashboardController::pltaList();

        $currentPlta = collect($pltaList)->firstWhere('slug', $slug);

        if (!$currentPlta) {
            return redirect()->route('dashboard');
        }

        // Ambil Plta model + semua equipment + relasi wos dalam 2 query (eager load)
        /** @var Plta|null $pltaModel */
        $pltaModel = Plta::where('slug', $slug)
            ->with(['equipments.wos'])
            ->first();

        // Map Equipment model ke format array yang dipakai view
        $equipments = $pltaModel
            ? $pltaModel->equipments
                ->sortByDesc(fn(Equipment $eq) => $eq->wos->first()?->uploaded_at?->timestamp ?? 0)
                ->values()
                ->map(fn(Equipment $eq): array => [
                    'unit' => $eq->unit,
                    'system' => $eq->system,
                    'equipment' => $eq->equipment,
                    'kks' => $eq->kks ?? '—',
                    'assetnum' => $eq->assetnum,
                    'status_operasi' => $eq->status_operasi,  // via accessor
                    'keterangan' => $eq->wos
                     ->filter(fn($wo) => !empty($wo->no_wo) || !empty($wo->description))
                     ->map(fn($wo) => [
                    'no_wo' => $wo->no_wo ?? '-',
                    'description' => $wo->description ?? '—',
                    'status' => $wo->wo_status ?? '',
    ])->values()->all(),
                    'report_date' => $eq->wos->first()?->report_date?->format('d M Y') ?? '—',
                    'durasi_hari' => $eq->wos->first()?->durasi_hari !== null ? $eq->wos->first()->durasi_hari . ' hari' : '—',
                ])->all()
            : [];

        $canEditStatus = session('login_role') === 'SO';
        $isCbm = session('login_role') === 'CBM';
        $isPublicView = ! auth()->check();
        $isReadOnlyView = ! $canEditStatus;
        $cbmEquipments = ($isCbm || $isReadOnlyView)
            ? $cbmService->forEquipment($pltaModel?->equipments ?? collect())
            : collect();

        // Kode status CBM yang valid (selain ini dihitung sebagai Not Measured)
        $cbmCodes = ['N', 'L', 'M', 'H', 'C'];

        $statusSummary = $isCbm
            ? [
                'N'  => $cbmEquipments->where('thermograph', 'N')->count(),
                'L'  => $cbmEquipments->where('thermograph', 'L')->count(),
                'M'  => $cbmEquipments->where('thermograph', 'M')->count(),
                'H'  => $cbmEquipments->where('thermograph', 'H')->count(),
                'C'  => $cbmEquipments->where('thermograph', 'C')->count(),
                'NM' => $cbmEquipments
                    ->filter(fn ($e) => ! in_array($e['thermograph'] ?? null, $cbmCodes, true))
                    ->count(),
            ]
            : [
                'normal' => collect($equipments)->where('status_operasi', 'Normal')->count(),
                'abnormal' => collect($equipments)->where('status_operasi', 'Abnormal')->count(),
                'not_ready' => collect($equipments)->where('status_operasi', 'Not Ready')->count(),
            ];
        $cbmStatusLegend = $cbmService->statusLegend();

        return view('plta.show', compact(
            'currentPlta',
            'pltaList',
            'equipments',
            'statusSummary',
            'canEditStatus',
            'isCbm',
            'isPublicView',
            'isReadOnlyView',
            'cbmEquipments',
            'cbmStatusLegend'
        ));
    }
}