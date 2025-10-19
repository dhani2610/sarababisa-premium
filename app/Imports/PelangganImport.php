<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class PelangganImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan jika tidak ada nama pelanggan atau nomor HP
        if (empty($row['Nama Pelanggan']) && empty($row['Nomor HP'])) {
            return null;
        }

        // Update jika sudah ada (berdasarkan Nomor HP), buat baru jika belum ada
        Customer::updateOrCreate(
            [
                'nomor_hp' => $row['Nomor HP'], // pencarian berdasarkan nomor HP
            ],
            [
                'nama'     => $row['Nama Pelanggan'],
                'kategori' => $row['Kategori Pelanggan'],
                'nomor_hp' => $row['Nomor HP'],
                'alamat'   => $row['Alamat'],
            ]
        );

        return null;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
