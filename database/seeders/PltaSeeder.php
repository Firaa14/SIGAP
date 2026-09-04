<?php

namespace Database\Seeders;

use App\Models\Plta;
use Illuminate\Database\Seeder;

class PltaSeeder extends Seeder
{
    /**
     * 13 PLTA dengan kode_prefix dan slug canonical.
     *
     * @return array<int, array{nama_plta: string, kode_prefix: string, slug: string, location: string, capacity: string}>
     */
    public static function pltaData(): array
    {
        return [
            [
                'nama_plta' => 'PLTA Ampelgading',
                'kode_prefix' => 'BAMG',
                'slug' => 'ampelgading',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '2 x 5 MW',
            ],
            [
                'nama_plta' => 'PLTA Sengguruh',
                'kode_prefix' => 'BSGR',
                'slug' => 'sengguruh',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '2 x 14.5 MW',
            ],
            [
                'nama_plta' => 'PLTA Sutami',
                'kode_prefix' => 'BSTM',
                'slug' => 'sutami',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '3 x 35 MW',
            ],
            [
                'nama_plta' => 'PLTA Selorejo',
                'kode_prefix' => 'BSLJ',
                'slug' => 'selorejo',
                'location' => 'Blitar, Jawa Timur',
                'capacity' => '1 x 4.8 MW',
            ],
            [
                'nama_plta' => 'PLTA Wonorejo',
                'kode_prefix' => 'BWNJ',
                'slug' => 'wonorejo',
                'location' => 'Tulungagung, Jawa Timur',
                'capacity' => '1 x 6.2 MW',
            ],
            [
                'nama_plta' => 'PLTA Lodoyo',
                'kode_prefix' => 'BLDY',
                'slug' => 'lodoyo',
                'location' => 'Blitar, Jawa Timur',
                'capacity' => '1 x 4.7 MW',
            ],
            [
                'nama_plta' => 'PLTA Siman',
                'kode_prefix' => 'BSMN',
                'slug' => 'siman',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '3 x 3.6 MW',
            ],
            [
                'nama_plta' => 'PLTA Golang',
                'kode_prefix' => 'BGLG',
                'slug' => 'golang',
                'location' => 'Madiun, Jawa Timur',
                'capacity' => '3 x 0.9 MW',
            ],
            [
                'nama_plta' => 'PLTA Giringan',
                'kode_prefix' => 'BGRG',
                'slug' => 'giringan',
                'location' => 'Madiun, Jawa Timur',
                'capacity' => '2 x 0.9 MW'.'1 x 1.4 MW',
            ],
            [
                'nama_plta' => 'PLTA Tulungagung',
                'kode_prefix' => 'BTLG',
                'slug' => 'tulungagung',
                'location' => 'Tulungagung, Jawa Timur',
                'capacity' => '2 x 18 MW',
            ],
            [
                'nama_plta' => 'PLTA Ngebel',
                'kode_prefix' => 'BNBL',
                'slug' => 'ngebel',
                'location' => 'Ponorogo, Jawa Timur',
                'capacity' => '1 x 2.2 MW',
            ],
            [
                'nama_plta' => 'PLTA Mendalan',
                'kode_prefix' => 'BMDL',
                'slug' => 'mendalan',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '1 x 5.6 MW'.'3 x 5.8 MW',
            ],
            [
                'nama_plta' => 'PLTA Wlingi',
                'kode_prefix' => 'BWLG',
                'slug' => 'wlingi',
                'location' => 'Blitar, Jawa Timur',
                'capacity' => '2 x 27 MW',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::pltaData() as $data) {
            Plta::firstOrCreate(['kode_prefix' => $data['kode_prefix']], $data);
        }
    }
}
