<?php

namespace App\Http\Controllers;

use App\Models\Plta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Daftar 13 PLTA dengan kode prefix ASSETNUM.
     *
     * @return array<int, array{slug: string, name: string, code: string, location: string, capacity: string}>
     */
    public static function pltaList(): array
    {
        return Plta::query()->orderBy('id')->get()->map(fn(Plta $plta): array => [
            'slug' => $plta->slug,
            'name' => $plta->nama_plta,
            'code' => $plta->kode_prefix,
            'location' => $plta->location,
            'capacity' => $plta->capacity,
        ])->all();
    }

    public function index(): View
    {
        $pltaList = self::pltaList();

        $stats = [
            'total_plta' => count($pltaList),
            'total_equipment' => 487,
            'normal' => 391,
            'abnormal' => 68,
            'not_ready' => 28,
        ];

        $recentActivity = [
            ['time' => '08:15', 'plta' => 'PLTA Sutami', 'assetnum' => 'BSTM020045', 'status' => 'Abnormal', 'wo' => 'WO-2024-0892'],
            ['time' => '07:43', 'plta' => 'PLTA Wlingi', 'assetnum' => 'BWLG010012', 'status' => 'Normal', 'wo' => 'WO-2024-0891'],
            ['time' => '07:20', 'plta' => 'PLTA Sengguruh', 'assetnum' => 'BSGR010078', 'status' => 'Not Ready', 'wo' => '-'],
            ['time' => '06:55', 'plta' => 'PLTA Selorejo', 'assetnum' => 'BSLJ010021', 'status' => 'Abnormal', 'wo' => 'WO-2024-0889'],
            ['time' => '06:30', 'plta' => 'PLTA Ampelgading', 'assetnum' => 'BAMG010009', 'status' => 'Normal', 'wo' => 'WO-2024-0888'],
        ];

        return view('dashboard', compact('pltaList', 'stats', 'recentActivity'));
    }
}
