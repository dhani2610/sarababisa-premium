<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MasterIzin extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $query = Izin::with('user')->latest();

        if ($user->role !== 'Kepala Toko') {
            $query->where('id_user', $user->id);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        $users = $user->role === 'Kepala Toko'
            ? User::whereIn('role', ['Teknisi', 'Sales', 'Admin Toko'])
                ->select('id', 'name')
                ->get()
            : User::where('id', $user->id)
                ->select('id', 'name')
                ->get();

        return view('livewire.master-izin', [
            'count' => Izin::count(),
            'izins' => $query->paginate($this->paginate),
            'users' => $users,
        ]);
    }
}
