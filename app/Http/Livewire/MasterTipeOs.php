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
        $count = TipeOs::where('cabang_id', getCabangId())->get()->count();
        return view('livewire.master-tipe-os', [
            'count' => $count,
            'TipeOs' => $this->search === null ?
                TipeOs::where('cabang_id', getCabangId())->latest()->paginate($this->paginate) :
                TipeOs::where('cabang_id', getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
