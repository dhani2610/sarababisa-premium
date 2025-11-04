<?php

namespace App\Exports;

use App\Models\Izin;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IzinExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $start_date, $end_date, $filter_tipe;

    public function __construct($start_date, $end_date, $filter_tipe)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->filter_tipe = $filter_tipe;
    }

    public function collection()
    {
        $user = Auth::user();
        $query = Izin::with('user');

        if ($user->role !== 'Kepala Toko') {
            $query->where('user_id', $user->id);
        }

        if ($this->filter_tipe) {
            $query->where('tipe', $this->filter_tipe);
        }

        if ($this->start_date && $this->end_date) {
            $query->where(function ($q) {
                $q->whereBetween('tanggal', [$this->start_date, $this->end_date])
                  ->orWhereBetween('tanggal_mulai', [$this->start_date, $this->end_date])
                  ->orWhereBetween('tanggal_selesai', [$this->start_date, $this->end_date]);
            });
        }

        return $query->get()->map(function ($izin) {
            return [
                'Nama' => $izin->user->name,
                'Tipe' => ucfirst($izin->tipe),
                'Tanggal Dibuat' => $izin->tanggal,
                'Periode' => "{$izin->tanggal_mulai} s/d {$izin->tanggal_selesai}",
                'Keterangan' => $izin->keterangan,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama', 'Tipe', 'Tanggal Dibuat', 'Periode', 'Keterangan'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Nama
            'B' => 15, // Tipe
            'C' => 18, // Tanggal Dibuat
            'D' => 25, // Periode
            'E' => 50, // Keterangan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Terapkan border, wrap text, dan bold untuk heading
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');

        // Auto wrap text untuk semua kolom
        $sheet->getStyle('A:E')->getAlignment()->setWrapText(true);

        // Border untuk semua sel
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:E{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
