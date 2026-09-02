<?php

namespace App\Http\Controllers;

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
        return [
            ['slug' => 'sengguruh',    'name' => 'PLTA Sengguruh',    'code' => 'BSGR', 'location' => 'Malang, Jawa Timur',      'capacity' => '29 MW'],
            ['slug' => 'sutami',       'name' => 'PLTA Sutami',       'code' => 'BSTM', 'location' => 'Malang, Jawa Timur',      'capacity' => '105 MW'],
            ['slug' => 'wlingi',       'name' => 'PLTA Wlingi',       'code' => 'BWLG', 'location' => 'Blitar, Jawa Timur',      'capacity' => '54 MW'],
            ['slug' => 'lodoyo',       'name' => 'PLTA Lodoyo',       'code' => 'BLDY', 'location' => 'Blitar, Jawa Timur',      'capacity' => '4.5 MW'],
            ['slug' => 'tulungagung',  'name' => 'PLTA Tulungagung',  'code' => 'BTLA', 'location' => 'Tulungagung, Jawa Timur', 'capacity' => '6 MW'],
            ['slug' => 'mendalan',     'name' => 'PLTA Mendalan',     'code' => 'BMDL', 'location' => 'Malang, Jawa Timur',      'capacity' => '23.4 MW'],
            ['slug' => 'siman',        'name' => 'PLTA Siman',        'code' => 'BSMN', 'location' => 'Malang, Jawa Timur',      'capacity' => '12 MW'],
            ['slug' => 'wonorejo',     'name' => 'PLTA Wonorejo',     'code' => 'BWRJ', 'location' => 'Tulungagung, Jawa Timur', 'capacity' => '6.25 MW'],
            ['slug' => 'ampelgading',      'name' => 'PLTA Ampelgading',      'code' => 'BAMG', 'location' => 'Malang, Jawa Timur',     'capacity' => '6.3 MW'],
            ['slug' => 'giringan',      'name' => 'PLTA Giringan',      'code' => 'BGRG', 'location' => 'Bandung, Jawa Barat',     'capacity' => '18.5 MW'],
            ['slug' => 'golang',     'name' => 'PLTA Golang',     'code' => 'BGLG', 'location' => 'Bandung, Jawa Barat',     'capacity' => '17.5 MW'],
            ['slug' => 'ngebel',      'name' => 'PLTA Ngebel',      'code' => 'BNB', 'location' => 'Bandung, Jawa Barat',     'capacity' => '3 MW'],
            ['slug' => 'selorejo',         'name' => 'Selorejo',         'code' => 'BSLJ', 'location' => 'Bandung, Jawa Barat',     'capacity' => '1.75 MW'],
        ];
    }

    public function index(): View
    {
        $pltaList = self::pltaList();

        $stats = [
            'total_plta' => 13,
            'total_equipment' => 487,
            'normal' => 391,
            'abnormal' => 68,
            'not_ready' => 28,
        ];

        $recentActivity = [
            ['time' => '08:15', 'plta' => 'PLTA Sutami',   'assetnum' => 'BSTM020045', 'status' => 'Abnormal',  'wo' => 'WO-2024-0892'],
            ['time' => '07:43', 'plta' => 'PLTA Wlingi',   'assetnum' => 'BWLG010012', 'status' => 'Normal',    'wo' => 'WO-2024-0891'],
            ['time' => '07:20', 'plta' => 'PLTA Sengguruh', 'assetnum' => 'BSGR010078', 'status' => 'Not Ready', 'wo' => '-'],
            ['time' => '06:55', 'plta' => 'PLTA Lamajan',  'assetnum' => 'BLMJ030021', 'status' => 'Abnormal',  'wo' => 'WO-2024-0889'],
            ['time' => '06:30', 'plta' => 'PLTA Plengan',  'assetnum' => 'BPLG010009', 'status' => 'Normal',    'wo' => 'WO-2024-0888'],
        ];

        return view('dashboard', compact('pltaList', 'stats', 'recentActivity'));
    }
}
