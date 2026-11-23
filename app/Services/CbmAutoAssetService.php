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
     *  - new    : buat asset baru. ASSETNUM WAJIB dari file (assetnum_file), tidak dibuat sistem.
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

        DB::transaction(function () use ($items, $parameter, $hasCbm, &$created, &$linked, &$skipped) {
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

                // ASSETNUM harus dari file. Sistem tidak membuat nomor sendiri.
                $assetnum = strtoupper(trim((string) ($it['assetnum_file'] ?? '')));

                if ($assetnum === '') {
                    $skipped[] = [
                        'equipment' => $name,
                        'reason' => 'ASSETNUM di file kosong. Lengkapi di Excel lalu upload ulang',
                    ];

                    continue;
                }

                $pref = strtoupper((string) $plta->kode_prefix);

                if (! str_starts_with($assetnum, $pref)) {
                    $skipped[] = [
                        'equipment' => $name,
                        'reason' => "ASSETNUM {$assetnum} tidak diawali {$pref}. Periksa kode PLTA atau ASSETNUM di file",
                    ];

                    continue;
                }

                if (Equipment::query()->where('assetnum', $assetnum)->exists()) {
                    $skipped[] = [
                        'equipment' => $name,
                        'reason' => "ASSETNUM {$assetnum} sudah terdaftar. Pilih Pakai alat terdekat atau Ganti nama",
                    ];

                    continue;
                }

                // Unit dibaca dari ASSETNUM (B + PLTA + 2 digit unit + urutan), kalau polanya cocok.
                $unitNo = 0;
                $unitDigits = substr($assetnum, strlen($pref), 2);
                if ($unitDigits !== '' && ctype_digit($unitDigits)) {
                    $unitNo = (int) $unitDigits;
                }

                $unitIn = trim((string) ($it['unit_in'] ?? ''));
                $unitText = $unitIn !== ''
                    ? $unitIn
                    : strtoupper($plta->nama_plta).($unitNo === 0 ? ' COMMON' : ' UNIT '.$unitNo);

                $systemIn = trim((string) ($it['system_in'] ?? ''));
                $kksIn = trim((string) ($it['kks_in'] ?? ''));

                $attributes = [
                    'plta_id' => $plta->id,
                    'unit' => mb_substr($unitText, 0, 50),
                    'system' => mb_substr($systemIn !== '' ? $systemIn : 'AUTO DARI UPLOAD CBM', 0, 255),
                    'equipment' => mb_substr($name, 0, 150),
                    'kks' => mb_substr(
                        $kksIn !== '' ? $kksIn : trim((string) ($it['kks_file'] ?? $it['noeq'] ?? '')),
                        0,
                        100
                    ),
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
}