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
        $product = Product::where('product_name', $row['Nama Produk'])->first();

        if ($product) {
            $product->stok = $row['Stok'];
            $product->save();

            return null;
        }
        
        return new Product([
            'categories_id' => 4, // Nilai default untuk categories_id
            'category_name'     => "Sparepart",
            'sub_categories_id'    => $row['ID Sub Kategori'],
            'product_name'     => $row['Nama Produk'],
            'product_code'    => $row['Kode Produk'],
            'stok'    => $row['Stok'],
            'stok_minimal'    => $row['Stok Minimal'],
            'harga_modal'    => $row['Harga Modal'],
            'harga_jual'    => $row['Harga Jual'],
            'keterangan'    => $row['Keterangan'],
            'garansi'    => $row['Garansi Produk (Hari)'],
            'ppn'    => $row['PPN 11%'],
        ]);
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
