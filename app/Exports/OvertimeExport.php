<?php

namespace App\Exports;

use App\Models\Overtime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class OvertimeExport implements FromCollection, WithHeadings
{
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        $query = Overtime::where('cabang_id',getCabangId())->with('user')
            ->when(
                $this->start && $this->end,
                fn($q) =>
                $q->whereBetween('tanggal', [$this->start, $this->end])
            );

        if (Auth::user()->role !== 'Kepala Toko') {
            $query->where('id_user', Auth::id());
        }

        return $query->get()->map(function ($o) {
            return [
                'Nama' => $o->user->name ?? '-',
                'Tanggal' => $o->tanggal,
                'Waktu Mulai' => $o->waktu_start,
                'Waktu Selesai' => $o->waktu_end,
                'Nominal' => $o->nominal_overtime,
                'Keterangan' => $o->keterangan,
            ];
        });
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');
        $sheet->getDefaultColumnDimension()->setAutoSize(true);

        return [];
    }


    public function headings(): array
    {
        return ['Nama', 'Tanggal', 'Waktu Mulai', 'Waktu Selesai', 'Nominal', 'Keterangan'];
    }
}
