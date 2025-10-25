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
        // Abaikan jika tidak ada nama produk atau nomor seri
        if (empty($row['Nama Produk']) && empty($row['Nomor Seri'])) {
            return null;
        }

        Product::updateOrCreate(
            [
                // Cari berdasarkan nomor seri (unik)
                'nomor_seri' => $row['Nomor Seri'],
            ],
            [
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
                'harga_jual_toko'        => $row['Harga Jual Toko'],
                'harga_jual'        => $row['Harga Jual Pelanggan'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk (Hari)'],
                'garansi_imei'      => $row['Garansi IMEI (Hari)'],
                'ppn'               => $row['PPN 11%'],
                'created_at'        => !empty($row['Tgl Masuk'])
                    ? Carbon::parse($row['Tgl Masuk'])
                    : now(),
            ]
        );

        return null;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
