<?php
namespace App\Http\Livewire;

use App\Models\RincianInvest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MasterRincianInvest extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $filterTipe = 0;
    public $filterInvestor = 0; // tambahkan
    protected $updatesQueryString = ['search', 'filterTipe', 'filterInvestor'];

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
        $query = RincianInvest::with('investor');

        if (auth()->user()->role == 'Investor') {
            $query->where('id_investor', auth()->id());
        } elseif ($this->filterInvestor != 0) {
            $query->where('id_investor', $this->filterInvestor);
        }

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
            'investors' => User::where('role', 'Investor')->get(), // untuk dropdown filter
        ]);
    }
}
