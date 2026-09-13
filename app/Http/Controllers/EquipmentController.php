<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Plta;
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
}
