<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;

class MasterMerek extends Component
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
        $brands_count = Brand::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.master-merek', [
            'brands_count' => $brands_count,
            'brands' => $this->search === null ?
                Brand::where('cabang_id',getCabangId())->paginate($this->paginate) :
                Brand::where('cabang_id',getCabangId())->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
