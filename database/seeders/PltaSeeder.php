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
