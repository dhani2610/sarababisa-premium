<?php

namespace App\Http\Livewire;

use App\Models\Expense;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPengeluaranData extends Component
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
        $users = User::where('cabang_id',getCabangId())->get();
        $expenses_count = Expense::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.admin-pengeluaran-data', [
            'users' => $users,
            'expenses_count' => $expenses_count,
            'expenses' => $this->search === null ?
                Expense::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Expense::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
