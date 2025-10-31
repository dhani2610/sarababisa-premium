<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MasterIzin extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $filter_bulan;
    public $filter_tipe;

    protected $updatesQueryString = ['search', 'filter_bulan', 'filter_tipe'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->filter_bulan = request()->query('filter_bulan', Carbon::now()->format('Y-m'));
        $this->filter_tipe = request()->query('filter_tipe', null);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $query = Izin::with('user')->latest();

        // Filter user jika bukan kepala toko
        if ($user->role !== 'Kepala Toko') {
            $query->where('user_id', $user->id);
        }

        // Filter pencarian
        if ($this->search) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            );
        }

        // Filter bulan (YYYY-MM)
        if ($this->filter_bulan) {
            $query->whereMonth('tanggal', Carbon::parse($this->filter_bulan)->month)
                  ->whereYear('tanggal', Carbon::parse($this->filter_bulan)->year);
        }

        // Filter tipe
        if ($this->filter_tipe) {
            $query->where('tipe', $this->filter_tipe);
        }

        // --- Statistik ---
        $today = Carbon::today();
        $bulanIni = Carbon::parse($this->filter_bulan ?? Carbon::now());

        $stats = [
            'hariIni' => [
                'izin' => Izin::whereDate('tanggal', $today)->where('tipe', 'izin')->count(),
                'sakit' => Izin::whereDate('tanggal', $today)->where('tipe', 'sakit')->count(),
                'alfa'  => Izin::whereDate('tanggal', $today)->where('tipe', 'alfa')->count(),
                'total' => Izin::whereDate('tanggal', $today)->count(),
            ],
            'bulanIni' => [
                'izin' => Izin::whereMonth('tanggal', $bulanIni->month)->whereYear('tanggal', $bulanIni->year)->where('tipe', 'izin')->count(),
                'sakit' => Izin::whereMonth('tanggal', $bulanIni->month)->whereYear('tanggal', $bulanIni->year)->where('tipe', 'sakit')->count(),
                'alfa'  => Izin::whereMonth('tanggal', $bulanIni->month)->whereYear('tanggal', $bulanIni->year)->where('tipe', 'alfa')->count(),
                'total' => Izin::whereMonth('tanggal', $bulanIni->month)->whereYear('tanggal', $bulanIni->year)->count(),
            ],
        ];

        $users = $user->role === 'Kepala Toko'
            ? User::whereIn('role', ['Teknisi', 'Sales', 'Admin Toko'])->select('id', 'name')->get()
            : User::where('id', $user->id)->select('id', 'name')->get();

        return view('livewire.master-izin', [
            'izins' => $query->paginate($this->paginate),
            'users' => $users,
            'count' => Izin::count(),
            'stats' => $stats,
        ]);
    }
}
