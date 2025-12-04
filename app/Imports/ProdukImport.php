<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ProdukImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        $cabangId = getCabangId();
        $snAsli = $row['Nomor Seri'];
        $namaAsli = $row['Nama Produk'];

        // Cek apakah produk ini punya Serial Number (Nomor Seri)
        $hasSn = !empty($snAsli) && $snAsli !== '-';

        // ---------------------------------------------------------
        // 1. LOGIKA UPDATE (Cek Cabang Sendiri)
        // ---------------------------------------------------------
        $query = Product::where('cabang_id', $cabangId);

        if ($hasSn) {
            // Jika ada SN, cari berdasarkan SN
            $query->where('nomor_seri', $snAsli);
        } else {
            // Jika tidak ada SN, cari berdasarkan Nama Produk
            $query->where('product_name', $namaAsli);
        }

        $existingProduct = $query->first();

        if ($existingProduct) {
            // == KONDISI UPDATE ==
            $existingProduct->update([
                // Update data-data pendukung
                'product_code'      => $row['Kode Produk'],
                'sub_categories_id' => $row['ID Sub Kategori'],
                'category_name'     => $row['Nama Sub Kategori'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual_toko'   => $row['Harga Jual Toko'],
                'harga_jual'        => $row['Harga Jual Pelanggan'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk'],
                'garansi_imei'      => $row['Garansi IMEI'],
                'ppn'               => $row['PPN 11%'],
                // Nama & SN tidak diupdate karena sudah jadi kunci pencarian
            ]);

            return null;
        }

        // ---------------------------------------------------------
        // 2. LOGIKA CREATE (Cek Global & Rename)
        // ---------------------------------------------------------

        $finalSn = $snAsli;
        $finalName = $namaAsli;
        $counter = 2;

        if ($hasSn) {
            // KASUS BARANG BERSERI (HP):
            // Cek apakah SN ini sudah dipakai di cabang lain? Rename SN-nya.
            while (Product::where('nomor_seri', $finalSn)->exists()) {
                $finalSn = $snAsli . ' (' . $counter . ')';
                $counter++;
            }
        } else {
            // KASUS BARANG NON-SERI (Aksesoris):
            // Cek apakah Nama Produk sudah dipakai di cabang lain? Rename Namanya.
            while (Product::where('product_name', $finalName)->exists()) {
                $finalName = $namaAsli . ' (' . $counter . ')';
                $counter++;
            }
        }

        // Simpan Data Baru
        return new Product([
            'nomor_seri'        => $finalSn,   // SN yang sudah aman/kosong
            'product_name'      => $finalName, // Nama yang sudah aman
            'product_code'      => $row['Kode Produk'],
            'sub_categories_id' => $row['ID Sub Kategori'],
            'category_name'     => $row['Nama Sub Kategori'],
            'stok'              => $row['Stok'],
            'stok_minimal'      => $row['Stok Minimal'],
            'harga_modal'       => $row['Harga Modal'],
            'harga_jual_toko'   => $row['Harga Jual Toko'],
            'harga_jual'        => $row['Harga Jual Pelanggan'],
            'keterangan'        => $row['Keterangan'],
            'garansi'           => $row['Garansi Produk'],
            'garansi_imei'      => $row['Garansi IMEI'],
            'ppn'               => $row['PPN 11%'],
            'cabang_id'         => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
