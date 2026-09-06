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
        $activeCabangId = getCabangId();
        $workers = Worker::where('cabang_id', $activeCabangId)->get();
        $users = User::where(function($q) use ($activeCabangId) {
            $q->where('cabang_id', $activeCabangId)
              ->orWhereHas('cabangs', function($cb) use ($activeCabangId) {
                  $cb->where('cabangs.id', $activeCabangId);
              });
        })->whereNotIn('role', ['Kepala Toko','Investor'])->get();
        $salaries_count = Salary::where('cabang_id', $activeCabangId)->get()->count();
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
