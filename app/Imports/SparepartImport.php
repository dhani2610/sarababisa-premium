<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class SparepartImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan baris kosong
        if (empty($row['Nama Produk'])) {
            return null;
        }

        // Update jika sudah ada, buat baru jika belum ada
        Product::updateOrCreate(
            [
                'product_name' => $row['Nama Produk'], // kunci pencarian
            ],
            [
                'categories_id'     => 2, // default kategori Sparepart
                'category_name'     => 'Sparepart',
                'sub_categories_id' => $row['ID Sub Kategori'],
                'model_series_id'   => $row['ID Model Seri'],
                'product_code'      => $row['Kode Produk'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual_toko'        => $row['Harga Jual Toko'],
                'harga_jual'        => $row['Harga Jual Pelanggan'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk (Hari)'],
                'ppn'               => $row['PPN 11%'],
            ]
        );

        return null;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
