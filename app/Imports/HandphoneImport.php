<?php

namespace App\Imports;

use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class HandphoneImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan jika tidak ada nama produk DAN tidak ada nomor seri
        if (empty($row['Nama Produk']) && empty($row['Nomor Seri'])) {
            return null;
        }

        $cabangId = getCabangId();
        $snAsli = $row['Nomor Seri'];

        // ---------------------------------------------------------
        // 1. LOGIKA UPDATE (Prioritas Utama - Cek Cabang Sendiri)
        // ---------------------------------------------------------
        // Cari apakah HP dengan Nomor Seri ini sudah ada di cabang ini?
        $existingHandphone = Product::where('cabang_id', $cabangId)
                                ->where('nomor_seri', $snAsli)
                                ->first();

        if ($existingHandphone) {
            // == KONDISI UPDATE ==
            // Data sudah ada di cabang ini, update detailnya.
            $existingHandphone->update([
                'categories_id'     => 1, // Default kategori Handphone
                'category_name'     => 'Handphone',
                'product_name'      => $row['Nama Produk'],
                'brands_id'         => $row['ID Merek'],
                'model_series_id'   => $row['ID Model Seri'],
                'ram'               => $row['RAM'],
                'capacities_id'     => $row['ID Kapasitas'],
                'warna'             => $row['Warna'],
                'kondisi'           => $row['Kondisi'],
                'product_code'      => $row['Kode Produk'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual_toko'   => $row['Harga Jual Toko'],
                'harga_jual'        => $row['Harga Jual Pelanggan'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk (Hari)'],
                'garansi_imei'      => $row['Garansi IMEI (Hari)'],
                'ppn'               => $row['PPN 11%'],
                'created_at'        => !empty($row['Tgl Masuk'])
                                        ? Carbon::parse($row['Tgl Masuk'])
                                        : now(),
            ]);

            return null; // Stop, data sudah terupdate
        }

        // ---------------------------------------------------------
        // 2. LOGIKA CREATE (Jika belum ada di cabang ini)
        // ---------------------------------------------------------

        $finalSn = $snAsli;
        $counter = 2;

        // Cek apakah Nomor Seri ini sudah dipakai secara GLOBAL (di cabang lain)?
        // Jika ya, rename SN-nya agar bisa masuk database (unik).
        while (Product::where('nomor_seri', $finalSn)->exists()) {
            $finalSn = $snAsli . ' (' . $counter . ')';
            $counter++;
        }

        // Simpan Data Baru
        return new Product([
            'nomor_seri'        => $finalSn, // SN yang sudah aman
            'categories_id'     => 1,
            'category_name'     => 'Handphone',
            'product_name'      => $row['Nama Produk'],
            'brands_id'         => $row['ID Merek'],
            'model_series_id'   => $row['ID Model Seri'],
            'ram'               => $row['RAM'],
            'capacities_id'     => $row['ID Kapasitas'],
            'warna'             => $row['Warna'],
            'kondisi'           => $row['Kondisi'],
            'product_code'      => $row['Kode Produk'],
            'stok'              => $row['Stok'],
            'stok_minimal'      => $row['Stok Minimal'],
            'harga_modal'       => $row['Harga Modal'],
            'harga_jual_toko'   => $row['Harga Jual Toko'],
            'harga_jual'        => $row['Harga Jual Pelanggan'],
            'keterangan'        => $row['Keterangan'],
            'garansi'           => $row['Garansi Produk (Hari)'],
            'garansi_imei'      => $row['Garansi IMEI (Hari)'],
            'ppn'               => $row['PPN 11%'],
            'created_at'        => !empty($row['Tgl Masuk'])
                                    ? Carbon::parse($row['Tgl Masuk'])
                                    : now(),
            'cabang_id'         => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
