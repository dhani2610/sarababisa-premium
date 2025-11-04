<?php

namespace App\Exports;

use App\Models\Izin;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IzinExport implements FromCollection, WithHeadings
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
}
