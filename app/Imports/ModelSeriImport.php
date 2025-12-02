<?php

namespace App\Imports;

use App\Models\ModelSerie;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ModelSeriImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan jika tidak ada nama model seri
        if (empty($row['Nama Model Seri'])) {
            return null;
        }

        // Update jika sudah ada, buat baru jika belum ada
        ModelSerie::updateOrCreate(
            [
                'name' => $row['Nama Model Seri'], // pencarian berdasarkan nama model seri
            ],
            [
                'brands_id'      => $row['ID Merek'],
                'id_tipe_os'     => $row['ID TIPE OS'],
                'nominal_bonus'  => $row['Nominal Bonus'] ?? 0, // fallback jika kolom bonus belum ada
                'cabang_id'   => getCabangId(),
            ]
        );

        return null;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
