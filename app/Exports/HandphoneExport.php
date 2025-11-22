<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HandphoneExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::where('cabang_id',getCabangId())->where('categories_id', '=', '1')->get();
    }

    public function map($product): array
    {
        return [
            $product->product_name,
            $product->brands_id,
            $product->model_series_id,
            $product->ram,
            $product->capacities_id,
            $product->warna,
            $product->kondisi,
            $product->product_code,
            $product->nomor_seri,
            $product->stok,
            $product->stok_minimal,
            $product->harga_modal,
            $product->harga_jual_toko,
            $product->harga_jual,
            $product->keterangan,
            $product->garansi,
            $product->garansi_imei,
            $product->ppn,
            $product->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Produk',
            'ID Merek',
            'ID Model Seri',
            'RAM',
            'ID Kapasitas',
            'Warna',
            'Kondisi',
            'Kode Produk',
            'Nomor Seri',
            'Stok',
            'Stok Minimal',
            'Harga Modal',
            'Harga Jual Toko',
            'Harga Jual Pelanggan',
            'Keterangan',
            'Garansi Produk (Hari)',
            'Garansi IMEI (Hari)',
            'PPN 11%',
            'Tgl Masuk',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
