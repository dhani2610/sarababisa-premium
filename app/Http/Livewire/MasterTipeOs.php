<?php

namespace App\Http\Livewire;

use App\Models\TipeOs;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Type;

class MasterTipeOs extends Component
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
        $count = TipeOs::all()->count();
        return view('livewire.master-tipe-os', [
            'count' => $count,
            'TipeOs' => $this->search === null ?
                TipeOs::latest()->paginate($this->paginate) :
                TipeOs::latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
