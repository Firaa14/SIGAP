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
 *  - Tambah asset baru ke master (PLTA, unit, system, equipment, KKS, ASSETNUM).
 *  - Isi "nama di file Excel" (alias) dan parameter yang berlaku untuk equipment yang sudah ada.
 */
class CbmAssetController extends Controller
{
    public function create(): View
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

        return view('cbm.asset', [
            'pltas' => $pltas,
            'unitsByPlta' => $unitsByPlta,
            'parameters' => CbmEquipmentService::PARAMETERS,
            'hasCbmColumns' => $this->hasCbmColumns(),
        ]);
    }

    /** Saran ASSETNUM berikutnya: B + kode PLTA + nomor unit 2 digit + nomor urut 4 digit. */
    public function next(Request $request): JsonResponse
    {
        $this->authorizeCbm();

        $data = $request->validate([
            'plta_id' => ['required', 'integer'],
            'unit' => ['required', 'string', 'max:150'],
        ]);

        return response()->json([
            'assetnum' => $this->nextAssetnum((int) $data['plta_id'], $data['unit']),
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
            'assetnum' => ['nullable', 'string', 'max:50'],
            'cbm_alias' => ['nullable', 'string', 'max:255'],
            'parameters' => ['nullable', 'array'],
            'parameters.*' => ['string', Rule::in(array_keys(CbmEquipmentService::PARAMETERS))],
        ], [
            'plta_id.required' => 'Pilih PLTA.',
            'unit.required' => 'Isi unit.',
            'system.required' => 'Isi system.',
            'equipment.required' => 'Isi nama equipment.',
        ]);

        $assetnum = strtoupper(trim((string) ($data['assetnum'] ?? '')));
        if ($assetnum === '') {
            $assetnum = (string) $this->nextAssetnum((int) $data['plta_id'], $data['unit']);
        }

        if ($assetnum === '') {
            return back()->withInput()->withErrors([
                'assetnum' => 'ASSETNUM tidak bisa dibuat otomatis dari unit ini. Isi ASSETNUM secara manual.',
            ]);
        }

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
            $attributes['cbm_alias'] = $this->normalizeAlias($data['cbm_alias'] ?? null);
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

    private function nextAssetnum(int $pltaId, string $unit): ?string
    {
        $plta = Plta::query()->find($pltaId);
        $number = $this->unitNumber($unit);

        if (! $plta || $number === null || ! $plta->kode_prefix) {
            return null;
        }

        $prefix = strtoupper($plta->kode_prefix).sprintf('%02d', $number);
        $length = strlen($prefix) + 4;

        $max = Equipment::query()
            ->where('assetnum', 'like', $prefix.'%')
            ->pluck('assetnum')
            ->map(fn ($a): string => strtoupper((string) $a))
            ->filter(fn (string $a): bool => strlen($a) === $length && ctype_digit(substr($a, strlen($prefix))))
            ->map(fn (string $a): int => (int) substr($a, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.sprintf('%04d', $max + 1);
    }

    /** "PLTA SENGGURUH COMMON" → 0, "PLTA AMPELGADING UNIT 1" → 1, selain itu null. */
    private function unitNumber(string $unit): ?int
    {
        $text = strtoupper($unit);

        if (str_contains($text, 'COMMON')) {
            return 0;
        }

        return preg_match('/UNIT\s*#?\s*(\d+)/', $text, $m) ? (int) $m[1] : null;
    }

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