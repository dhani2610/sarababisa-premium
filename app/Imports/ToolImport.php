<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ToolImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan jika nama produk kosong
        if (empty($row['Nama Produk'])) {
            return null;
        }

        $cabangId = getCabangId();
        $namaAsli = $row['Nama Produk'];

        // ---------------------------------------------------------
        // 1. LOGIKA UPDATE (Prioritas Utama - Cek Cabang Sendiri)
        // ---------------------------------------------------------
        // Cari apakah Alat ini sudah ada di cabang ini?
        $existingProduct = Product::where('cabang_id', $cabangId)
                            ->where('product_name', $namaAsli)
                            ->first();

        if ($existingProduct) {
            // == KONDISI UPDATE ==
            // Data sudah ada di cabang ini, update detailnya
            $existingProduct->update([
                'categories_id'     => 4, // ID Kategori untuk Tools
                'category_name'     => "Sparepart", // Sesuaikan jika ingin diganti "Alat" atau tetap "Sparepart"
                'sub_categories_id' => $row['ID Sub Kategori'],
                'product_code'      => $row['Kode Produk'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual_toko'   => $row['Harga Jual Toko'],
                'harga_jual'        => $row['Harga Jual Pelanggan'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk (Hari)'],
                'ppn'               => $row['PPN 11%'],
                // Nama tidak diupdate agar tidak error duplikat
            ]);

            return null; // Stop, jangan lanjut create
        }

        // ---------------------------------------------------------
        // 2. LOGIKA CREATE (Jika belum ada di cabang ini)
        // ---------------------------------------------------------

        // $finalName = $namaAsli;
        // $counter = 2;

        // // Cek apakah nama alat ini sudah dipakai secara GLOBAL (di cabang lain)?
        // // Jika ya, rename jadi "Obeng (2)", dst.
        // while (Product::where('product_name', $finalName)->exists()) {
        //     $finalName = $namaAsli . '.';
        //     $counter++;
        // }

        // Simpan Data Baru
        return new Product([
            'product_name'      => $namaAsli, // Nama yang sudah aman
            'categories_id'     => 4,
            'category_name'     => "Sparepart",
            'sub_categories_id' => $row['ID Sub Kategori'],
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
