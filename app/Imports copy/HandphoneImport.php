<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class HandphoneImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts
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
            'categories_id' => 1, // Nilai default untuk categories_id
            'category_name'     => "Handphone",
            'product_name'     => $row['Nama Produk'],
            'brands_id'    => $row['ID Merek'],
            'model_series_id'    => $row['ID Model Seri'],
            'ram'    => $row['RAM'],
            'capacities_id'    => $row['ID Kapasitas'],
            'warna'    => $row['Warna'],
            'kondisi'    => $row['Kondisi'],
            'product_code'    => $row['Kode Produk'],
            'nomor_seri'    => $row['Nomor Seri'],
            'stok'    => $row['Stok'],
            'stok_minimal'    => $row['Stok Minimal'],
            'harga_modal'    => $row['Harga Modal'],
            'harga_jual'    => $row['Harga Jual'],
            'keterangan'    => $row['Keterangan'],
            'garansi'    => $row['Garansi Produk (Hari)'],
            'garansi_imei'    => $row['Garansi IMEI (Hari)'],
            'ppn'    => $row['PPN 11%'],
            'created_at'    => $row['Tgl Masuk'],
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return ['nomor_seri'];
    }
}
