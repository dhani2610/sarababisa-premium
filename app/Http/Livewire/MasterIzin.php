<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IzinExport;

class MasterIzin extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $filter_tipe;
    public $start_date;
    public $end_date;

    protected $updatesQueryString = ['search', 'filter_tipe', 'start_date', 'end_date'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->filter_tipe = request()->query('filter_tipe', null);
        $this->start_date = request()->query('start_date', null);
        $this->end_date = request()->query('end_date', null);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        return Excel::download(
            new IzinExport($this->start_date, $this->end_date, $this->filter_tipe),
            'izin-export-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function render()
    {
        $user = Auth::user();
        $query = Izin::where('cabang_id',getCabangId())->with('user')->latest();

        // Filter user jika bukan kepala toko
        if ($user->role !== 'Kepala Toko' && $user->role !== 'Admin Toko') {

            $query->where('user_id', $user->id);
        }

        // Filter pencarian
        if ($this->search) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            );
        }

        // Filter tipe izin
        if ($this->filter_tipe) {
            $query->where('tipe', $this->filter_tipe);
        }

        // 🔹 Filter range tanggal
        if ($this->start_date && $this->end_date) {
            $query->where(function ($q) {
                $q->whereBetween('tanggal', [$this->start_date, $this->end_date])
                  ->orWhereBetween('tanggal_mulai', [$this->start_date, $this->end_date])
                  ->orWhereBetween('tanggal_selesai', [$this->start_date, $this->end_date]);
            });
        }

        // === Statistik ===
        $today = Carbon::today();

        $statQuery = Izin::query();
        if ($user->role !== 'Kepala Toko' && $user->role !== 'Admin Toko') {
            $statQuery->where('user_id', $user->id);
        }
        $statQuery->where('cabang_id',getCabangId());

        $stats = [
            'hariIni' => [
                'izin' => (clone $statQuery)->whereDate('tanggal', $today)->where('tipe', 'izin')->count(),
                'sakit' => (clone $statQuery)->whereDate('tanggal', $today)->where('tipe', 'sakit')->count(),
                'alfa'  => (clone $statQuery)->whereDate('tanggal', $today)->where('tipe', 'alfa')->count(),
                'total' => (clone $statQuery)->whereDate('tanggal', $today)->count(),
            ]
        ];

        // $users = $user->role === 'Kepala Toko'
        //     ? User::where('cabang_id',getCabangId())->whereIn('role', ['Teknisi', 'Sales', 'Admin Toko'])->select('id', 'name')->get()
        //     : User::where('cabang_id',getCabangId())->where('id', $user->id)->select('id', 'name')->get();

        if ($user->role == 'Kepala Toko') {
            $users = User::forCabang()->whereIn('role', ['Teknisi', 'Sales','Admin Toko'])->select('id', 'name')->get();
        }else if($user->role == 'Admin Toko'){
            $users = User::forCabang()->whereIn('role', ['Teknisi', 'Sales','Admin Toko'])->select('id', 'name')->get();
        }else{
            $users = User::forCabang()->where('id', $user->id)->select('id', 'name')->get();
        }
        return view('livewire.master-izin', [
            'izins' => $query->paginate($this->paginate),
            'users' => $users,
            'count' => Izin::where('cabang_id',getCabangId())->count(),
            'stats' => $stats,
        ]);
    }
}
