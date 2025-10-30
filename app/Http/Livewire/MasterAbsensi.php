<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MasterAbsensi extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $filter_role_user_id; // optional filter when kepala toko wants to filter by user

    protected $updatesQueryString = ['search', 'filter_role_user_id'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->filter_role_user_id = request()->query('filter_role_user_id', null);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $query = Attendance::with('user')->latest();

        if ($user->role !== 'Kepala Toko') {
            $query->where('user_id', $user->id);
        } else {
            // kepala toko can filter by user
            if ($this->filter_role_user_id) {
                $query->where('user_id', $this->filter_role_user_id);
            }
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                  ->orWhere('note', 'like', "%{$this->search}%");
            });
        }

        $users = $user->role === 'Kepala Toko'
            ? User::select('id','name')->get()
            : User::where('id', $user->id)->select('id','name')->get();

        return view('livewire.master-absensi', [
            'attendances' => $query->paginate($this->paginate),
            'users' => $users,
            'count' => Attendance::count(),
        ]);
    }
}
