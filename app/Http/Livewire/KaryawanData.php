<?php

namespace App\Http\Livewire;

use App\Models\Budget;
use App\Models\Worker;
use Livewire\Component;
use Livewire\WithPagination;

class KaryawanData extends Component
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
        if (auth()->user()->role == 'Kepala Toko') {
            $query = Worker::query();
        } else {
            $query = Worker::where('name', auth()->user()->name);
        }

        if ($this->search !== null) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $workers = $query->latest()->paginate($this->paginate);

        $budgets = Budget::all();
        $workers_count = Worker::count();

        return view('livewire.karyawan-data', [
            'budgets' => $budgets,
            'workers' => $workers,
            'workers_count' => $workers_count,
        ]);
    }
}
