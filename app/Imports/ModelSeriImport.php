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

        $cabangId = getCabangId();
        $namaAsli = $row['Nama Model Seri'];

        // 1. CEK DATA EKSISTING DI CABANG INI
        // Cari apakah model ini sudah ada di cabang yang sedang login?
        $existingModel = ModelSerie::where('cabang_id', $cabangId)
                            ->where('name', $namaAsli)
                            ->first();

        if ($existingModel) {
            // == KONDISI UPDATE ==
            // Data sudah ada di cabang ini, update detailnya saja
            $existingModel->update([
                'brands_id'      => $row['ID Merek'],
                'id_tipe_os'     => $row['ID TIPE OS'],
                'nominal_bonus'  => $row['Nominal Bonus'] ?? 0,
            ]);

            return null; // Return null karena sudah di-handle update
        }

        // Simpan Data Baru dengan nama yang sudah aman
        return new ModelSerie([
            'name'           => $row['Nama Model Seri'], // Nama unik
            'brands_id'      => $row['ID Merek'],
            'id_tipe_os'     => $row['ID TIPE OS'],
            'nominal_bonus'  => $row['Nominal Bonus'] ?? 0,
            'cabang_id'      => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
