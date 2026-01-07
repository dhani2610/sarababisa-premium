<?php

namespace App\Http\Livewire;

use App\Models\Target;
use Livewire\Component;
use Livewire\WithPagination;

class TargetData extends Component
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
        $targets_count = Target::where('cabang_id',getCabangId())->get()->count();
        // $targets_count = Target::where('cabang_id',getCabangId())->get();
        // dd($targets_count);
        return view('livewire.target-data', [
            'targets_count' => $targets_count,
            'targets' => $this->search === null ?
                Target::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Target::where('cabang_id',getCabangId())->latest()->where('created_at', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
