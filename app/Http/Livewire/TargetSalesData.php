<?php

namespace App\Http\Livewire;

use App\Models\SalesTarget;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TargetSalesData extends Component
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
        $targets_count = SalesTarget::where('cabang_id',getCabangId())->get()->count();
        $sales = User::forCabang()->where('role', 'Sales')->get();
        return view('livewire.target-sales-data', [
            'targets_count' => $targets_count,
            'sales' => $sales,
            'targets' => $this->search === null ?
                SalesTarget::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                SalesTarget::where('cabang_id',getCabangId())->latest()->where('sales_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
