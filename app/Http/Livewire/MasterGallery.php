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
        $count = Gallery::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.master-gallery', [
            'count' => $count,
            'galleries' => $this->search === null ?
                Gallery::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Gallery::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
