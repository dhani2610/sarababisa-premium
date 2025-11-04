<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MasterAbsensi extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $filter_role_user_id;
    public $start_date;
    public $end_date;

    protected $updatesQueryString = ['search', 'filter_role_user_id', 'start_date', 'end_date'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->filter_role_user_id = request()->query('filter_role_user_id', null);
        $this->start_date = request()->query('start_date', null);
        $this->end_date = request()->query('end_date', null);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $query = Attendance::with('user')->latest();

        // Filter berdasarkan role
        if ($user->role !== 'Kepala Toko') {
            $query->where('user_id', $user->id);
        } elseif ($this->filter_role_user_id) {
            $query->where('user_id', $this->filter_role_user_id);
        }

        // 🔍 Filter pencarian
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                  ->orWhere('note', 'like', "%{$this->search}%");
            });
        }

        // 📅 Filter range tanggal
        if ($this->start_date && $this->end_date) {
            $query->whereBetween('tanggal', [
                Carbon::parse($this->start_date)->startOfDay(),
                Carbon::parse($this->end_date)->endOfDay()
            ]);
        }

        $users = $user->role === 'Kepala Toko'
            ? User::select('id','name')->whereIn('role',['Teknisi','Sales','Admin Toko'])->get()
            : User::where('id', $user->id)->select('id','name')->whereIn('role',['Teknisi','Sales','Admin Toko'])->get();

        $totalMasuk = Attendance::where('type', 'masuk')->count();
        $totalPulang = Attendance::where('type', 'pulang')->count();
        $totalAbsen = $totalMasuk + $totalPulang;

        $today = Carbon::today();
        $hariIniMasuk = Attendance::where('type', 'masuk')->whereDate('created_at', $today)->count();
        $hariIniPulang = Attendance::where('type', 'pulang')->whereDate('created_at', $today)->count();
        $hariIniTotal = $hariIniMasuk + $hariIniPulang;

        return view('livewire.master-absensi', [
            'attendances' => $query->paginate($this->paginate),
            'users' => $users,
            'totalMasuk' => $totalMasuk,
            'totalPulang' => $totalPulang,
            'totalAbsen' => $totalAbsen,
            'hariIniMasuk' => $hariIniMasuk,
            'hariIniPulang' => $hariIniPulang,
            'hariIniTotal' => $hariIniTotal,
            'count' => Attendance::count(),
        ]);
    }
}
