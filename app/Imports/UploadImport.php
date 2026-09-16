<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class buat baca file excel yang di-upload
class UploadImport implements ToCollection, WithHeadingRow
{
    public $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
