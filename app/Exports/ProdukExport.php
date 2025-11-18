<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProdukExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::where('cabang_id',getCabangId())->get();
    }

    public function map($product): array
    {
        return [
            $product->product_name,
            $product->product_code,
            $product->nomor_seri,
            $product->sub_categories_id,
            $product->category_name,
            $product->stok,
            $product->stok_minimal,
            $product->harga_modal,
            $product->harga_jual,
            $product->harga_jual_toko,
            $product->keterangan,
            $product->garansi,
            $product->garansi_imei,
            $product->ppn,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Produk',
            'Kode Produk',
            'Nomor Seri',
            'ID Sub Kategori',
            'Nama Sub Kategori',
            'Stok',
            'Stok Minimal',
            'Harga Modal',
            'Harga Jual Toko',
            'Harga Jual Pelanggan',
            'Keterangan',
            'Garansi Produk',
            'Garansi IMEI',
            'PPN 11%',
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
