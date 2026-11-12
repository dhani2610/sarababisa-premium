<?php

namespace App\Http\Livewire;

use App\Models\Debt;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TeknisiKasbonData extends Component
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
        $worker = Auth::user()?->worker;

        if (!$worker) {
            return view('livewire.teknisi-kasbon-data', [
                'hasWorker' => false,
                'debts_count' => 0,
                'debts' => new LengthAwarePaginator([], 0, $this->paginate),
            ]);
        }

        $debts_count = Debt::where('workers_id', $worker->id)->count();
        return view('livewire.teknisi-kasbon-data', [
            'hasWorker' => true,
            'debts_count' => $debts_count,
            'debts' => $this->search === null ?
                Debt::latest()->where('workers_id', $worker->id)->paginate($this->paginate) :
                Debt::latest()->where('workers_id', $worker->id)->where('item', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
