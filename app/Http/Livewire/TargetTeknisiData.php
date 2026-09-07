<?php

namespace App\Http\Livewire;

use App\Models\SalesTarget;
use App\Models\TeknisiTarget;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TargetTeknisiData extends Component
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
        $targets_count = TeknisiTarget::where('cabang_id',getCabangId())->where('cabang_id',getCabangId())->get()->count();
        $teknisi = User::forCabang()->where('role', 'Teknisi')->get();
        return view('livewire.target-teknisi-data', [
            'targets_count' => $targets_count,
            'teknisi' => $teknisi,
            'targets' => $this->search === null ?
                TeknisiTarget::where('cabang_id',getCabangId())->where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                TeknisiTarget::where('cabang_id',getCabangId())->where('cabang_id',getCabangId())->latest()->where('teknisi_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
