<?php

namespace App\Http\Livewire;

use App\Models\RincianInvest;
use Livewire\Component;
use Livewire\WithPagination;

class MasterRincianInvest extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $filterTipe = 0; // 0 = semua

    protected $updatesQueryString = ['search', 'filterTipe'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setFilter($tipe)
    {
        $this->filterTipe = $tipe;
        $this->resetPage();
    }

    public function render()
    {
        $query = RincianInvest::query();

        if ($this->filterTipe != 0) {
            $query->where('tipe', $this->filterTipe);
        }

        if ($this->search) {
            $query->where('keterangan', 'like', '%' . $this->search . '%');
        }

        $data = $query->latest()->paginate($this->paginate);

        $totals = [
            'masuk' => RincianInvest::where('tipe', 1)->sum('nominal'),
            'pembagian' => RincianInvest::where('tipe', 2)->sum('nominal'),
            'lain' => RincianInvest::where('tipe', 3)->sum('nominal'),
        ];

        return view('livewire.master-rincian-invest', [
            'rincian' => $data,
            'totals' => $totals,
        ]);
    }
}
