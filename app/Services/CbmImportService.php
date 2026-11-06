<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CbmImportService
{
    /** Nama header yang dikenali (huruf besar, spasi dirapikan). */
    private const ASSET_HEADERS = ['ASSETNUM', 'ASSET NUM', 'ASSET', 'NO ASSET', 'KODE ASSET'];
    private const STATUS_HEADERS = ['STATUS', 'STATUS TERAKHIR'];

    public function __construct(private CbmEquipmentService $cbm)
    {
    }

    /**
     * Baca file Excel/CSV. Semua sheet yang punya header ASSETNUM/Asset dan STATUS/Status Terakhir
     * dibaca. Jika satu ASSETNUM muncul di banyak baris/sheet, status terparah yang dipakai
     * (urutan keparahan = urutan kode di legenda parameter, dari ringan ke berat).
     * Tanggal uji = tanggal upload.
     *
     * @return array{valid: array<int, array<string, mixed>>, errors: array<int, array<string, mixed>>}
     */
    public function parse(string $path, string $parameter): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '-1');

        $allowed = array_keys($this->cbm->statusLegend()[$parameter] ?? []);

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $best = [];      // assetnum => ['rank' => int, 'status' => string, 'where' => string]
        $invalid = [];   // assetnum|status => ['assetnum' => , 'where' => , 'status' => ]
        $headerFound = false;

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $rows = $sheet->toArray(null, false, false, false);

            [$headerIdx, $colAsset, $colStatus] = $this->findHeader($rows);
            if ($headerIdx === null) {
                continue;
            }
            $headerFound = true;

            foreach (array_slice($rows, $headerIdx + 1, null, true) as $i => $row) {
                $assetnum = strtoupper(trim((string) ($row[$colAsset] ?? '')));
                $status = strtoupper(trim((string) ($row[$colStatus] ?? '')));

                // Baris tanpa asset atau tanpa status = tidak ada data uji, dilewati.
                if ($assetnum === '' || $status === '') {
                    continue;
                }

                $where = $sheet->getTitle().' baris '.($i + 1);
                $rank = array_search($status, $allowed, true);

                if ($rank === false) {
                    $invalid[$assetnum.'|'.$status] ??= [
                        'assetnum' => $assetnum,
                        'where' => $where,
                        'status' => $status,
                    ];

                    continue;
                }

                if (! isset($best[$assetnum]) || $rank > $best[$assetnum]['rank']) {
                    $best[$assetnum] = ['rank' => $rank, 'status' => $status, 'where' => $where];
                }
            }
        }

        if (! $headerFound) {
            return [
                'valid' => [],
                'errors' => [[
                    'row' => '-',
                    'assetnum' => '-',
                    'reason' => 'Header tidak ditemukan. Dibutuhkan kolom ASSETNUM (atau Asset) dan STATUS (atau Status Terakhir).',
                ]],
            ];
        }

        $known = Equipment::query()->pluck('assetnum')->map(fn ($a) => strtoupper($a))->flip();
        $date = now()->toDateString();

        $valid = [];
        $errors = [];

        foreach ($best as $assetnum => $info) {
            if (! isset($known[$assetnum])) {
                $errors[] = [
                    'row' => $info['where'],
                    'assetnum' => $assetnum,
                    'reason' => 'ASSETNUM tidak terdaftar di master equipment',
                ];

                continue;
            }

            $valid[] = [
                'row' => $info['where'],
                'assetnum' => $assetnum,
                'status_code' => $info['status'],
                'measured_at' => $date,
                'keterangan' => null,
            ];
        }

        foreach ($invalid as $item) {
            // Asset yang sudah punya status valid dari baris lain tidak perlu dilaporkan.
            if (isset($best[$item['assetnum']])) {
                continue;
            }

            $errors[] = [
                'row' => $item['where'],
                'assetnum' => $item['assetnum'],
                'reason' => 'STATUS "'.$item['status'].'" tidak valid (boleh: '.implode('/', $allowed).')',
            ];
        }

        if ($valid === [] && $errors === []) {
            $errors[] = [
                'row' => '-',
                'assetnum' => '-',
                'reason' => 'Tidak ada baris yang memiliki ASSETNUM dan STATUS terisi.',
            ];
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * Cari baris header di 20 baris pertama sebuah sheet.
     *
     * @return array{0: ?int, 1: int|false, 2: int|false}
     */
    private function findHeader(array $rows): array
    {
        foreach (array_slice($rows, 0, 20, true) as $i => $row) {
            $header = array_map(
                fn ($h) => strtoupper(trim(preg_replace('/\s+/', ' ', (string) $h))),
                $row
            );

            $a = false;
            $s = false;

            foreach ($header as $idx => $name) {
                if ($a === false && in_array($name, self::ASSET_HEADERS, true)) {
                    $a = $idx;
                }
                if ($s === false && in_array($name, self::STATUS_HEADERS, true)) {
                    $s = $idx;
                }
            }

            if ($a !== false && $s !== false) {
                return [$i, $a, $s];
            }
        }

        return [null, false, false];
    }

    /**
     * Simpan baris valid. Baris dengan assetnum+parameter+tanggal yang sama diperbarui.
     */
    public function store(string $parameter, array $rows, ?int $userId): int
    {
        $now = now();

        $payload = array_map(fn (array $r) => [
            'assetnum' => $r['assetnum'],
            'parameter' => $parameter,
            'status_code' => $r['status_code'],
            'measured_at' => $r['measured_at'],
            'keterangan' => $r['keterangan'] ?? null,
            'uploaded_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows);

        DB::transaction(function () use ($payload) {
            foreach (array_chunk($payload, 500) as $chunk) {
                DB::table('cbm_measurements')->upsert(
                    $chunk,
                    ['assetnum', 'parameter', 'measured_at'],
                    ['status_code', 'keterangan', 'uploaded_by', 'updated_at']
                );
            }
        });

        return count($payload);
    }

    /**
     * Buat template Excel untuk satu parameter, berisi daftar equipment.
     */
    public function template(string $parameter, ?int $pltaId = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');
        $sheet->fromArray(['ASSETNUM', 'STATUS'], null, 'A1');

        $query = Equipment::query()->orderBy('assetnum');
        if ($pltaId) {
            $query->where('plta_id', $pltaId);
        }

        $r = 2;
        foreach ($query->get(['assetnum']) as $eq) {
            $sheet->setCellValue("A{$r}", $eq->assetnum);
            $r++;
        }

        $ref = $spreadsheet->createSheet();
        $ref->setTitle('Kode Status');
        $ref->fromArray(['KODE', 'ARTI'], null, 'A1');
        $r = 2;
        foreach ($this->cbm->statusLegend()[$parameter] as $code => $meta) {
            $ref->setCellValue("A{$r}", $code);
            $ref->setCellValue("B{$r}", $meta['label']);
            $r++;
        }

        foreach (['A', 'B'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function writeTemplate(Spreadsheet $spreadsheet): void
    {
        (new Xlsx($spreadsheet))->save('php://output');
    }
}