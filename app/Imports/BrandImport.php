<?php

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class BrandImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan baris kosong
        if (empty($row['Nama Merek'])) {
            return null;
        }

        $cabangId = getCabangId();

        // Simpan Data Baru dengan nama yang sudah aman
        return new Brand([
            // 'id'          => $row['ID Merek'],
            'name'          => $row['Nama Merek'],
            'cabang_id'   => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
