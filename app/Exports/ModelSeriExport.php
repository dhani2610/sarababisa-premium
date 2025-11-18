<?php

namespace App\Exports;

use App\Models\ModelSerie;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ModelSeriExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ModelSerie::where('cabang_id',getCabangId())->with('brand')->get();
    }

    public function map($modelserie): array
    {
        return [
            $modelserie->id,
            $modelserie->name,
            $modelserie->brands_id,
            $modelserie->id_tipe_os,
            $modelserie->nominal_bonus,
        ];
    }

    public function headings(): array
    {
        return [
            'ID Model Seri',
            'Nama Model Seri',
            'ID Merek',
            'ID TIPE OS',
            'Nominal Bonus Interface',
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
