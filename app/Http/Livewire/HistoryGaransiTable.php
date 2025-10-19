<?php

namespace App\Http\Livewire;

use App\Models\HistoryGaransi;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class HistoryGaransiTable extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = HistoryGaransi::with(['service', 'teknisi', 'penerima'])
            ->latest();

        if ($this->search) {
            $query->whereHas('service', function ($q) {
                $q->where('nomor_servis', 'like', '%' . $this->search . '%');
            });
        }
        // dd(auth()->user()->role);
        if (auth()->user()->role == 'Teknisi') {
            $query->where('teknisi_id', auth()->user()->id);
            $users = User::where('id',auth()->user()->id)->get();
        }else{
            $users = User::all();
        }

        $serviceTransactions = ServiceTransaction::all();
        $products = Product::whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::all();

        return view('livewire.history-garansi', [
            'data' => $query->paginate($this->paginate),
            'serviceTransactions' => $serviceTransactions,
            'products' => $products,
            'serviceActions' => $serviceActions,
            'users' => $users,
            'count' => $query->count(),
        ]);
    }
}
