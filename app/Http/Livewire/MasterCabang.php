<?php

namespace App\Http\Livewire;

use App\Models\Cabang;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Type;

class MasterCabang extends Component
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
        $count = Cabang::all()->count();
        return view('livewire.master-cabang', [
            'count' => $count,
            'cabang' => $this->search === null ?
                Cabang::latest()->paginate($this->paginate) :
                Cabang::latest()->where('nama_cabang', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
