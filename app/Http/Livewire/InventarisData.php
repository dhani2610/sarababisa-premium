<?php

namespace App\Http\Livewire;

use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;

class InventarisData extends Component
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
        $inventories_count = Inventory::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.inventaris-data', [
            'inventories_count' => $inventories_count,
            'inventories' => $this->search === null ?
                Inventory::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Inventory::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
