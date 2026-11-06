<?php

namespace App\Http\Controllers;

use App\Services\CbmEquipmentService;
use App\Services\CbmImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CbmUploadController extends Controller
{
    public function __construct(
        private CbmImportService $importer,
        private CbmEquipmentService $cbm
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

        // Tanpa batas waktu dan memori untuk file besar.
        @set_time_limit(0);
        @ini_set('memory_limit', '-1');

        $data = $request->validate([
            'parameter' => ['required', Rule::in(array_keys(CbmEquipmentService::PARAMETERS))],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $result = $this->importer->parse($request->file('file')->getRealPath(), $data['parameter']);

        session(['cbm_import' => [
            'parameter' => $data['parameter'],
            'rows' => $result['valid'],
        ]]);

        return $this->page([
            'parameter' => $data['parameter'],
            'valid' => $result['valid'],
            'errors' => $result['errors'],
            'filename' => $request->file('file')->getClientOriginalName(),
        ], $data['parameter']);
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

        session()->forget('cbm_import');

        $label = CbmEquipmentService::PARAMETERS[$pending['parameter']];

        return redirect()->route('cbm.upload.index')
            ->with('status', "{$count} data {$label} berhasil disimpan.");
    }

    public function cancel(): RedirectResponse
    {
        session()->forget('cbm_import');

        return redirect()->route('cbm.upload.index');
    }

    private function page(?array $preview, ?string $selected = null): View
    {
        return view('upload.cbm-upload', [
            'parameters' => CbmEquipmentService::PARAMETERS,
            'legend' => $this->cbm->statusLegend(),
            'selected' => $selected ?? old('parameter', array_key_first(CbmEquipmentService::PARAMETERS)),
            'preview' => $preview,
        ]);
    }

    private function authorizeUpload(): void
    {
        // Sesuaikan dengan pembatasan role yang sama seperti fitur SO/CBM.
        abort_unless(auth()->check(), 403);
    }
}