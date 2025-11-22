<?php

namespace App\Http\Livewire;

use App\Models\Color;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Type;

class MasterWarna extends Component
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
        $count = Color::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.master-warna', [
            'count' => $count,
            'colors' => $this->search === null ?
                Color::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Color::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
