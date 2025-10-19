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
        // Lakukan update jika nomor_seri & product_name sudah ada, kalau tidak create baru
        Product::updateOrCreate(
            [
                'nomor_seri'   => $row['Nomor Seri'],
                'product_name' => $row['Nama Produk'],
            ],
            [
                'product_code'      => $row['Kode Produk'],
                'sub_categories_id' => $row['ID Sub Kategori'],
                'category_name'     => $row['Nama Sub Kategori'],
                'stok'              => $row['Stok'],
                'stok_minimal'      => $row['Stok Minimal'],
                'harga_modal'       => $row['Harga Modal'],
                'harga_jual'        => $row['Harga Jual'],
                'keterangan'        => $row['Keterangan'],
                'garansi'           => $row['Garansi Produk'],
                'garansi_imei'      => $row['Garansi IMEI'],
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
