<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Plta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function create(): View
    {
        $pltas = Plta::query()
            ->orderBy('nama_plta')
            ->get(['id', 'nama_plta']);

        return view('equipment.create', compact('pltas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plta_id' => ['required', 'integer', 'exists:pltas,id'],
            'unit' => ['required', 'string', 'max:50'],
            'system' => ['required', 'string', 'max:100'],
            'equipment' => ['required', 'string', 'max:150'],
            'kks' => ['nullable', 'string', 'max:100'],
            'assetnum' => ['required', 'string', 'max:50', 'unique:equipments,assetnum'],
        ]);

        Equipment::create($validated);

        return redirect()
            ->route('equipment.create')
            ->with('success', 'Equipment berhasil ditambahkan.');
    }

    /**
     * Update status operasi secara manual oleh SO atau CBM.
     *
     * Hanya user yang login dengan role SO atau CBM yang diizinkan.
     * Perubahan disimpan ke kolom status_manual pada semua WO milik equipment.
     * Status manual akan di-reset (null) ketika ada upload Excel terbaru.
     */
    public function updateStatus(Request $request, string $assetnum): JsonResponse
    {
        if (! in_array(session('login_role'), ['SO', 'CBM'], true)) {
            return response()->json([
                'error' => 'Akses ditolak. Hanya SO atau CBM yang dapat mengubah Status Operasi.',
            ], 403);
        }

        $request->validate([
            'status' => ['required', 'in:Normal,Abnormal,Not Ready'],
        ]);

        $equipment = Equipment::byAssetnum($assetnum)->firstOrFail();

        $statusManual = match ($request->string('status')->value()) {
            'Normal' => 'normal',
            'Abnormal' => 'abnormal',
            'Not Ready' => 'not_ready',
        };

        $equipment->wos()->update(['status_manual' => $statusManual]);

        return response()->json([
            'success' => true,
            'assetnum' => $assetnum,
            'status' => $request->input('status'),
        ]);
    }
}
