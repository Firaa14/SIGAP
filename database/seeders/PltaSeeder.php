<?php

namespace Database\Seeders;

use App\Models\Plta;
use Illuminate\Database\Seeder;

class PltaSeeder extends Seeder
{
    /**
     * 13 PLTA dengan kode_prefix, slug canonical, dan koordinat lokasi
     * (diambil dari titik lokasi Google Maps masing-masing PLTA).
     *
     * @return array<int, array{nama_plta: string, kode_prefix: string, slug: string, location: string, capacity: string, latitude: float, longitude: float}>
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
                'latitude' => -8.2600164,
                'longitude' => 112.9108671,
            ],
            [
                'nama_plta' => 'PLTA Sengguruh',
                'kode_prefix' => 'BSGR',
                'slug' => 'sengguruh',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '2 x 14.5 MW',
                'latitude' => -8.1852575,
                'longitude' => 112.5492457,
            ],
            [
                'nama_plta' => 'PLTA Sutami',
                'kode_prefix' => 'BSTM',
                'slug' => 'sutami',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '3 x 35 MW',
                'latitude' => -8.1612004,
                'longitude' => 112.4442189,
            ],
            [
                'nama_plta' => 'PLTA Selorejo',
                'kode_prefix' => 'BSLJ',
                'slug' => 'selorejo',
                'location' => 'Blitar, Jawa Timur',
                'capacity' => '1 x 4.8 MW',
                'latitude' => -7.8733008,
                'longitude' => 112.3515660,
            ],
            [
                'nama_plta' => 'PLTA Wonorejo',
                'kode_prefix' => 'BWNJ',
                'slug' => 'wonorejo',
                'location' => 'Tulungagung, Jawa Timur',
                'capacity' => '1 x 6.2 MW',
                'latitude' => -8.0205297,
                'longitude' => 111.8075256,
            ],
            [
                'nama_plta' => 'PLTA Lodoyo',
                'kode_prefix' => 'BLDY',
                'slug' => 'lodoyo',
                'location' => 'Blitar, Jawa Timur',
                'capacity' => '1 x 4.7 MW',
                'latitude' => -8.1489650,
                'longitude' => 112.1905331,
            ],
            [
                'nama_plta' => 'PLTA Siman',
                'kode_prefix' => 'BSMN',
                'slug' => 'siman',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '3 x 3.6 MW',
                'latitude' => -7.8293071,
                'longitude' => 112.3084503,
            ],
            [
                'nama_plta' => 'PLTA Golang',
                'kode_prefix' => 'BGLG',
                'slug' => 'golang',
                'location' => 'Madiun, Jawa Timur',
                'capacity' => '3 x 0.9 MW',
                'latitude' => -7.7043509,
                'longitude' => 111.6584054,
            ],
            [
                'nama_plta' => 'PLTA Giringan',
                'kode_prefix' => 'BGRG',
                'slug' => 'giringan',
                'location' => 'Madiun, Jawa Timur',
                'capacity' => '2 x 0.9 MW'.'1 x 1.4 MW',
                'latitude' => -7.7216238,
                'longitude' => 111.6743170,
            ],
            [
                'nama_plta' => 'PLTA Tulungagung',
                'kode_prefix' => 'BTLG',
                'slug' => 'tulungagung',
                'location' => 'Tulungagung, Jawa Timur',
                'capacity' => '2 x 18 MW',
                'latitude' => -8.2534440,
                'longitude' => 111.7941460,
            ],
            [
                'nama_plta' => 'PLTA Ngebel',
                'kode_prefix' => 'BNBL',
                'slug' => 'ngebel',
                'location' => 'Ponorogo, Jawa Timur',
                'capacity' => '1 x 2.2 MW',
                'latitude' => -7.8148424,
                'longitude' => 111.6205171,
            ],
            [
                'nama_plta' => 'PLTA Mendalan',
                'kode_prefix' => 'BMDL',
                'slug' => 'mendalan',
                'location' => 'Malang, Jawa Timur',
                'capacity' => '1 x 5.6 MW'.'3 x 5.8 MW',
                'latitude' => -7.8558336,
                'longitude' => 112.3220126,
            ],
            [
                'nama_plta' => 'PLTA Wlingi',
                'kode_prefix' => 'BWLG',
                'slug' => 'wlingi',
                'location' => 'Blitar, Jawa Timur',
                'capacity' => '2 x 27 MW',
                'latitude' => -8.1420988,
                'longitude' => 112.2473165,
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::pltaData() as $data) {
            // updateOrCreate supaya PLTA yang sudah ada di database (nama, capacity, dst)
            // ikut ter-update dengan koordinat latitude/longitude baru tanpa duplikasi baris.
            Plta::updateOrCreate(['kode_prefix' => $data['kode_prefix']], $data);
        }
    }
}
