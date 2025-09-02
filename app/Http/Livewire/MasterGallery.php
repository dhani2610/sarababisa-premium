<?php

namespace App\Http\Livewire;

use App\Models\Gallery;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Type;

class MasterGallery extends Component
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
        $count = Gallery::all()->count();
        return view('livewire.master-gallery', [
            'count' => $count,
            'galleries' => $this->search === null ?
                Gallery::latest()->paginate($this->paginate) :
                Gallery::latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
