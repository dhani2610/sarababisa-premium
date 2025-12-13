<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class AksesorisImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan jika tidak ada nama produk
        if (empty($row['Nama Produk'])) {
            return null;
        }

        $cabangId = getCabangId();
        $namaAsli = $row['Nama Produk'];

        // ---------------------------------------------------------
        // 1. LOGIKA UPDATE (Prioritas Utama - Cek Cabang Sendiri)
        // ---------------------------------------------------------
        // Cari apakah Produk ini sudah ada di cabang ini?
        $existingProduct = Product::where('cabang_id', $cabangId)
                            ->where('product_name', $namaAsli)
                            ->first();

        if ($existingProduct) {
            // == KONDISI UPDATE ==
            // Data sudah ada di cabang ini, update detailnya (stok, harga, dll)
            $existingProduct->update([
                'categories_id'     => 3, // Tetap set default Aksesoris
                'category_name'     => "Aksesoris",
                'sub_categories_id' => $row['ID Sub Kategori'],
                'model_series_id'   => $row['ID Model Seri'],
                'product_code'      => $row['Kode Produk'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual_toko'   => $row['Harga Jual Toko'],
                'harga_jual'        => $row['Harga Jual Pelanggan'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk (Hari)'],
                'ppn'               => $row['PPN 11%'],
            ]);

            return null; // Selesai update, stop.
        }

        // ---------------------------------------------------------
        // 2. LOGIKA CREATE (Jika belum ada di cabang ini)
        // ---------------------------------------------------------

        $finalName = $namaAsli;
        $counter = 2;

        // Cek apakah nama produk ini sudah dipakai secara GLOBAL (di cabang lain)?
        // Jika ya, rename jadi "Casing HP (2)", dst.
        // while (Product::where('product_name', $finalName)->exists()) {
        //     $finalName = $namaAsli . '.';
        //     $counter++;
        // }

        // Simpan Data Baru
        return new Product([
            'product_name'      => $finalName, // Nama yang sudah aman (unik)
            'categories_id'     => 3,
            'category_name'     => "Aksesoris",
            'sub_categories_id' => $row['ID Sub Kategori'],
            'model_series_id'   => $row['ID Model Seri'],
            'product_code'      => $row['Kode Produk'],
            'stok'              => $row['Stok'],
            'stok_minimal'      => $row['Stok Minimal'],
            'harga_modal'       => $row['Harga Modal'],
            'harga_jual_toko'   => $row['Harga Jual Toko'],
            'harga_jual'        => $row['Harga Jual Pelanggan'],
            'keterangan'        => $row['Keterangan'],
            'garansi'           => $row['Garansi Produk (Hari)'],
            'ppn'               => $row['PPN 11%'],
            'cabang_id'         => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
