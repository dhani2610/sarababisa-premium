<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Type;

class MasterJenisBarang extends Component
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
        $types_count = Type::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.master-jenis-barang', [
            'types_count' => $types_count,
            'types' => $this->search === null ?
                Type::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Type::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
