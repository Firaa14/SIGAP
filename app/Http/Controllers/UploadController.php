<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UploadController extends Controller
{
    /**
     * Dummy preview data untuk simulasi upload Excel.
     *
     * @return array<int, array{assetnum: string, no_wo: string, description: string, worktype: string, status: string, plta: string, status_operasi: string}>
     */
    private function getDummyPreviewData(): array
    {
        return [
            ['assetnum' => 'BSGR010078', 'no_wo' => 'WO-2024-0892', 'description' => 'Governor Valve Leaking', 'worktype' => 'CM', 'status' => 'INPRG', 'plta' => 'Sengguruh', 'status_operasi' => 'Abnormal'],
            ['assetnum' => 'BSTM010045', 'no_wo' => 'WO-2024-0888', 'description' => 'Penstock Valve Inspection', 'worktype' => 'EV', 'status' => 'APPR', 'plta' => 'Sutami', 'status_operasi' => 'Abnormal'],
            ['assetnum' => 'BWLG010012', 'no_wo' => 'WO-2024-0891', 'description' => 'Runner Balancing', 'worktype' => 'PAM', 'status' => 'CLOSE', 'plta' => 'Wlingi', 'status_operasi' => 'Normal'],
            ['assetnum' => 'BSTM020033', 'no_wo' => 'WO-2024-0905', 'description' => 'Thrust Bearing Vibration', 'worktype' => 'CM', 'status' => 'WPTW', 'plta' => 'Sutami', 'status_operasi' => 'Abnormal'],
            ['assetnum' => 'BSLJ010021', 'no_wo' => 'WO-2024-0889', 'description' => 'Bearing Temperature High', 'worktype' => 'CM', 'status' => 'INPRG', 'plta' => 'Selorejo', 'status_operasi' => 'Abnormal'],
            ['assetnum' => 'BSTM030071', 'no_wo' => 'WO-2024-0841', 'description' => 'Guide Vane Maintenance', 'worktype' => 'PAM', 'status' => 'COMP', 'plta' => 'Sutami', 'status_operasi' => 'Normal'],
            ['assetnum' => 'BWNJ010021', 'no_wo' => 'WO-2024-0911', 'description' => 'Runner Cavitation Repair', 'worktype' => 'EJ', 'status' => 'INPRG', 'plta' => 'Wonorejo', 'status_operasi' => 'Abnormal'],
            ['assetnum' => 'BAMG010009', 'no_wo' => 'WO-2024-0883', 'description' => 'Nozzle Cleaning & Check', 'worktype' => 'PAM', 'status' => 'CLOSE', 'plta' => 'Ampelgading', 'status_operasi' => 'Normal'],
        ];
    }

    public function index(): View
    {
        $pltaList = DashboardController::pltaList();
        $showPreview = false;
        $previewData = [];

        return view('upload.index', compact('pltaList', 'showPreview', 'previewData'));
    }

    public function preview(): View
    {
        $pltaList = DashboardController::pltaList();
        $showPreview = true;
        $previewData = $this->getDummyPreviewData();

        $validationResults = [
            'total_rows' => 8,
            'valid_rows' => 8,
            'invalid_rows' => 0,
            'valid_worktypes' => ['CM', 'EJ', 'EV', 'PAM'],
            'found_plta' => ['Sengguruh', 'Sutami', 'Wlingi', 'Selorejo', 'Wonorejo', 'Ampelgading'],
        ];

        return view('upload.index', compact('pltaList', 'showPreview', 'previewData', 'validationResults'));
    }
}
