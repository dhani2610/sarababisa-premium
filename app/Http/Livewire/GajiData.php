<?php

namespace App\Http\Livewire;

use App\Models\Budget;
use App\Models\Salary;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerUser;
use Livewire\Component;
use Livewire\WithPagination;

class GajiData extends Component
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
        $workers = Worker::where('cabang_id',getCabangId())->get();
        $users = User::where('cabang_id',getCabangId())->whereNotIn('role', ['Kepala Toko','Investor'])->get();
        $salaries_count = Salary::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.gaji-data', [
            'workers' => $workers,
            'users' => $users,
            'salaries_count' => $salaries_count,
            'worker_users' => $this->search === null ?
                Salary::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Salary::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
