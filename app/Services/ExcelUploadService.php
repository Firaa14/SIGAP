<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use App\Models\UploadHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ExcelUploadService
{
    private const VALID_WORKTYPES = ['CM', 'EJ', 'EV', 'PAM', 'CP'];

    private const REQUIRED_COLUMNS = [
        'NO WO',
        'DESCRIPTION',
        'WORKTYPE',
        'STATUS',
        'ASSETNUM',
    ];

    private const OPTIONAL_COLUMNS = [
        'NAMA ASSET',
        'REPORTDATE',
        'SITEID',
        'LOCATION',
    ];

    private const ABNORMAL_STATUSES = [
        'APPR',
        'INPRG',
        'PTWCL',
        'PTWR',
        'WPTW',
        'WJOBCARD',
    ];

    private const NORMAL_STATUSES = [
        'CLOSE',
        'COMP',
    ];

    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);

        $sheetNames = $spreadsheet->getSheetNames();
        $dataSheetName = null;

        foreach ($sheetNames as $name) {
            if (strtoupper(trim($name)) === 'DATA') {
                $dataSheetName = $name;
                break;
            }
        }

        $sheet = $dataSheetName !== null
            ? $spreadsheet->getSheetByName($dataSheetName)
            : $spreadsheet->getActiveSheet();

        $usedSheetName = $sheet->getTitle();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [
                'rows' => [],
                'sheet_name' => $usedSheetName,
                'missing_required' => self::REQUIRED_COLUMNS,
            ];
        }

        $headers = array_map('trim', $rows[0]);

        $headerMap = [];

        foreach ($headers as $colIndex => $headerName) {
            $headerMap[strtoupper($headerName)] = $colIndex;
        }

        $missingRequired = [];

        foreach (self::REQUIRED_COLUMNS as $required) {
            if (! isset($headerMap[$required])) {
                $missingRequired[] = $required;
            }
        }

        $data = [];
        $rowCount = count($rows);

        for ($i = 1; $i < $rowCount; $i++) {
            $rowRaw = $rows[$i];

            $rowValues = array_filter(
                $rowRaw,
                fn ($v) => $v !== null && $v !== ''
            );

            if (empty($rowValues)) {
                continue;
            }

            $mapped = [];

            foreach (self::REQUIRED_COLUMNS as $col) {
                $idx = $headerMap[$col] ?? null;

                $mapped[$col] = $idx !== null && isset($rowRaw[$idx])
                    ? trim((string) $rowRaw[$idx])
                    : '';
            }

            foreach (self::OPTIONAL_COLUMNS as $col) {
                $idx = $headerMap[$col] ?? null;

                if ($idx === null) {
                    continue;
                }

                if ($col === 'REPORTDATE') {
                    $mapped[$col] = $this->extractReportDate($sheet, $idx, $i + 1);
                } else {
                    $mapped[$col] = isset($rowRaw[$idx])
                        ? trim((string) $rowRaw[$idx])
                        : '';
                }
            }

            $mapped['_row_number'] = (string) ($i + 1);

            $data[] = $mapped;
        }

        return [
            'rows' => $data,
            'sheet_name' => $usedSheetName,
            'missing_required' => $missingRequired,
        ];
    }

    private function extractReportDate($sheet, int $colIndex, int $sheetRow): string
    {
        try {
            $cell = $sheet->getCell([$colIndex + 1, $sheetRow]);
            $rawValue = $cell->getValue();
        } catch (\Throwable $e) {
            Log::warning('[IMPORT] Gagal membaca cell REPORTDATE', [
                'row' => $sheetRow,
                'col' => $colIndex + 1,
                'error' => $e->getMessage(),
            ]);

            return '';
        }

        if ($rawValue === null || $rawValue === '') {
            return '';
        }

        if (is_numeric($rawValue) && ExcelDate::isDateTime($cell)) {
            try {
                return ExcelDate::excelToDateTimeObject($rawValue)->format('Y-m-d');
            } catch (\Throwable $e) {
                Log::warning('[IMPORT] Gagal konversi Excel date serial', [
                    'row' => $sheetRow,
                    'raw_value' => $rawValue,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $rawString = trim((string) $rawValue);

        $explicitFormats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];

        foreach ($explicitFormats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $rawString);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                //
            }
        }

        try {
            return Carbon::parse($rawString)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning('[IMPORT] Gagal parse REPORTDATE sebagai teks', [
                'row' => $sheetRow,
                'raw_value' => $rawString,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    public function validate(array $rows): array
    {
        Log::info('[IMPORT] VALIDATION START', [
            'total_rows' => count($rows),
        ]);

        $pltaPrefixMap = Plta::all()->keyBy('kode_prefix');

        // FIX: tambahkan description ke key, supaya WO dengan no_wo + worktype sama
        // tapi description beda dianggap record yang BERBEDA (bukan saling menimpa).
        $existingWoKeys = EquipmentWo::get()
            ->map(fn (EquipmentWo $wo) => $wo->equipment_id.'|'.$wo->no_wo.'|'.$wo->worktype.'|'.$wo->description)
            ->flip()
            ->all();

        $rawAssetNums = [];

        foreach ($rows as $row) {
            $a = strtoupper(trim($row['ASSETNUM'] ?? ''));

            if ($a !== '') {
                $rawAssetNums[] = $a;
            }
        }

        $uniqueAssetNums = array_unique($rawAssetNums);

        $equipmentMap = Equipment::whereIn('assetnum', $uniqueAssetNums)
            ->get()
            ->keyBy(fn (Equipment $e) => strtoupper($e->assetnum))
            ->all();

        Log::info('[IMPORT] EQUIPMENT MAP LOADED', [
            'unique_assetnums_requested' => count($uniqueAssetNums),
            'equipments_found' => count($equipmentMap),
        ]);

        $valid = [];
        $errors = [];
        $seenAssetNums = [];
        $pltasFound = [];
        $newCount = 0;
        $updateCount = 0;

        foreach ($rows as $row) {
            $rowNum = (int) ($row['_row_number'] ?? 0);

            $assetnum = strtoupper(trim($row['ASSETNUM'] ?? ''));
            $noWo = trim($row['NO WO'] ?? '');
            $desc = trim($row['DESCRIPTION'] ?? '');
            $worktype = strtoupper(trim($row['WORKTYPE'] ?? ''));
            $status = strtoupper(trim($row['STATUS'] ?? ''));
            $namaAsset = trim($row['NAMA ASSET'] ?? '');
            $reportDate = trim($row['REPORTDATE'] ?? '');

            $rowErrors = [];

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
                $rowErrors[] = "Worktype tidak valid: \"{$worktype}\". Worktype yang diizinkan: "
                    .implode(', ', self::VALID_WORKTYPES).'.';
            }

            if ($status === '') {
                $rowErrors[] = 'STATUS kosong.';
            }

            if ($assetnum !== '' && ! isset($seenAssetNums[$assetnum])) {
                $seenAssetNums[$assetnum] = $rowNum;
            }

            $equipment = null;
            $statusOto = null;
            $pltaName = '—';

            if ($assetnum !== '' && ! isset($seenAssetNums[$assetnum.'_dup'])) {
                $prefix = strtoupper(substr($assetnum, 0, 4));

                if (! $pltaPrefixMap->has($prefix)) {
                    $rowErrors[] = "Prefix ASSETNUM \"{$prefix}\" tidak dikenali dalam sistem.";
                } else {
                    $plta = $pltaPrefixMap->get($prefix);

                    $equipment = $equipmentMap[$assetnum] ?? null;

                    if (! $equipment) {
                        $rowErrors[] = "ASSETNUM \"{$assetnum}\" tidak ditemukan dalam data master equipment.";
                    } else {
                        $pltaName = $plta->nama_plta;
                        $pltasFound[$plta->nama_plta] = true;

                        if ($namaAsset === '') {
                            $namaAsset = $equipment->equipment;
                        }
                    }
                }
            }

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
                    $rowErrors[] = "Kombinasi Worktype \"{$worktype}\" + Status WO \"{$status}\" tidak dikenali. "
                        .'Status yang valid: '
                        .implode(', ', array_merge(
                            self::ABNORMAL_STATUSES,
                            self::NORMAL_STATUSES
                        )).'.';
                }
            }

            if (
                empty($rowErrors)
                && $equipment !== null
                && $statusOto !== null
            ) {
                // FIX: description ikut menentukan apakah row ini "sudah ada" atau "baru"
                $rowKey = $equipment->id.'|'.$noWo.'|'.$worktype.'|'.$desc;
                $isNew = ! isset($existingWoKeys[$rowKey]);

                if ($isNew) {
                    $newCount++;
                } else {
                    $updateCount++;
                }

                $valid[] = [
                    'equipment_id' => $equipment->id,
                    'assetnum' => $assetnum,
                    'no_wo' => $noWo,
                    'description' => $desc,
                    'worktype' => $worktype,
                    'wo_status' => $status,
                    'status_otomatis' => $statusOto,
                    'plta_name' => $pltaName,
                    'nama_asset' => $namaAsset,
                    'report_date' => $reportDate,
                    'row' => $rowNum,
                    'is_new' => $isNew,
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

        Log::info('[IMPORT] VALIDATION COMPLETE', [
            'valid' => count($valid),
            'errors' => count($errors),
            'new' => $newCount,
            'update' => $updateCount,
        ]);

        return [
            'valid' => $valid,
            'errors' => $errors,
            'summary' => [
                'total' => count($rows),
                'valid_count' => count($valid),
                'error_count' => count($errors),
                'new_count' => $newCount,
                'update_count' => $updateCount,
                'pltas_found' => array_keys($pltasFound),
            ],
        ];
    }

    public function commitProtected(
        array $validRows,
        string $filename,
        int $totalRows,
        int $errorRows
    ): array {
        Log::info('[IMPORT] COMMIT START', [
            'filename' => $filename,
            'valid_rows' => count($validRows),
        ]);

        $result = DB::transaction(function () use ($validRows, $filename, $totalRows, $errorRows): array {
            $uploadedAt = now();

            $pltaDistribution = [];

            $equipmentIds = array_column($validRows, 'equipment_id');

            // FIX: description ikut jadi bagian key, supaya WO sama tapi description beda
            // tidak saling menimpa saat dicocokkan dengan data yang sudah ada di DB.
            $existingWoMap = EquipmentWo::whereIn('equipment_id', $equipmentIds)
                ->get()
                ->keyBy(fn (EquipmentWo $wo) => $wo->equipment_id.'|'.$wo->no_wo.'|'.$wo->worktype.'|'.$wo->description)
                ->all();

            $toInsert = [];
            $toUpdate = [];

            foreach ($validRows as $row) {
                $pltaName = $row['plta_name'];

                $pltaDistribution[$pltaName] =
                    ($pltaDistribution[$pltaName] ?? 0) + 1;

                $reportDate = null;
                $durasiHari = null;

                if (! empty($row['report_date'])) {
                    try {
                        $reportDate = Carbon::parse(
                            $row['report_date']
                        )->startOfDay();

                        $durasiHari = $reportDate->diffInDays(
                            $uploadedAt->copy()->startOfDay()
                        );
                    } catch (\Throwable $e) {
                        Log::warning('[IMPORT] Gagal hitung durasi_hari', [
                            'equipment_id' => $row['equipment_id'],
                            'report_date_raw' => $row['report_date'],
                            'error' => $e->getMessage(),
                        ]);

                        $reportDate = null;
                        $durasiHari = null;
                    }
                }

                $data = [
                    'no_wo' => $row['no_wo'],
                    'description' => $row['description'],
                    'worktype' => $row['worktype'],
                    'wo_status' => $row['wo_status'],
                    'status_otomatis' => $row['status_otomatis'],
                    'status_manual' => null,  // reset override manual SO/CBM saat ada upload terbaru
                    'report_date' => $reportDate,
                    'durasi_hari' => $durasiHari,
                    'uploaded_at' => $uploadedAt,
                ];

                // FIX: description ikut dalam key pencocokan insert vs update
                $rowKey = $row['equipment_id'].'|'.$row['no_wo'].'|'.$row['worktype'].'|'.$row['description'];

                if (isset($existingWoMap[$rowKey])) {
                    $toUpdate[] = [
                        $existingWoMap[$rowKey],
                        $data,
                    ];
                } else {
                    $toInsert[] = array_merge($data, [
                        'equipment_id' => $row['equipment_id'],
                        'status_manual' => null,
                        'created_at' => $uploadedAt,
                        'updated_at' => $uploadedAt,
                    ]);
                }
            }

            if (! empty($toInsert)) {
                DB::table('equipment_wo')->insert($toInsert);
            }

            foreach ($toUpdate as [$existingWo, $data]) {
                $existingWo->update($data);
            }

            $newCount = count($toInsert);
            $updatedCount = count($toUpdate);
            $imported = $newCount + $updatedCount;

            $history = UploadHistory::create([
                'user_id' => Auth::id(),
                'filename' => $filename,
                'total_rows' => $totalRows,
                'valid_rows' => count($validRows),
                'imported_rows' => $imported,
                'new_rows' => $newCount,
                'updated_rows' => $updatedCount,
                'skipped_rows' => 0,
                'error_rows' => $errorRows,
                'status' => 'done',
                'plta_distribution' => $pltaDistribution,
                'uploaded_at' => $uploadedAt,
            ]);

            return [
                'history_id' => $history->id,
                'imported' => $imported,
                'new_rows' => $newCount,
                'updated_rows' => $updatedCount,
                'plta_distribution' => $pltaDistribution,
            ];
        });

        Log::info('[IMPORT] COMMIT COMPLETE', [
            'history_id' => $result['history_id'],
        ]);

        return $result;
    }
}
