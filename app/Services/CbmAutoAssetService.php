<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Plta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CbmAutoAssetService
{
    /**
     * Tiap item punya 'action':
     *  - new    : buat asset baru (boleh membawa unit_in, system_in, kks_in)
     *  - alias  : tambahkan nama Excel sebagai alias alat terdekat
     *  - rename : ganti nama alat terdekat sesuai Excel (koreksi nama)
     *  - skip   : lewati
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array{created: array, linked: array, skipped: array}
     */
    public function create(array $items, string $parameter): array
    {
        $hasCbm = Schema::hasColumn('equipments', 'cbm_alias')
            && Schema::hasColumn('equipments', 'cbm_parameters');

        $created = [];
        $linked = [];
        $skipped = [];
        $counters = [];

        DB::transaction(function () use ($items, $parameter, $hasCbm, &$created, &$linked, &$skipped, &$counters) {
            foreach ($items as $it) {
                $action = $it['action'] ?? 'new';
                $name = trim((string) ($it['name'] ?? ''));
                $code = strtoupper(trim((string) ($it['plta'] ?? '')));

                if ($name === '' || $action === 'skip') {
                    continue;
                }

                // Pakai alat terdekat: tambahkan nama Excel sebagai alias
                if ($action === 'alias') {
                    $target = Equipment::query()
                        ->where('assetnum', (string) ($it['near']['assetnum'] ?? ''))
                        ->first();

                    if (! $target || ! $hasCbm) {
                        $skipped[] = ['equipment' => $name, 'reason' => 'Alat terdekat tidak ditemukan'];

                        continue;
                    }

                    $aliases = array_filter(array_map('trim', explode(',', (string) $target->cbm_alias)));
                    $aliases[] = $name;
                    $params = array_filter(explode(',', (string) $target->cbm_parameters));
                    $params[] = $parameter;

                    $target->forceFill([
                        'cbm_alias' => mb_substr(implode(', ', array_values(array_unique($aliases))), 0, 255),
                        'cbm_parameters' => implode(',', array_values(array_unique($params))),
                    ])->save();

                    $linked[] = [
                        'assetnum' => $target->assetnum,
                        'equipment' => $name,
                        'master' => (string) $target->equipment,
                    ];

                    continue;
                }

                // Ganti nama alat terdekat: nama di master dikoreksi sesuai file terbaru
                if ($action === 'rename') {
                    $target = Equipment::query()
                        ->where('assetnum', (string) ($it['near']['assetnum'] ?? ''))
                        ->first();

                    if (! $target) {
                        $skipped[] = ['equipment' => $name, 'reason' => 'Alat terdekat tidak ditemukan'];

                        continue;
                    }

                    $old = (string) $target->equipment;
                    $changes = ['equipment' => mb_substr($name, 0, 150)];

                    if ($hasCbm) {
                        // Nama lama dibuang dari alias supaya tidak ada nama salah yang tertinggal
                        $aliases = array_filter(
                            array_map('trim', explode(',', (string) $target->cbm_alias)),
                            fn ($a) => $a !== '' && strcasecmp($a, $old) !== 0
                        );
                        $aliases[] = $name;
                        $params = array_filter(explode(',', (string) $target->cbm_parameters));
                        $params[] = $parameter;

                        $changes['cbm_alias'] = mb_substr(implode(', ', array_values(array_unique($aliases))), 0, 255);
                        $changes['cbm_parameters'] = implode(',', array_values(array_unique($params)));
                    }

                    $target->forceFill($changes)->save();

                    $linked[] = [
                        'assetnum' => $target->assetnum,
                        'equipment' => $name,
                        'master' => $old.' (nama diganti)',
                    ];

                    continue;
                }

                // Buat asset baru
                $plta = $code !== ''
                    ? Plta::query()->where('kode_prefix', 'B'.$code)->first()
                    : null;

                if (! $plta) {
                    $skipped[] = ['equipment' => $name, 'reason' => 'Kode PLTA "'.$code.'" tidak ada di daftar PLTA'];

                    continue;
                }

                $unitIn = trim((string) ($it['unit_in'] ?? ''));
                $unitSrc = $unitIn !== '' ? $unitIn : $name;

                if ($unitIn !== '' && stripos($unitIn, 'COMMON') !== false) {
                    $unitNo = 0;
                } else {
                    $unitNo = preg_match('/UNIT\s*#?\s*(\d+)/i', $unitSrc, $m) ? (int) $m[1] : 0;
                }

                $unitText = $unitIn !== ''
                    ? $unitIn
                    : strtoupper($plta->nama_plta).($unitNo === 0 ? ' COMMON' : ' UNIT '.$unitNo);

                $prefix = strtoupper($plta->kode_prefix).sprintf('%02d', $unitNo);

                if (! isset($counters[$prefix])) {
                    $counters[$prefix] = $this->maxSequence($prefix);
                }

                do {
                    $counters[$prefix]++;
                    $assetnum = $prefix.sprintf('%04d', $counters[$prefix]);
                } while (Equipment::query()->where('assetnum', $assetnum)->exists());

                $systemIn = trim((string) ($it['system_in'] ?? ''));
                $kksIn = trim((string) ($it['kks_in'] ?? ''));

                $attributes = [
                    'plta_id' => $plta->id,
                    'unit' => mb_substr($unitText, 0, 50),
                    'system' => mb_substr($systemIn !== '' ? $systemIn : 'AUTO DARI UPLOAD CBM', 0, 255),
                    'equipment' => mb_substr($name, 0, 150),
                    'kks' => mb_substr($kksIn !== '' ? $kksIn : trim((string) ($it['noeq'] ?? '')), 0, 100),
                    'assetnum' => $assetnum,
                ];

                if ($hasCbm) {
                    $attributes['cbm_alias'] = mb_substr($name, 0, 255);
                    $attributes['cbm_parameters'] = $parameter;
                }

                Equipment::forceCreate($attributes);

                $created[] = ['assetnum' => $assetnum, 'equipment' => $name, 'plta' => $code];
            }
        });

        return ['created' => $created, 'linked' => $linked, 'skipped' => $skipped];
    }

    private function maxSequence(string $prefix): int
    {
        $length = strlen($prefix) + 4;

        return Equipment::query()
            ->where('assetnum', 'like', $prefix.'%')
            ->pluck('assetnum')
            ->map(fn ($a): string => strtoupper((string) $a))
            ->filter(fn (string $a): bool => strlen($a) === $length && ctype_digit(substr($a, strlen($prefix))))
            ->map(fn (string $a): int => (int) substr($a, strlen($prefix)))
            ->max() ?? 0;
    }
}