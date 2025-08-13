<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ProdukImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts
{
    // public function model(array $row)
    // {
    //     dd($row);
    //     return new Product([
    //         'product_name'     => $row['Nama Produk'],
    //         'product_code'    => $row['Kode Produk'],
    //         'nomor_seri'    => $row['Nomor Seri'],
    //         'sub_categories_id'    => $row['ID Sub Kategori'],
    //         'category_name'    => $row['Nama Sub Kategori'],
    //         'stok'    => $row['Stok'],
    //         'stok_minimal'    => $row['Stok Minimal'],
    //         'harga_modal'    => $row['Harga Modal'],
    //         'harga_jual'    => $row['Harga Jual'],
    //         'keterangan'    => $row['Keterangan'],
    //         'garansi'    => $row['Garansi Produk'],
    //         'garansi_imei'    => $row['Garansi IMEI'],
    //         'ppn'    => $row['PPN 11%'],
    //     ]);
    // }

    public function model(array $row)
    {
        $product = Product::where('product_name', $row['Nama Produk'])->first();

        if ($product) {
            $product->stok = $row['Stok'];
            $product->save();

            return null;
        }

        // Jika belum ada → insert data baru
        return new Product([
            'product_name'     => $row['Nama Produk'],
            'product_code'     => $row['Kode Produk'],
            'nomor_seri'       => $row['Nomor Seri'],
            'sub_categories_id'=> $row['ID Sub Kategori'],
            'category_name'    => $row['Nama Sub Kategori'],
            'stok'             => $row['Stok'],
            'stok_minimal'     => $row['Stok Minimal'],
            'harga_modal'      => $row['Harga Modal'],
            'harga_jual'       => $row['Harga Jual'],
            'keterangan'       => $row['Keterangan'],
            'garansi'          => $row['Garansi Produk'],
            'garansi_imei'     => $row['Garansi IMEI'],
            'ppn'              => $row['PPN 11%'],
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return ['nomor_seri', 'product_name'];
    }
}
