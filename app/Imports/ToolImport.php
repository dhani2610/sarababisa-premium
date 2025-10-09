<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ToolImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts
{
    public function model(array $row)
    {
        // Gunakan updateOrCreate agar otomatis update jika ada, atau create jika belum ada
        Product::updateOrCreate(
            [
                'product_name' => $row['Nama Produk'], // kunci unik
            ],
            [
                'categories_id'     => 4, // default kategori
                'category_name'     => "Sparepart",
                'sub_categories_id' => $row['ID Sub Kategori'],
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

    public function uniqueBy()
    {
        return ['product_name'];
    }
}
