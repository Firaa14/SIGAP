<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CbmImportService
{
    /** Format lama (template aplikasi): kolom ASSETNUM + STATUS. Tetap didukung. */
    private const ASSET_HEADERS = ['ASSETNUM', 'ASSET NUM', 'ASSET', 'NO ASSET', 'KODE ASSET'];
    private const STATUS_HEADERS = ['STATUS', 'STATUS TERAKHIR'];

    /** Kata yang diabaikan saat mencocokkan nama equipment. */
    private const STOPWORDS = ['UNIT', 'NO', 'THE', 'DAN'];

    /** Penyeragaman istilah antara file Excel dan master equipment. */
    private const SYNONYMS = [
        'TURBIN' => 'TURBINE',
        'GEN' => 'GENERATOR',
        'TRAFO' => 'TRANSFORMER',
        'TRF' => 'TRANSFORMER',
        'GOV' => 'GOVERNOR',
        'BRG' => 'BEARING',
        'VIBRATION' => 'VIB',
        'VIBRASI' => 'VIB',
    ];

    /** Kata yang bobotnya kecil saat mencocokkan (sering tidak ada di master). */
    private const LOW_WEIGHT = ['VIB', 'X', 'Y', 'Z'];

    /** Skor minimum agar sebuah equipment dianggap cocok. */
    private const MATCH_THRESHOLD = 0.6;

    public function __construct(private CbmEquipmentService $cbm)
    {
    }

    /**
     * Baca file Excel/CSV.
     *
     * Dua format didukung:
     *  1. Format lama  : header ASSETNUM + STATUS (ASSETNUM dipakai langsung).
     *  2. Format resume: header PLTA + EQUIPMENT + (No. Equip.) + Status Terakhir / Status Akhir /
     *     Resume / Status. ASSETNUM dicari otomatis di master equipment dari kode PLTA,
     *     nama equipment, dan No. Equip.
     *
     * Jika satu ASSETNUM muncul di banyak baris, status terparah yang dipakai
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

        $sheets = $spreadsheet->getAllSheets();

        // Jika ada sheet berjudul "Resume ...", hanya sheet itu yang dibaca pada format resume
        // (sheet lain biasanya riwayat/detail per PLTA yang bisa menimpa hasil terbaru).
        $hasResumeSheet = false;
        foreach ($sheets as $sheet) {
            if (str_contains(strtoupper($sheet->getTitle()), 'RESUME')) {
                $hasResumeSheet = true;
                break;
            }
        }

        $entries = [];
        $headerFound = false;

        foreach ($sheets as $sheet) {
            $rows = $sheet->toArray(null, false, false, false);
            $title = $sheet->getTitle();

            // 1. Format lama (ASSETNUM + STATUS)
            [$legacyIdx, $colAsset, $colStatus] = $this->findLegacyHeader($rows);
            if ($legacyIdx !== null) {
                $headerFound = true;

                foreach (array_slice($rows, $legacyIdx + 1, null, true) as $i => $row) {
                    $assetnum = strtoupper(trim((string) ($row[$colAsset] ?? '')));
                    $status = strtoupper(trim((string) ($row[$colStatus] ?? '')));

                    if ($assetnum === '' || $status === '') {
                        continue;
                    }

                    $entries[] = [
                        'where' => $title.' baris '.($i + 1),
                        'assetnum' => $assetnum,
                        'plta' => '',
                        'name' => '',
                        'noeq' => '',
                        'status' => $status,
                    ];
                }

                continue;
            }

            // 2. Format resume (PLTA + EQUIPMENT + Status Terakhir)
            if ($hasResumeSheet && ! str_contains(strtoupper($title), 'RESUME')) {
                continue;
            }

            $header = $this->findResumeHeader($rows);
            if ($header === null) {
                continue;
            }

            $headerFound = true;
            $this->collectResumeRows($rows, $header, $title, $parameter, $entries);
        }

        if (! $headerFound) {
            return [
                'valid' => [],
                'errors' => [[
                    'row' => '-',
                    'plta' => '-',
                    'equipment' => '-',
                    'assetnum' => '-',
                    'reason' => 'Header tidak ditemukan. Dibutuhkan kolom PLTA, EQUIPMENT, dan Status Terakhir '
                        .'(atau Status Akhir / Resume), atau kolom ASSETNUM dan STATUS.',
                ]],
            ];
        }

        [$index, $known] = $this->equipmentIndex();
        $date = now()->toDateString();

        $best = [];       // assetnum => ['rank','status','where','plta','name']
        $errors = [];
        $seenErrors = [];
        $matchCache = [];

        $pushError = function (string $key, array $error) use (&$errors, &$seenErrors): void {
            if (isset($seenErrors[$key])) {
                return;
            }
            $seenErrors[$key] = true;
            $errors[] = $error;
        };

        foreach ($entries as $e) {
            $label = '';
            $note = null;
            $rank = array_search($e['status'], $allowed, true);

            if ($rank === false) {
                $pushError('status|'.$e['plta'].'|'.$e['name'].'|'.$e['assetnum'].'|'.$e['status'], [
                    'row' => $e['where'],
                    'plta' => $e['plta'] !== '' ? $e['plta'] : '—',
                    'equipment' => $e['name'] !== '' ? $e['name'] : '—',
                    'assetnum' => $e['assetnum'] ?? '—',
                    'reason' => 'Status "'.$e['status'].'" tidak valid (boleh: '.implode('/', $allowed).')',
                ]);

                continue;
            }

            if ($e['assetnum'] !== null) {
                // Format lama: ASSETNUM langsung dari file.
                $assetnum = $e['assetnum'];

                if (! isset($known[$assetnum])) {
                    $pushError('unknown|'.$assetnum, [
                        'row' => $e['where'],
                        'plta' => '—',
                        'equipment' => '—',
                        'assetnum' => $assetnum,
                        'reason' => 'ASSETNUM tidak terdaftar di master equipment',
                    ]);

                    continue;
                }
            } else {
                // Format resume: cari ASSETNUM di master.
                $key = $e['plta'].'|'.$e['name'].'|'.$e['noeq'];

                if (! array_key_exists($key, $matchCache)) {
                    $matchCache[$key] = $this->matchEquipment($index, $e['plta'], $e['name'], $e['noeq']);
                }

                $match = $matchCache[$key];

                if ($match['error'] !== null) {
                    $pushError('match|'.$key, [
                        'row' => $e['where'],
                        'plta' => $e['plta'],
                        'equipment' => $e['name'].($e['noeq'] !== '' ? ' ('.$e['noeq'].')' : ''),
                        'assetnum' => $match['hint'] ?? '—',
                        'reason' => $match['error'],
                    ]);

                    continue;
                }

                $assetnum = $match['assetnum'];
                $label = (string) $match['label'];
                $note = $match['note'];
            }

            if (! isset($best[$assetnum]) || $rank > $best[$assetnum]['rank']) {
                $best[$assetnum] = [
                    'rank' => $rank,
                    'status' => $e['status'],
                    'where' => $e['where'],
                    'plta' => $e['plta'],
                    'name' => $e['name'],
                    'master' => $label,
                    'note' => $note,
                ];
            }
        }

        $valid = [];

        foreach ($best as $assetnum => $info) {
            $valid[] = [
                'row' => $info['where'],
                'assetnum' => (string) $assetnum,
                'status_code' => $info['status'],
                'measured_at' => $date,
                'keterangan' => null,
                'plta' => $info['plta'],
                'equipment' => $info['name'],
                'master' => $info['master'],
                'note' => $info['note'],
            ];
        }

        if ($valid === [] && $errors === []) {
            $errors[] = [
                'row' => '-',
                'plta' => '-',
                'equipment' => '-',
                'assetnum' => '-',
                'reason' => 'Tidak ada baris yang memiliki equipment dan status terisi.',
            ];
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Membaca format resume
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cari baris header format resume di 20 baris pertama sebuah sheet.
     *
     * @return array{row: int, plta: int, equip: int, noeq: int|null, status: int}|null
     */
    private function findResumeHeader(array $rows): ?array
    {
        foreach (array_slice($rows, 0, 20, true) as $i => $row) {
            $cells = array_map(fn ($h) => $this->normalize($h), $row);

            $plta = array_search('PLTA', $cells, true);
            if ($plta === false) {
                continue;
            }

            $status = $this->statusColumn($cells);
            if ($status === null) {
                continue;
            }

            $equip = null;
            $noeq = null;

            foreach ($cells as $idx => $name) {
                if ($equip === null && str_starts_with($name, 'EQUIPMENT')) {
                    $equip = $idx;
                }
                if ($noeq === null && (str_starts_with($name, 'NO. EQUIP') || str_starts_with($name, 'NO EQUIP'))) {
                    $noeq = $idx;
                }
            }

            if ($equip === null) {
                continue;
            }

            return [
                'row' => (int) $i,
                'plta' => (int) $plta,
                'equip' => (int) $equip,
                'noeq' => $noeq !== null ? (int) $noeq : null,
                'status' => $status,
            ];
        }

        return null;
    }

    /**
     * Kolom hasil akhir. Urutan prioritas: "Status Terakhir", "Status Akhir", "Resume", "Status".
     *
     * @param  array<int, string>  $cells
     */
    private function statusColumn(array $cells): ?int
    {
        $rules = [
            fn (string $n): bool => str_starts_with($n, 'STATUS TERAKHIR'),
            fn (string $n): bool => str_starts_with($n, 'STATUS AKHIR'),
            fn (string $n): bool => $n === 'RESUME',
            fn (string $n): bool => $n === 'STATUS',
        ];

        foreach ($rules as $rule) {
            foreach ($cells as $idx => $name) {
                if ($rule($name)) {
                    return (int) $idx;
                }
            }
        }

        return null;
    }

    /**
     * Kumpulkan baris data format resume.
     * - Baris pemisah ("PLTA SENGGURUH") dan baris tanpa kode PLTA dilewati.
     * - Nama equipment yang kosong (baris lanjutan item uji) diisi dari baris di atasnya.
     * - Baris tanpa status dilewati (belum ada hasil uji).
     */
    private function collectResumeRows(array $rows, array $h, string $sheetTitle, string $parameter, array &$entries): void
    {
        $lastPlta = '';
        $lastName = '';
        $lastNo = '';

        for ($i = $h['row'] + 1, $n = count($rows); $i < $n; $i++) {
            $row = $rows[$i];

            $plta = $this->pltaCode(trim((string) ($row[$h['plta']] ?? '')));

            if ($plta === '') {
                $lastPlta = $lastName = $lastNo = '';

                continue;
            }

            $name = trim((string) ($row[$h['equip']] ?? ''));
            $noeq = $h['noeq'] !== null ? trim((string) ($row[$h['noeq']] ?? '')) : '';

            // Baris pemisah per PLTA.
            if (str_starts_with(strtoupper($name), 'PLTA ')) {
                $lastPlta = $lastName = $lastNo = '';

                continue;
            }

            // PD: kolom EQUIPMENT berisi nomor unit generator (1, 2, ...).
            if ($parameter === 'pd' && $name !== '' && preg_match('/^\d+(\.0+)?$/', $name)) {
                $name = 'GENERATOR UNIT '.(int) $name;
            }

            if ($name === '' && $lastPlta === $plta) {
                $name = $lastName;
                $noeq = $noeq !== '' ? $noeq : $lastNo;
            } elseif ($name !== '') {
                $lastPlta = $plta;
                $lastName = $name;
                $lastNo = $noeq;
            }

            $status = strtoupper(trim((string) ($row[$h['status']] ?? '')));

            if ($status === '' || $status === '-' || $name === '') {
                continue;
            }

            $entries[] = [
                'where' => $sheetTitle.' baris '.($i + 1),
                'assetnum' => null,
                'plta' => $plta,
                'name' => $name,
                'noeq' => $noeq,
                'status' => $status,
            ];
        }
    }

    /** "SGR (E)" → "SGR". Nilai non-huruf dianggap bukan kode PLTA. */
    private function pltaCode(string $raw): string
    {
        return preg_match('/^[A-Z]{2,4}/', strtoupper($raw), $m) ? $m[0] : '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Mencocokkan equipment Excel dengan master equipment
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Index master equipment per kode PLTA (3 huruf setelah "B" pada ASSETNUM, misal BSGR… → SGR).
     *
     * Nama master berbentuk "TA-01-RUNNER TURBINE UNIT 1": awalan "TA-01-" dibuang dan nomor unit (01)
     * dijadikan kata tersendiri. Kolom System/Unit tidak dipakai karena berisi kata umum yang sama
     * untuk puluhan equipment.
     *
     * @return array{0: array<string, array<int, array<string, mixed>>>, 1: array<string, true>}
     */
    private function equipmentIndex(): array
    {
        $index = [];
        $known = [];

        // Kolom cbm_alias (nama di file Excel) opsional: dipakai bila migrasinya sudah dijalankan.
        $hasAlias = Schema::hasColumn((new Equipment())->getTable(), 'cbm_alias');
        $columns = $hasAlias ? ['assetnum', 'equipment', 'kks', 'cbm_alias'] : ['assetnum', 'equipment', 'kks'];

        foreach (Equipment::query()->orderBy('assetnum')->get($columns) as $eq) {
            $asset = strtoupper((string) $eq->assetnum);
            if ($asset === '') {
                continue;
            }

            $known[$asset] = true;

            $code = (str_starts_with($asset, 'B') && strlen($asset) >= 4) ? substr($asset, 1, 3) : '';

            $label = trim((string) ($eq->equipment ?? ''));
            $base = strtoupper($label);
            $unitToken = '';

            if (preg_match('/^[A-Z]{1,3}\s*-\s*(\d{1,2})\s*-\s*(.*)$/', $base, $m)) {
                $unitToken = (string) ((int) $m[1]);
                $base = $m[2];
            }

            $tokens = $this->tokens($base);
            if ($unitToken !== '' && ! isset($tokens[$unitToken])) {
                $tokens[$unitToken] = 1.5;
            }

            $weight = array_sum($tokens);

            $aliases = [];
            if ($hasAlias && trim((string) $eq->cbm_alias) !== '') {
                foreach (explode(',', (string) $eq->cbm_alias) as $alias) {
                    $key = $this->compact($alias);
                    if ($key !== '') {
                        $aliases[$key] = true;
                    }
                }
            }

            $index[$code][] = [
                'assetnum' => $asset,
                'label' => $label,
                'set' => $tokens,
                'weight' => $weight > 0 ? (float) $weight : 1.0,
                'kks' => $this->compact((string) ($eq->kks ?? '')),
                'aliases' => $aliases,
            ];
        }

        return [$index, $known];
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $index
     * @return array{assetnum: ?string, label: ?string, note: ?string, error: ?string, hint: ?string}
     */
    private function matchEquipment(array $index, string $plta, string $name, string $noeq): array
    {
        $fail = fn (string $error, string $hint = '—'): array => [
            'assetnum' => null, 'label' => null, 'note' => null, 'error' => $error, 'hint' => $hint,
        ];

        $candidates = $index[$plta] ?? [];

        if ($candidates === []) {
            return $fail('Tidak ada equipment di master untuk kode PLTA "'.$plta.'" (ASSETNUM diawali B'.$plta.').');
        }

        // 1. Alias yang diisi di master equipment (nama di file Excel) dicocokkan lebih dulu, tanpa tebakan.
        $aliasKeys = array_values(array_unique(array_filter([
            $this->compact($name),
            $this->compact($name.' '.$noeq),
        ])));

        $byAlias = [];
        foreach ($candidates as $c) {
            foreach ($aliasKeys as $key) {
                if (isset($c['aliases'][$key])) {
                    $byAlias[] = $c;
                    break;
                }
            }
        }

        if ($byAlias !== []) {
            return [
                'assetnum' => $byAlias[0]['assetnum'],
                'label' => $byAlias[0]['label'],
                'note' => count($byAlias) > 1
                    ? 'alias yang sama dipakai '.count($byAlias).' equipment, dipilih yang pertama'
                    : null,
                'error' => null,
                'hint' => null,
            ];
        }

        // 2. Tidak ada alias: cocokkan berdasarkan kemiripan nama.
        $query = $this->tokens($name);
        $noeqKey = $this->compact($noeq);

        $scored = [];
        foreach ($candidates as $c) {
            $scored[] = [$this->score($query, $c, $noeqKey), $c];
        }

        // Skor tertinggi lebih dulu; jika sama, ASSETNUM terkecil (urutan master).
        usort($scored, fn (array $a, array $b): int => [$b[0], $a[1]['assetnum']] <=> [$a[0], $b[1]['assetnum']]);

        $topScore = $scored[0][0];
        $best = $scored[0][1];

        if ($topScore < self::MATCH_THRESHOLD) {
            return $fail(
                'Tidak ada padanan di master equipment. Terdekat: '.$best['assetnum'].' ('.$best['label'].').',
                $best['assetnum'].'?'
            );
        }

        $tied = array_filter($scored, fn (array $s): bool => $s[0] >= $topScore - 0.0001);
        $note = count($tied) > 1
            ? 'dipilih otomatis dari '.count($tied).' kandidat yang sama kuat'
            : null;

        return [
            'assetnum' => $best['assetnum'],
            'label' => $best['label'],
            'note' => $note,
            'error' => null,
            'hint' => null,
        ];
    }

    /**
     * Skor kecocokan 0..±1,5.
     * - recall   : seberapa banyak kata di nama Excel ditemukan di nama master (berbobot).
     * - presisi  : seberapa "sederhana" nama master; nama master yang sama persis lebih diutamakan
     *              daripada komponen yang namanya lebih panjang.
     * - No. Equip. yang sama dengan KKS/kata di nama master memberi bonus.
     *
     * @param  array<string, float>  $query
     * @param  array<string, mixed>  $candidate
     */
    private function score(array $query, array $candidate, string $noeqKey): float
    {
        $total = 0.0;
        $hit = 0.0;
        $digitMissing = false;

        foreach ($query as $token => $weight) {
            $token = (string) $token;
            $total += $weight;

            if (isset($candidate['set'][$token])) {
                $hit += $weight;
            } elseif (ctype_digit($token)) {
                $digitMissing = true;
            }
        }

        if ($total <= 0) {
            return 0.0;
        }

        $recall = $hit / $total;
        $precision = $hit / $candidate['weight'];

        $score = $recall * (0.7 + 0.3 * $precision);

        // Nomor unit/pompa (1, 2, 11, …) harus cocok.
        if ($digitMissing) {
            $score *= 0.4;
        }

        if ($noeqKey !== '' && ($candidate['kks'] === $noeqKey || isset($candidate['set'][$noeqKey]))) {
            $score += 0.5;
        }

        return $score;
    }

    /**
     * Pecah nama equipment menjadi kata berbobot. Angka "01" disamakan dengan "1".
     *
     * @return array<string, float>
     */
    private function tokens(string $text): array
    {
        $text = strtoupper(str_replace('#', ' ', $text));
        $text = (string) preg_replace('/[^A-Z0-9]+/', ' ', $text);

        $out = [];

        foreach (preg_split('/\s+/', trim($text)) ?: [] as $word) {
            if ($word === '') {
                continue;
            }

            if (ctype_digit($word)) {
                $word = (string) ((int) $word);
            }

            $word = self::SYNONYMS[$word] ?? $word;

            if (in_array($word, self::STOPWORDS, true)) {
                continue;
            }

            $out[$word] = match (true) {
                ctype_digit($word) => 1.5,
                in_array($word, self::LOW_WEIGHT, true) => 0.25,
                default => 1.0,
            };
        }

        return $out;
    }

    /** Huruf besar tanpa spasi/tanda baca (untuk membandingkan KKS / No. Equip.). */
    private function compact(string $value): string
    {
        return (string) preg_replace('/[^A-Z0-9]+/', '', strtoupper($value));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Format lama (ASSETNUM + STATUS)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{0: ?int, 1: int|false, 2: int|false}
     */
    private function findLegacyHeader(array $rows): array
    {
        foreach (array_slice($rows, 0, 20, true) as $i => $row) {
            $header = array_map(fn ($h) => $this->normalize($h), $row);

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

    private function normalize(mixed $value): string
    {
        return strtoupper(trim((string) preg_replace('/\s+/', ' ', (string) $value)));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Simpan & template
    // ─────────────────────────────────────────────────────────────────────────

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
        $sheet->fromArray(['ASSETNUM', 'STATUS', 'EQUIPMENT'], null, 'A1');

        $query = Equipment::query()->orderBy('assetnum');
        if ($pltaId) {
            $query->where('plta_id', $pltaId);
        }

        $r = 2;
        foreach ($query->get(['assetnum', 'equipment']) as $eq) {
            $sheet->setCellValue("A{$r}", $eq->assetnum);
            $sheet->setCellValue("C{$r}", $eq->equipment);
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

        foreach (['A', 'B', 'C'] as $c) {
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