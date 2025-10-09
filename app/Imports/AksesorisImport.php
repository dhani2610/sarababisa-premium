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

        Product::updateOrCreate(
            [
                'product_name' => $row['Nama Produk'], // kunci unik untuk pencarian
            ],
            [
                'categories_id'     => 3, // default kategori Aksesoris
                'category_name'     => "Aksesoris",
                'sub_categories_id' => $row['ID Sub Kategori'],
                'model_series_id'   => $row['ID Model Seri'],
                'product_code'      => $row['Kode Produk'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual'        => $row['Harga Jual'],
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
