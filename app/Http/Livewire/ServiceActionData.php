<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceAction;

class ServiceActionData extends Component
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

    protected $listeners = [
        'customerStored' => 'handleStored'
    ];

    public function render()
    {
        $cabangId = getCabangId(); // cabang aktif

        $service_actions_count = ServiceAction::where('cabang_id',$cabangId)->get()->count();
        return view('livewire.service-action-data', [
            'service_actions_count' => $service_actions_count,
            'service_actions' => $this->search === null ?
                ServiceAction::where('cabang_id',$cabangId)->latest()->paginate($this->paginate) :
                ServiceAction::where('cabang_id',$cabangId)->latest()->where('nama_tindakan', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }

    public function handleStored($service_action)
    {
        session()->flash('message', 'Data tindakan servis ' . $service_action['nama_tindakan'] . ' berhasil ditambahkan');
    }
}
