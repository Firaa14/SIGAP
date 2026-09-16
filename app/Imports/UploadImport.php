<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

// class buat baca file excel yang di-upload
class UploadImport implements ToCollection, WithHeadingRow
{
    public $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}