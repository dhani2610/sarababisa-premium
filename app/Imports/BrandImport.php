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
        $namaAsli = $row['Nama Merek'];

        // 1. CEK DATA EKSISTING DI CABANG INI
        // Cari apakah Brand ini sudah ada di cabang yang sedang login?
        $existingBrand = Brand::where('cabang_id', $cabangId)
                            ->where('name', $namaAsli)
                            ->first();

        if ($existingBrand) {
            // == KONDISI UPDATE ==
            // Data sudah ada di cabang ini, update datanya.
            // (Catatan: Hati-hati mengupdate 'id' jika itu Primary Key)
            $existingBrand->update([
                'id' => $row['ID Merek'],
                // Nama tidak perlu diupdate karena sudah pasti sama
            ]);

            return null;
        }

        // == KONDISI CREATE (BARU) ==
        // Data belum ada di cabang ini.
        // Cek apakah nama ini sudah dipakai cabang lain?

        $finalName = $namaAsli;
        $counter = 2;

        // Loop: Jika nama sudah ada di database (milik cabang manapun), tambah angka (2), (3)...
        while (Brand::where('name', $finalName)->exists()) {
            $finalName = $namaAsli . ' (' . $counter . ')';
            $counter++;
        }

        // Simpan Data Baru dengan nama yang sudah aman
        return new Brand([
            'id'          => $row['ID Merek'],
            'name'        => $finalName, // Nama unik (misal: "Samsung (2)")
            'cabang_id'   => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
