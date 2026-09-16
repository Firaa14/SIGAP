<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Plta;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PltaController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        $pltaList = DashboardController::pltaList();

        $currentPlta = collect($pltaList)->firstWhere('slug', $slug);

        if (! $currentPlta) {
            return redirect()->route('dashboard');
        }

        // Ambil Plta model + semua equipment + relasi wo dalam 2 query (eager load)
        /** @var Plta|null $pltaModel */
        $pltaModel = Plta::where('slug', $slug)
            ->with(['equipments.wo'])
            ->first();

        // Map Equipment model ke format array yang dipakai view
        $equipments = $pltaModel
            ? $pltaModel->equipments->map(fn (Equipment $eq): array => [
                'unit' => $eq->unit,
                'system' => $eq->system,
                'equipment' => $eq->equipment,
                'kks' => $eq->kks ?? '—',
                'assetnum' => $eq->assetnum,
                'status_operasi' => $eq->status_operasi,  // via accessor
                'keterangan' => [
                    'no_wo' => $eq->wo?->no_wo ?? '-',
                    'description' => $eq->wo?->description ?? '—',
                    'status' => $eq->wo?->wo_status ?? '',
                ],
                'report_date' => $eq->wo?->report_date?->format('d M Y') ?? '—',
                'durasi_hari' => $eq->wo?->durasi_hari !== null ? $eq->wo->durasi_hari.' hari' : '—',
            ])->values()->all()
            : [];

        $statusSummary = [
            'normal' => collect($equipments)->where('status_operasi', 'Normal')->count(),
            'abnormal' => collect($equipments)->where('status_operasi', 'Abnormal')->count(),
            'not_ready' => collect($equipments)->where('status_operasi', 'Not Ready')->count(),
        ];

        return view('plta.show', compact('currentPlta', 'pltaList', 'equipments', 'statusSummary'));
    }
}
