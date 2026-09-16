<?php

namespace App\Http\Controllers;

use App\Models\UploadHistory;
use App\Services\ExcelUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function index(): View
    {
        $pltaList = DashboardController::pltaList();

        return view('upload.index', [
            'pltaList' => $pltaList,
            'showPreview' => false,
            'previewData' => [],
        ]);
    }

    public function preview(Request $request, ExcelUploadService $service): View|RedirectResponse
    {
        $pltaList = DashboardController::pltaList();

        $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],
        ], [
            'excel_file.required' => 'Pilih file Excel terlebih dahulu.',
            'excel_file.file' => 'Upload harus berupa file.',
            'excel_file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'excel_file.max' => 'Ukuran file maksimum 20 MB.',
        ]);

        $file = $request->file('excel_file');
        $originalName = $file->getClientOriginalName();

        $tempPath = $file->store('temp', 'local');
        $absoluteTempPath = Storage::disk('local')->path($tempPath);

        try {
            $readResult = $service->read($absoluteTempPath);

            if (! empty($readResult['missing_required'])) {
                Storage::disk('local')->delete($tempPath);

                return view('upload.index', [
                    'pltaList' => $pltaList,
                    'showPreview' => false,
                    'previewData' => [],
                    'uploadError' => 'Kolom wajib tidak ditemukan dalam file: '.implode(', ', $readResult['missing_required']).'.',
                ]);
            }

            $rows = $readResult['rows'];

            if (empty($rows)) {
                Storage::disk('local')->delete($tempPath);

                return view('upload.index', [
                    'pltaList' => $pltaList,
                    'showPreview' => false,
                    'previewData' => [],
                    'uploadError' => 'File Excel tidak memiliki data (kosong).',
                ]);
            }

            $validated = $service->validate($rows);

        } catch (\Throwable $e) {
            Log::error('Excel upload processing failed.', [
                'filename' => $originalName,
                'exception' => $e,
            ]);

            Storage::disk('local')->delete($tempPath);

            return view('upload.index', [
                'pltaList' => $pltaList,
                'showPreview' => false,
                'previewData' => [],
                'uploadError' => 'File tidak dapat diproses. Pastikan file Excel tidak rusak dan formatnya benar.',
            ]);
        }

        session([
            'upload_valid_rows' => $validated['valid'],
            'upload_filename' => $originalName,
            'upload_total_rows' => $validated['summary']['total'],
            'upload_error_rows_count' => $validated['summary']['error_count'],
            'upload_temp_path' => $tempPath,
        ]);

        return view('upload.index', [
            'pltaList' => $pltaList,
            'showPreview' => true,
            'previewData' => $validated['valid'],
            'errorRows' => $validated['errors'],
            'sheetName' => $readResult['sheet_name'],
            'originalFilename' => $originalName,
            'validationResults' => [
                'total_rows' => $validated['summary']['total'],
                'valid_rows' => $validated['summary']['valid_count'],
                'invalid_rows' => $validated['summary']['error_count'],
                'new_count' => $validated['summary']['new_count'],
                'update_count' => $validated['summary']['update_count'],
                'found_plta' => $validated['summary']['pltas_found'],
            ],
        ]);
    }

    public function commit(Request $request, ExcelUploadService $service): RedirectResponse
    {
        $validRows = session('upload_valid_rows');
        $filename = session('upload_filename');
        $totalRows = session('upload_total_rows');
        $errorRowsCount = session('upload_error_rows_count');
        $tempPath = session('upload_temp_path');

        if (empty($validRows) || ! $filename) {
            return redirect()->route('upload.index')
                ->with('error', 'Sesi preview telah berakhir. Silakan upload file kembali.');
        }

        try {
            $result = $service->commitProtected(
                $validRows,
                $filename,
                $totalRows,
                $errorRowsCount
            );
        } catch (\Throwable $e) {
            Log::error('Import commit gagal.', [
                'filename' => $filename,
                'exception' => $e,
            ]);

            return redirect()->route('upload.index')
                ->with(
                    'error',
                    'Proses import gagal: '.$e->getMessage().' — Tidak ada data yang tersimpan. Silakan coba lagi.'
                );
        } finally {
            if ($tempPath) {
                Storage::disk('local')->delete($tempPath);
            }

            session()->forget([
                'upload_valid_rows',
                'upload_filename',
                'upload_total_rows',
                'upload_error_rows_count',
                'upload_temp_path',
            ]);
        }

        session()->put('show_upload_result', $result['history_id']);

        return redirect()->route('dashboard');
    }

    public function result(UploadHistory $history): View
    {
        $pltaList = DashboardController::pltaList();

        $canonicalOrder = collect($pltaList)->pluck('name');
        $distribution = $history->plta_distribution ?? [];

        $pltaDistribution = $canonicalOrder->map(fn (string $name) => [
            'name' => $name,
            'count' => $distribution[$name] ?? 0,
        ])->values();

        return view('upload.result', [
            'pltaList' => $pltaList,
            'history' => $history,
            'pltaDistribution' => $pltaDistribution,
        ]);
    }
}