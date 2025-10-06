<?php

namespace App\Imports;

use App\Models\ModelSerie;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ModelSeriImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts
{
    public function model(array $row)
    {
        return new ModelSerie([
            'name'     => $row['Nama Model Seri'],
            'brands_id'     => $row['ID Merek'],
            'id_tipe_os'     => $row['ID TIPE OS'],
            'nominal_bonus'     => $row['ID TIPE OS'],
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'name';
    }
}
