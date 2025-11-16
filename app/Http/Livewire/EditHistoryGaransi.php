<?php

namespace App\Http\Livewire;

use App\Models\HistoryGaransi;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Models\User;
use Livewire\Component;

class EditHistoryGaransi extends Component
{


    public $historyGaransi;
        
    public $modalOpen = false;

    public function mount($historyGaransi)
    {
        $this->historyGaransi = $historyGaransi;

        // begitu halaman dibuka, modal langsung ON
        $this->modalOpen = true;
    }
    public function render()
    {
        // dd(auth()->user()->role);
        if (auth()->user()->role == 'Teknisi') {
            $users = User::where('id',auth()->user()->id)->get();
        }else{
            $users = User::all();
        }

        $serviceTransactions = ServiceTransaction::orderBy('created_at', 'desc')->get();
        $products = Product::whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::all();

        return view('livewire.edit-history-garansi', [
            'serviceTransactions' => $serviceTransactions,
            'products' => $products,
            'serviceActions' => $serviceActions,
            'users' => $users,
        ]);
    }
}
