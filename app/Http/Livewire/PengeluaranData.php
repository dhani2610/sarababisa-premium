<?php

namespace App\Http\Livewire;

use App\Models\Expense;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PengeluaranData extends Component
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
        $users = User::forCabang()->get();
        if (auth()->user()->role == 'Kepala Toko' || auth()->user()->role == 'Admin Toko') {
            $expenses_count = Expense::where('cabang_id',getCabangId())->get()->count();
        }else{
            $expenses_count = Expense::where('cabang_id',getCabangId())->where('users_id',auth()->user()->id)->get()->count();
        }
        return view('livewire.pengeluaran-data', [
            'users' => $users,
            'expenses_count' => $expenses_count,
            'expenses' => $this->search === null ?
                Expense::where('cabang_id',getCabangId())->orderByRaw('is_approve IS NULL DESC')->latest()->paginate($this->paginate) :
                Expense::where('cabang_id',getCabangId())->orderByRaw('is_approve IS NULL DESC')->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
