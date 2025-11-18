<?php

namespace App\Exports;

use App\Models\ServiceAction;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TindakanServisExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ServiceAction::where('cabang_id',getCabangId())->get();
    }

    public function map($serviceaction): array
    {
        return [
            $serviceaction->nama_tindakan,
            $serviceaction->modal_sparepart,
            $serviceaction->harga_toko,
            $serviceaction->harga_pelanggan,
            $serviceaction->garansi
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Tindakan',
            'Modal Sparepart',
            'Harga Pelanggan Toko',
            'Harga Pelanggan Biasa',
            'Garansi'
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
