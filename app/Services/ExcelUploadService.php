<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentWo;
use App\Models\Plta;
use App\Models\UploadHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;

class ExcelUploadService
{
    /**
     * Worktype yang diterima sistem.
     *
     * @var string[]
     */
    private const VALID_WORKTYPES = ['CM', 'EJ', 'EV', 'PAM'];

    /**
     * Kolom wajib yang harus ada di Excel.
     *
     * @var string[]
     */
    private const REQUIRED_COLUMNS = ['NO WO', 'DESCRIPTION', 'WORKTYPE', 'STATUS', 'ASSETNUM'];

    /**
     * Kolom opsional yang akan dibaca jika ada.
     *
     * @var string[]
     */
    private const OPTIONAL_COLUMNS = ['NAMA ASSET', 'REPORTDATE', 'SITEID', 'LOCATION'];

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
     * Mencoba membaca sheet "DATA" terlebih dahulu; jika tidak ada, gunakan active sheet.
     * Kolom diambil dari header baris pertama.
     *
     * @return array{rows: array<int, array<string, string>>, sheet_name: string, missing_required: string[]}
     *
     * @throws SpreadsheetException
     */
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);

        // Coba ambil sheet "DATA" terlebih dahulu
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
            return ['rows' => [], 'sheet_name' => $usedSheetName, 'missing_required' => self::REQUIRED_COLUMNS];
        }

        // Baris pertama = header
        $headers = array_map('trim', $rows[0]);

        // Normalkan header ke uppercase untuk case-insensitive matching
        $headerMap = [];
        foreach ($headers as $colIndex => $headerName) {
            $headerMap[strtoupper($headerName)] = $colIndex;
        }

        // Cek kolom wajib
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

            // Lewati baris yang sepenuhnya kosong
            $rowValues = array_filter($rowRaw, fn ($v) => $v !== null && $v !== '');
            if (empty($rowValues)) {
                continue;
            }

            $mapped = [];

            // Baca kolom wajib
            foreach (self::REQUIRED_COLUMNS as $col) {
                $idx = $headerMap[$col] ?? null;
                $mapped[$col] = $idx !== null && isset($rowRaw[$idx])
                    ? trim((string) $rowRaw[$idx])
                    : '';
            }

            // Baca kolom opsional jika ada
            foreach (self::OPTIONAL_COLUMNS as $col) {
                $idx = $headerMap[$col] ?? null;
                if ($idx !== null) {
                    $mapped[$col] = isset($rowRaw[$idx]) ? trim((string) $rowRaw[$idx]) : '';
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

    /**
     * Validasi raw rows dari Excel.
     *
     * Mengembalikan:
     * - valid: baris siap commit (sudah resolved ke equipment_id + status_otomatis + is_new flag)
     * - errors: daftar error per baris
     * - summary: statistik keseluruhan
     *
     * @param  array<int, array<string, string>>  $rows
     * @return array{
     *   valid: array<int, array{
     *     equipment_id: int, assetnum: string, no_wo: string, description: string,
     *     worktype: string, wo_status: string, status_otomatis: string,
     *     plta_name: string, nama_asset: string, report_date: string,
     *     row: int, is_new: bool
     *   }>,
     *   errors: array<int, array{row: int, assetnum: string, no_wo: string, errors: string[]}>,
     *   summary: array{total: int, valid_count: int, error_count: int, new_count: int, update_count: int, pltas_found: string[]}
     * }
     */
    public function validate(array $rows): array
    {
        // Pre-load semua PLTA prefix → plta mapping
        $pltaPrefixMap = Plta::all()->keyBy('kode_prefix');

        // Pre-load semua equipment_id yang sudah punya WO (untuk flag is_new)
        $existingWoEquipmentIds = EquipmentWo::pluck('equipment_id')->flip()->all();

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

            // --- Resolusi DB: Equipment & PLTA ---
            $equipment = null;
            $statusOto = null;
            $pltaName = '—';

            if ($assetnum !== '' && ! isset($seenAssetNums[$assetnum.'_dup'])) {
                $prefix = strtoupper(substr($assetnum, 0, 4));

                if (! $pltaPrefixMap->has($prefix)) {
                    $rowErrors[] = "Prefix ASSETNUM \"{$prefix}\" tidak dikenali dalam sistem.";
                } else {
                    /** @var Plta $plta */
                    $plta = $pltaPrefixMap->get($prefix);
                    $equipment = Equipment::byAssetnum($assetnum)->first();

                    if (! $equipment) {
                        $rowErrors[] = "ASSETNUM \"{$assetnum}\" tidak ditemukan dalam data master equipment.";
                    } else {
                        $pltaName = $plta->nama_plta;
                        $pltasFound[$plta->nama_plta] = true;

                        // Isi nama_asset dari DB jika tidak ada di Excel
                        if ($namaAsset === '') {
                            $namaAsset = $equipment->equipment;
                        }
                    }
                }
            }

            // --- Hitung status otomatis ---
            if ($equipment && in_array($worktype, self::VALID_WORKTYPES, true) && $status !== '') {
                if (in_array($status, self::ABNORMAL_STATUSES, true)) {
                    $statusOto = 'abnormal';
                } elseif (in_array($status, self::NORMAL_STATUSES, true)) {
                    $statusOto = 'normal';
                } else {
                    $rowErrors[] = "Kombinasi Worktype \"{$worktype}\" + Status WO \"{$status}\" tidak dikenali. "
                        .'Status yang valid: '.implode(', ', array_merge(self::ABNORMAL_STATUSES, self::NORMAL_STATUSES)).'.';
                }
            }

            // --- Klasifikasi baris ---
            if (empty($rowErrors) && $equipment !== null && $statusOto !== null) {
                $isNew = ! isset($existingWoEquipmentIds[$equipment->id]);

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

    /**
     * Commit data yang sudah divalidasi ke database dalam satu transaksi.
     * Status manual (not_ready) tidak disentuh.
     * Menyimpan riwayat upload ke upload_histories.
     *
     * @param  array<int, array{equipment_id: int, assetnum: string, no_wo: string, description: string, worktype: string, wo_status: string, status_otomatis: string, plta_name: string, is_new: bool}>  $validRows
     * @param  string  $filename  Nama file yang diupload
     * @param  int  $totalRows  Total baris di file (termasuk yang error)
     * @param  int  $errorRows  Jumlah baris yang gagal validasi
     * @return array{history_id: int, imported: int, new_rows: int, updated_rows: int, plta_distribution: array<string, int>}
     *
     * @throws \Throwable
     */
    public function commitProtected(array $validRows, string $filename, int $totalRows, int $errorRows): array
    {
        $result = DB::transaction(function () use ($validRows, $filename, $totalRows, $errorRows): array {
            $uploadedAt = now();
            $newCount = 0;
            $updatedCount = 0;
            $pltaDistribution = [];

            foreach ($validRows as $row) {
                $existingWo = EquipmentWo::where('equipment_id', $row['equipment_id'])->first();

                if ($existingWo) {
                    // Update — jangan sentuh status_manual
                    $existingWo->update([
                        'no_wo' => $row['no_wo'],
                        'description' => $row['description'],
                        'worktype' => $row['worktype'],
                        'wo_status' => $row['wo_status'],
                        'status_otomatis' => $row['status_otomatis'],
                        'uploaded_at' => $uploadedAt,
                    ]);
                    $updatedCount++;
                } else {
                    // Insert baru
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
                    $newCount++;
                }

                // Hitung distribusi PLTA
                $pltaName = $row['plta_name'];
                $pltaDistribution[$pltaName] = ($pltaDistribution[$pltaName] ?? 0) + 1;
            }

            $imported = $newCount + $updatedCount;

            // Simpan riwayat upload
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

        return $result;
    }
}
