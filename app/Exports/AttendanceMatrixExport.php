<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceMatrixExport implements FromView, WithStyles, ShouldAutoSize
{
    protected $bulan;

    public function __construct($bulan)
    {
        $this->bulan = $bulan;
    }

    public function view(): View
    {
        $start = Carbon::parse($this->bulan)->startOfMonth();
        $end = Carbon::parse($this->bulan)->endOfMonth();

        // ambil semua user yang punya absensi di bulan ini
        $users = User::whereHas('attendances', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal', [$start, $end]);
        })->get();

        // ambil absensi dalam rentang tanggal
        $attendances = Attendance::with('user')
            ->whereBetween('tanggal', [$start, $end])
            ->get()
            ->groupBy(['user_id', 'tanggal']);

        $daysInMonth = [];
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $daysInMonth[] = $d->copy();
        }

        return view('exports.attendance-matrix', [
            'users' => $users,
            'attendances' => $attendances,
            'days' => $daysInMonth,
            'bulan' => $start,
        ]);
    }

    /**
     * Tambahkan border dan wraptext pada seluruh sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Ambil range seluruh area terisi
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $highestColumn . $highestRow;

        // Border dan wrap text
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'alignment' => [
                'wrapText' => true,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Bold header (baris pertama)
        $sheet->getStyle('A1:' . $highestColumn . '2')->getFont()->setBold(true);

        return [];
    }
}
