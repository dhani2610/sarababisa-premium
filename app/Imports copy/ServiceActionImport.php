<?php

namespace App\Imports;

use App\Models\ServiceAction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ServiceActionImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts
{
    public function model(array $row)
    {
        return new ServiceAction([
            'nama_tindakan'     => $row['Nama Tindakan'],
            'modal_sparepart'    => $row['Modal Sparepart'],
            'harga_toko'    => $row['Harga Pelanggan Toko'],
            'harga_pelanggan'    => $row['Harga Pelanggan Biasa'],
            'garansi'    => $row['Garansi'],
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'nama_tindakan';
    }
}
