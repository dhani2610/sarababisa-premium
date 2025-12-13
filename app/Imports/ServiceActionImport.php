<?php

namespace App\Imports;

use App\Models\ServiceAction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

// Hapus 'WithUpserts' karena kita akan handle logika uniknya sendiri
class ServiceActionImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        $cabangId = getCabangId();
        $namaTindakanAsli = $row['Nama Tindakan'];

        // 1. CEK DATA EKSISTING DI CABANG INI
        // Kita cari apakah "Ganti LCD" (nama asli) sudah ada di cabang ini?
        // Atau mungkin "Ganti LCD (2)" yang milik cabang ini?

        // Logika: Kita coba update dulu berdasarkan nama persis yg ada di Excel + Cabang ID
        $existingService = ServiceAction::where('cabang_id', $cabangId)
                            ->where('nama_tindakan', $namaTindakanAsli)
                            ->first();

        if ($existingService) {
            // == KONDISI UPDATE ==
            // Datanya sudah ada di cabang ini, langsung update saja.
            $existingService->update([
                'modal_sparepart'   => $row['Modal Sparepart'],
                'harga_toko'        => $row['Harga Pelanggan Toko'],
                'harga_pelanggan'   => $row['Harga Pelanggan Biasa'],
                'garansi'           => $row['Garansi'],
            ]);

            return $existingService;
        }

        // // == KONDISI CREATE (BARU) ==
        // // Data belum ada di cabang ini. Kita harus buat baru.
        // // TAPI, kita harus cek apakah nama ini sudah dipake secara GLOBAL (di cabang lain)?

        // $finalName = $namaTindakanAsli;
        // $counter = 2;

        // // Loop: Selama nama tersebut sudah ada di tabel (milik siapapun/cabang manapun), tambah angka
        // while (ServiceAction::where('nama_tindakan', $finalName)->exists()) {
        //     $finalName = $namaTindakanAsli . '.';
        //     $counter++;
        // }

        // Setelah loop selesai, $finalName pasti unik (misal: "Ganti LCD (2)")
        return new ServiceAction([
            'nama_tindakan'     => $namaTindakanAsli, // Nama yang sudah aman
            'cabang_id'         => $cabangId,
            'modal_sparepart'   => $row['Modal Sparepart'],
            'harga_toko'        => $row['Harga Pelanggan Toko'],
            'harga_pelanggan'   => $row['Harga Pelanggan Biasa'],
            'garansi'           => $row['Garansi'],
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    // Hapus function uniqueBy() karena kita tidak pakai WithUpserts lagi
}
