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

        // Update jika sudah ada, buat baru jika belum ada
        Brand::updateOrCreate(
            [
                'name' => $row['Nama Merek'], // pencarian berdasarkan nama merek
            ],
            [
                'id'   => $row['ID Merek'],
                'name' => $row['Nama Merek'],
            ]
        );

        return null;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
