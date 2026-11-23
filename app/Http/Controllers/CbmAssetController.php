<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Plta;
use App\Services\CbmEquipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Halaman "Insert Asset CBM" (khusus peran CBM, terpisah dari halaman equipment milik SO).
 *  - Tambah asset baru ke master (PLTA, unit, system, equipment, KKS, ASSETNUM diisi manual).
 *  - Edit asset yang sudah ada lewat /cbm/asset?edit=ASSETNUM (ASSETNUM tidak berubah).
 *  - Isi "nama di file Excel" (alias) dan parameter yang berlaku untuk equipment yang sudah ada.
 */
class CbmAssetController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorizeCbm();

        $pltas = Plta::query()->orderBy('id')->get(['id', 'nama_plta', 'kode_prefix']);

        $unitsByPlta = Equipment::query()
            ->select('plta_id', 'unit')
            ->distinct()
            ->orderBy('unit')
            ->get()
            ->groupBy('plta_id')
            ->map(fn ($rows) => $rows->pluck('unit')->filter()->values()->all());

        // Mode edit: /cbm/asset?edit=BSTM010101
        $edit = $request->query('edit')
            ? Equipment::query()->where('assetnum', (string) $request->query('edit'))->first()
            : null;

        return view('cbm.asset', [
            'pltas' => $pltas,
            'unitsByPlta' => $unitsByPlta,
            'parameters' => CbmEquipmentService::PARAMETERS,
            'hasCbmColumns' => $this->hasCbmColumns(),
            'edit' => $edit,
        ]);
    }

    /** Daftar equipment satu PLTA (untuk mengatur alias dan parameter). */
    public function equipments(Request $request): JsonResponse
    {
        $this->authorizeCbm();

        $data = $request->validate(['plta_id' => ['required', 'integer']]);

        $columns = ['assetnum', 'equipment'];
        if ($this->hasCbmColumns()) {
            $columns[] = 'cbm_alias';
            $columns[] = 'cbm_parameters';
        }

        $rows = Equipment::query()
            ->where('plta_id', (int) $data['plta_id'])
            ->orderBy('assetnum')
            ->get($columns)
            ->map(fn (Equipment $e): array => [
                'assetnum' => $e->assetnum,
                'equipment' => $e->equipment,
                'alias' => (string) ($e->cbm_alias ?? ''),
                'parameters' => array_values(array_filter(explode(',', (string) ($e->cbm_parameters ?? '')))),
            ]);

        return response()->json($rows);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCbm();

        $data = $request->validate([
            'plta_id' => ['required', Rule::exists((new Plta())->getTable(), 'id')],
            'unit' => ['required', 'string', 'max:150'],
            'system' => ['required', 'string', 'max:255'],
            'equipment' => ['required', 'string', 'max:255'],
            'kks' => ['nullable', 'string', 'max:100'],
            'assetnum' => ['required', 'string', 'max:50'],
            'edit_assetnum' => ['nullable', 'string', 'max:50'],
            'parameters' => ['nullable', 'array'],
            'parameters.*' => ['string', Rule::in(array_keys(CbmEquipmentService::PARAMETERS))],
        ], [
            'plta_id.required' => 'Pilih PLTA.',
            'unit.required' => 'Isi unit.',
            'system.required' => 'Isi system.',
            'equipment.required' => 'Isi nama equipment.',
            'assetnum.required' => 'Isi ASSETNUM sesuai nomor resmi (tidak dibuat otomatis).',
        ]);

        // ── Mode edit: ubah alat yang sudah ada, ASSETNUM tidak berubah ──
        if (! empty($data['edit_assetnum'])) {
            $target = Equipment::query()->where('assetnum', $data['edit_assetnum'])->first();

            if (! $target) {
                return back()->withInput()
                    ->withErrors(['assetnum' => 'Alat yang akan diedit tidak ditemukan.']);
            }

            $changes = [
                'plta_id' => (int) $data['plta_id'],
                'unit' => trim($data['unit']),
                'system' => trim($data['system']),
                'equipment' => trim($data['equipment']),
                'kks' => trim((string) ($data['kks'] ?? '')),
            ];

            if ($this->hasCbmColumns()) {
                // Nama equipment baru ditambahkan ke alias, alias lama tetap.
                $changes['cbm_alias'] = $this->normalizeAlias(
                    trim((string) $target->cbm_alias.','.$data['equipment'], ',')
                );
                $changes['cbm_parameters'] = $this->joinParameters($data['parameters'] ?? []);
            }

            $target->forceFill($changes)->save();

            return redirect()->route('cbm.asset.create')
                ->with('status', "Asset {$target->assetnum} berhasil diperbarui.");
        }

        // ── Mode tambah: asset baru, ASSETNUM diisi manual ──
        $assetnum = strtoupper(trim($data['assetnum']));

        if (Equipment::query()->where('assetnum', $assetnum)->exists()) {
            return back()->withInput()->withErrors(['assetnum' => "ASSETNUM {$assetnum} sudah terdaftar."]);
        }

        $attributes = [
            'plta_id' => (int) $data['plta_id'],
            'unit' => trim($data['unit']),
            'system' => trim($data['system']),
            'equipment' => trim($data['equipment']),
            'kks' => trim((string) ($data['kks'] ?? '')),
            'assetnum' => $assetnum,
        ];

        if ($this->hasCbmColumns()) {
            $attributes['cbm_alias'] = $this->normalizeAlias($data['equipment']);
            $attributes['cbm_parameters'] = $this->joinParameters($data['parameters'] ?? []);
        }

        Equipment::forceCreate($attributes);

        return redirect()->route('cbm.asset.create')
            ->with('status', "Asset {$assetnum} berhasil ditambahkan.");
    }

    /** Simpan alias dan parameter yang berlaku untuk equipment yang sudah ada. */
    public function updateAlias(Request $request): RedirectResponse
    {
        $this->authorizeCbm();

        if (! $this->hasCbmColumns()) {
            return back()->withErrors(['assetnum' => 'Kolom CBM belum ada di database. Jalankan migrasinya dulu.']);
        }

        $data = $request->validate([
            'assetnum' => ['required', 'string'],
            'cbm_alias' => ['nullable', 'string', 'max:255'],
            'parameters' => ['nullable', 'array'],
            'parameters.*' => ['string', Rule::in(array_keys(CbmEquipmentService::PARAMETERS))],
        ], [
            'assetnum.required' => 'Pilih equipment yang akan diatur.',
        ]);

        $equipment = Equipment::query()->where('assetnum', $data['assetnum'])->first();

        if (! $equipment) {
            return back()->withErrors(['assetnum' => 'Equipment tidak ditemukan.']);
        }

        $equipment->forceFill([
            'cbm_alias' => $this->normalizeAlias($data['cbm_alias'] ?? null),
            'cbm_parameters' => $this->joinParameters($data['parameters'] ?? []),
        ])->save();

        return redirect()->route('cbm.asset.create')
            ->with('status', "Alias dan parameter {$equipment->assetnum} berhasil disimpan.");
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function normalizeAlias(?string $alias): ?string
    {
        $parts = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $alias)))));

        return $parts === [] ? null : implode(', ', $parts);
    }

    /** @param  array<int, string>  $parameters */
    private function joinParameters(array $parameters): ?string
    {
        $valid = array_values(array_intersect(array_keys(CbmEquipmentService::PARAMETERS), $parameters));

        return $valid === [] ? null : implode(',', $valid);
    }

    private function hasCbmColumns(): bool
    {
        $table = (new Equipment())->getTable();

        return Schema::hasColumn($table, 'cbm_alias') && Schema::hasColumn($table, 'cbm_parameters');
    }

    private function authorizeCbm(): void
    {
        abort_unless(session('login_role') === 'CBM', 403);
    }
}