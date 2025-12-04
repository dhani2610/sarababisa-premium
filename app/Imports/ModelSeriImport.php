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

        // == KONDISI CREATE (BARU) ==
        // Data belum ada di cabang ini, kita buat baru.
        // TAPI cek dulu, apakah nama ini sudah dipakai cabang lain?

        $finalName = $namaAsli;
        $counter = 2;

        // Loop: Jika nama sudah ada di database (milik cabang manapun), tambah angka (2), (3)...
        while (ModelSerie::where('name', $finalName)->exists()) {
            $finalName = $namaAsli . ' (' . $counter . ')';
            $counter++;
        }

        // Simpan Data Baru dengan nama yang sudah aman
        return new ModelSerie([
            'name'           => $finalName, // Nama unik
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
