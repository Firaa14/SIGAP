<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class ExcelUploadService
{
    /**
     * Worktype yang diterima sistem.
     *
     * @var string[]
     */
    private const VALID_WORKTYPES = ['CM', 'EJ', 'EV', 'PAM'];

    /**
     * Status WO → ABNORMAL.
     *
     * @var string[]
     */
    private const ABNORMAL_STATUSES = ['APPR', 'INPRG', 'PTWCL', 'PTWR', 'WPTW'];

    /**
     * Status WO → NORMAL.
     *
     * @var string[]
     */
    private const NORMAL_STATUSES = ['CLOSE', 'COMP'];

    /**
     * Baca file Excel dan kembalikan raw rows (associative array per baris).
     * Kolom diambil dari header baris pertama (EXACT MATCH, ALL CAPS).
     *
     * @return array<int, array<string, string>>
     *
     * @throws Exception
     */
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        // Baris pertama = header
        $headers = array_map('trim', $rows[0]);

        $data = [];
        $rowCount = count($rows);

        for ($i = 1; $i < $rowCount; $i++) {
            $rowRaw = $rows[$i];

            // Lewati baris yang sepenuhnya kosong
            $rowValues = array_filter($rowRaw, fn ($v) => $v !== null && $v !== '');
            if (empty($rowValues)) {
                continue;
            }

            $mapped = [];
            foreach ($headers as $colIndex => $headerName) {
                $mapped[$headerName] = isset($rowRaw[$colIndex])
                    ? trim((string) $rowRaw[$colIndex])
                    : '';
            }

            $mapped['_row_number'] = (string) ($i + 1); // nomor baris di Excel (1-indexed, +1 karena header)
            $data[] = $mapped;
        }

        return $data;
    }

    /**
     * Validasi raw rows dari Excel.
     *
     * Mengembalikan:
     * - valid: baris siap commit (sudah resolved ke equipment_id + status_otomatis)
     * - errors: daftar error per baris
     *
     * @param  array<int, array<string, string>>  $rows
     * @return array{
     *   valid: array<int, array{equipment_id: int, assetnum: string, no_wo: string, description: string, worktype: string, wo_status: string, status_otomatis: string, plta_name: string, row: int}>,
     *   errors: array<int, array{row: int, assetnum: string, errors: string[]}>,
     *   summary: array{total: int, valid_count: int, error_count: int, pltas_found: string[]}
     * }
     */
    public function validate(array $rows): array
    {
        // Pre-load semua PLTA prefix → plta_name mapping
        $pltaPrefixMap = Plta::all()->keyBy('kode_prefix');

        $valid = [];
        $errors = [];
        $seenAssetNums = [];  // untuk deteksi duplikat dalam file
        $pltasFound = [];

        foreach ($rows as $row) {
            $rowNum = (int) ($row['_row_number'] ?? 0);
            $assetnum = strtoupper(trim($row['ASSETNUM'] ?? ''));
            $noWo = trim($row['NO WO'] ?? '');
            $desc = trim($row['DESCRIPTION'] ?? '');
            $worktype = strtoupper(trim($row['WORKTYPE'] ?? ''));
            $status = strtoupper(trim($row['STATUS'] ?? ''));

            $rowErrors = [];

            // --- Validasi field kosong ---
            if ($assetnum === '') {
                $rowErrors[] = 'ASSETNUM kosong.';
            }
            if ($noWo === '') {
                $rowErrors[] = 'NO WO kosong.';
            }
            if ($desc === '') {
                $rowErrors[] = 'DESCRIPTION kosong.';
            }
            if ($worktype === '') {
                $rowErrors[] = 'WORKTYPE kosong.';
            } elseif (! in_array($worktype, self::VALID_WORKTYPES, true)) {
                $rowErrors[] = "Worktype tidak valid: \"{$worktype}\". Worktype yang diizinkan: ".implode(', ', self::VALID_WORKTYPES).'.';
            }
            if ($status === '') {
                $rowErrors[] = 'STATUS kosong.';
            }

            // --- Deteksi duplikat ASSETNUM dalam file ---
            if ($assetnum !== '' && isset($seenAssetNums[$assetnum])) {
                $rowErrors[] = "Duplikat ASSETNUM \"{$assetnum}\" dalam file (pertama kali muncul di baris {$seenAssetNums[$assetnum]}).";
            } elseif ($assetnum !== '') {
                $seenAssetNums[$assetnum] = $rowNum;
            }

            // --- Hanya lanjutkan resolusi DB jika field wajib valid ---
            $equipment = null;
            $statusOto = null;
            $pltaName = '—';

            if ($assetnum !== '' && ! isset($seenAssetNums[$assetnum.'_dup'])) {
                // Resolusi prefix ASSETNUM
                $prefix = strtoupper(substr($assetnum, 0, 4));

                if (! $pltaPrefixMap->has($prefix)) {
                    $rowErrors[] = "Prefix ASSETNUM \"{$prefix}\" tidak dikenali dalam sistem.";
                } else {
                    /** @var Plta $plta */
                    $plta = $pltaPrefixMap->get($prefix);

                    // Cari equipment di DB
                    $equipment = Equipment::byAssetnum($assetnum)->first();

                    if (! $equipment) {
                        $rowErrors[] = "ASSETNUM \"{$assetnum}\" tidak ditemukan dalam data master equipment.";
                    } else {
                        $pltaName = $plta->nama_plta;
                        $pltasFound[$plta->nama_plta] = true;
                    }
                }
            }

            // --- Hitung status otomatis (hanya jika worktype & status valid) ---
            if (empty($rowErrors) || (count($rowErrors) === 0)) {
                // tidak ada error, lanjut
            }

            $unknownStatusCombo = false;
            if (
                $equipment
                && in_array($worktype, self::VALID_WORKTYPES, true)
                && $status !== ''
            ) {
                if (in_array($status, self::ABNORMAL_STATUSES, true)) {
                    $statusOto = 'abnormal';
                } elseif (in_array($status, self::NORMAL_STATUSES, true)) {
                    $statusOto = 'normal';
                } else {
                    $unknownStatusCombo = true;
                    $rowErrors[] = "Kombinasi Worktype \"{$worktype}\" + Status WO \"{$status}\" tidak dikenali. "
                        .'Status yang valid: '.implode(', ', array_merge(self::ABNORMAL_STATUSES, self::NORMAL_STATUSES)).'.';
                }
            }

            // --- Klasifikasi baris ---
            if (empty($rowErrors)) {
                $valid[] = [
                    'equipment_id' => $equipment->id,
                    'assetnum' => $assetnum,
                    'no_wo' => $noWo,
                    'description' => $desc,
                    'worktype' => $worktype,
                    'wo_status' => $status,
                    'status_otomatis' => $statusOto,
                    'plta_name' => $pltaName,
                    'row' => $rowNum,
                ];
            } else {
                $errors[] = [
                    'row' => $rowNum,
                    'assetnum' => $assetnum ?: '(kosong)',
                    'no_wo' => $noWo ?: '(kosong)',
                    'errors' => $rowErrors,
                ];
            }
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'summary' => [
                'total' => count($rows),
                'valid_count' => count($valid),
                'error_count' => count($errors),
                'pltas_found' => array_keys($pltasFound),
            ],
        ];
    }

    /**
     * Commit data yang sudah divalidasi ke database dalam satu transaksi.
     * Status manual (not_ready) tidak disentuh.
     *
     * @param  array<int, array{equipment_id: int, assetnum: string, no_wo: string, description: string, worktype: string, wo_status: string, status_otomatis: string}>  $validRows
     *
     * @throws \Throwable
     */
    public function commit(array $validRows): void
    {
        \DB::transaction(function () use ($validRows) {
            $uploadedAt = now();

            foreach ($validRows as $row) {
                EquipmentWo::updateOrCreate(
                    ['equipment_id' => $row['equipment_id']],
                    [
                        'no_wo' => $row['no_wo'],
                        'description' => $row['description'],
                        'worktype' => $row['worktype'],
                        'wo_status' => $row['wo_status'],
                        'status_otomatis' => $row['status_otomatis'],
                        // status_manual SENGAJA tidak disentuh → pakai updateOrCreate
                        // dengan cara: setelah updateOrCreate, hanya update kolom yg bukan status_manual
                        'uploaded_at' => $uploadedAt,
                    ]
                );
            }
        });
    }

    /**
     * Commit dengan perlindungan status_manual.
     * Menggunakan upsert manual agar status_manual tidak ditimpa.
     *
     * @param  array<int, array{equipment_id: int, no_wo: string, description: string, worktype: string, wo_status: string, status_otomatis: string}>  $validRows
     *
     * @throws \Throwable
     */
    public function commitProtected(array $validRows): void
    {
        \DB::transaction(function () use ($validRows) {
            $uploadedAt = now();

            foreach ($validRows as $row) {
                $wo = EquipmentWo::where('equipment_id', $row['equipment_id'])->first();

                if ($wo) {
                    // Update semua kecuali status_manual
                    $wo->update([
                        'no_wo' => $row['no_wo'],
                        'description' => $row['description'],
                        'worktype' => $row['worktype'],
                        'wo_status' => $row['wo_status'],
                        'status_otomatis' => $row['status_otomatis'],
                        'uploaded_at' => $uploadedAt,
                        // status_manual TIDAK DISENTUH
                    ]);
                } else {
                    // Insert baru, status_manual = null secara default
                    EquipmentWo::create([
                        'equipment_id' => $row['equipment_id'],
                        'no_wo' => $row['no_wo'],
                        'description' => $row['description'],
                        'worktype' => $row['worktype'],
                        'wo_status' => $row['wo_status'],
                        'status_otomatis' => $row['status_otomatis'],
                        'status_manual' => null,
                        'uploaded_at' => $uploadedAt,
                    ]);
                }
            }
        });
    }
}
