<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\ModelSerie;
use App\Models\TipeOs;
use Livewire\Component;
use Livewire\WithPagination;

class MasterModelSeri extends Component
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
        $brands = Brand::all();
        $tipe = TipeOs::all();
        $model_series_count = ModelSerie::all()->count();
        return view('livewire.master-model-seri', [
            'brands' => $brands,
            'tipe' => $tipe,
            'model_series_count' => $model_series_count,
            'model_series' => $this->search === null ?
                ModelSerie::latest()->paginate($this->paginate) :
                ModelSerie::latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
