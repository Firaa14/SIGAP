<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\CbmAutoAssetService;
use App\Services\CbmEquipmentService;
use App\Services\CbmImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CbmUploadController extends Controller
{
    public function __construct(
        private CbmImportService $importer,
        private CbmEquipmentService $cbm,
        private CbmAutoAssetService $autoAssets
    ) {
    }

    public function index(): View
    {
        $this->authorizeUpload();

        return $this->page(null);
    }

    public function template(string $parameter)
    {
        $this->authorizeUpload();
        abort_unless(array_key_exists($parameter, CbmEquipmentService::PARAMETERS), 404);

        $spreadsheet = $this->importer->template($parameter);

        return response()->streamDownload(
            fn () => $this->importer->writeTemplate($spreadsheet),
            "template_cbm_{$parameter}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function preview(Request $request): View
    {
        $this->authorizeUpload();

        @set_time_limit(0);
        @ini_set('memory_limit', '-1');

        $data = $request->validate([
            'parameter' => ['required', Rule::in(array_keys(CbmEquipmentService::PARAMETERS))],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $this->deleteTempFile();

        $file = $request->file('file');
        $stored = $file->store('cbm_tmp');
        $result = $this->importer->parse(Storage::path($stored), $data['parameter']);

        session(['cbm_import' => [
            'parameter' => $data['parameter'],
            'rows' => $result['valid'],
            'file' => $stored,
            'filename' => $file->getClientOriginalName(),
            'auto' => $this->autoCandidates($result['errors']),
        ]]);

        return $this->page([
            'parameter' => $data['parameter'],
            'valid' => $result['valid'],
            'errors' => $this->withNearInfo($result['errors']),
            'filename' => $file->getClientOriginalName(),
        ], $data['parameter']);
    }

    /** Proses baris gagal sesuai pilihan (buat baru / alias / ganti nama), lalu cek ulang file. */
    public function autoCreate(Request $request): View|RedirectResponse
    {
        $this->authorizeUpload();

        @set_time_limit(0);
        @ini_set('memory_limit', '-1');

        $pending = session('cbm_import');

        if (! $pending || empty($pending['file']) || ! Storage::exists($pending['file'])) {
            return redirect()->route('cbm.upload.index')
                ->withErrors(['file' => 'File upload sudah tidak ada. Upload ulang file.']);
        }

        $actions = (array) $request->input('action', []);
        $meta = (array) $request->input('meta', []);
        $items = [];

        foreach (($pending['auto'] ?? []) as $key => $auto) {
            $act = $actions[$key] ?? 'skip';

            if (! in_array($act, ['new', 'alias', 'rename'], true)) {
                continue;
            }

            $items[] = $auto + [
                'action' => $act,
                'unit_in' => trim((string) ($meta[$key]['unit'] ?? '')),
                'system_in' => trim((string) ($meta[$key]['system'] ?? '')),
                'kks_in' => trim((string) ($meta[$key]['kks'] ?? '')),
            ];
        }

        if ($items === []) {
            return redirect()->route('cbm.upload.index')
                ->withErrors(['file' => 'Tidak ada baris yang dipilih untuk dibuat atau dihubungkan.']);
        }

        $created = $this->autoAssets->create($items, $pending['parameter']);

        $result = $this->importer->parse(Storage::path($pending['file']), $pending['parameter']);

        session(['cbm_import' => array_merge($pending, [
            'rows' => $result['valid'],
            'auto' => $this->autoCandidates($result['errors']),
        ])]);

        return $this->page([
            'parameter' => $pending['parameter'],
            'valid' => $result['valid'],
            'errors' => $this->withNearInfo($result['errors']),
            'filename' => $pending['filename'] ?? 'file',
        ], $pending['parameter'], $created);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $this->authorizeUpload();

        @set_time_limit(0);
        @ini_set('memory_limit', '-1');

        $pending = session('cbm_import');

        if (! $pending || empty($pending['rows'])) {
            return redirect()->route('cbm.upload.index')
                ->withErrors(['file' => 'Tidak ada data untuk disimpan. Upload ulang file.']);
        }

        $count = $this->importer->store($pending['parameter'], $pending['rows'], $request->user()?->id);

        $this->deleteTempFile();
        session()->forget('cbm_import');

        $label = CbmEquipmentService::PARAMETERS[$pending['parameter']];

        return redirect()->route('cbm.upload.index')
            ->with('status', "{$count} data {$label} berhasil disimpan.");
    }

    public function cancel(): RedirectResponse
    {
        $this->deleteTempFile();
        session()->forget('cbm_import');

        return redirect()->route('cbm.upload.index');
    }

    /** @param array<int, array<string, mixed>> $errors */
    private function autoCandidates(array $errors): array
    {
        $out = [];

        foreach ($errors as $err) {
            if (! empty($err['auto_key']) && ! empty($err['auto'])) {
                $out[$err['auto_key']] = $err['auto'];
            }
        }

        return $out;
    }

    /**
     * Isian awal Unit, System, KKS di tabel merah.
     * Disalin dari alat terdekat; kalau System-nya kosong/AUTO, disusun dari pola master + nama Excel.
     *
     * @param  array<int, array<string, mixed>>  $errors
     * @return array<int, array<string, mixed>>
     */
    private function withNearInfo(array $errors): array
    {
        $nums = collect($errors)->pluck('near.assetnum')->filter()->unique()->all();

        $rows = $nums === []
            ? collect()
            : Equipment::query()
                ->whereIn('assetnum', $nums)
                ->get(['assetnum', 'unit', 'system', 'kks'])
                ->keyBy('assetnum');

        foreach ($errors as &$e) {
            $n = $e['near']['assetnum'] ?? null;
            $row = $n ? $rows->get($n) : null;

            $unit = trim((string) ($row->unit ?? ''));
            $system = trim((string) ($row->system ?? ''));
            $kks = trim((string) ($row->kks ?? ''));

            if ($system === '' || stripos($system, 'AUTO DARI UPLOAD CBM') !== false) {
                $system = $this->guessSystem($e, $unit);
            }

            $e['near_info'] = ['unit' => $unit, 'system' => $system, 'kks' => $kks];
        }
        unset($e);

        return $errors;
    }

    /** Contoh: TA BSGR COMMON DRAINAGE PUMP C, atau TA BSTM UNIT #1 GOVERNOR PUMP 1A. */
    private function guessSystem(array $err, string $unit): string
    {
        $code = strtoupper(trim((string) ($err['plta'] ?? '')));
        $name = strtoupper(trim((string) ($err['equipment'] ?? '')));

        if ($code === '' || $name === '') {
            return '';
        }

        $loc = 'COMMON';

        if (stripos($unit, 'COMMON') === false
            && preg_match('/UNIT\s*#?\s*(\d+)/i', $unit.' '.$name, $m)) {
            $loc = 'UNIT #'.(int) $m[1];
        }

        return 'TA B'.$code.' '.$loc.' '.$name;
    }

    private function deleteTempFile(): void
    {
        $file = session('cbm_import.file');

        if ($file && Storage::exists($file)) {
            Storage::delete($file);
        }
    }

    private function page(?array $preview, ?string $selected = null, ?array $created = null): View
    {
        return view('upload.cbm-upload', [
            'parameters' => CbmEquipmentService::PARAMETERS,
            'legend' => $this->cbm->statusLegend(),
            'selected' => $selected ?? old('parameter', array_key_first(CbmEquipmentService::PARAMETERS)),
            'preview' => $preview,
            'created' => $created,
        ]);
    }

    private function authorizeUpload(): void
    {
        abort_unless(auth()->check(), 403);
    }
}