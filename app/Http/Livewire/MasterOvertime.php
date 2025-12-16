<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Overtime;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OvertimeExport;

class MasterOvertime extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $start_date;
    public $end_date;

    protected $updatesQueryString = ['search', 'start_date', 'end_date'];


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        return Excel::download(
            new OvertimeExport($this->start_date, $this->end_date),
            'overtime-export-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function render()
    {
        $user = Auth::user();

        $query = Overtime::with('user')->latest();

        if ($user->role !== 'Kepala Toko' && $user->role !== 'Admin Toko') {
            $query->where('id_user', $user->id);
        }

        if ($this->search) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            );
        }

        if ($this->start_date && $this->end_date) {
            $query->whereBetween('tanggal', [$this->start_date, $this->end_date]);
        }

        // Statistik
        $today = Carbon::today();
        $statQuery = Overtime::query();
        if ($user->role !== 'Kepala Toko' && $user->role !== 'Admin Toko') {

            $statQuery->where('id_user', $user->id);
        }
        $statQuery->where('cabang_id',getCabangId());

        $stats = [
            'hariIni' => [
                'total' => (clone $statQuery)->whereDate('tanggal', $today)->count(),
                'total_nominal' => (clone $statQuery)->whereDate('tanggal', $today)->sum('nominal_overtime'),
            ],
            'bulanIni' => [
                'total' => (clone $statQuery)->whereMonth('tanggal', now()->month)->count(),
                'total_nominal' => (clone $statQuery)->whereMonth('tanggal', now()->month)->sum('nominal_overtime'),
            ],
        ];

        return view('livewire.master-overtime', [
            'overtimes' => $query->paginate($this->paginate),
            'stats' => $stats,
        ]);
    }
}
