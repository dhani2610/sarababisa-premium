<?php

namespace App\Http\Livewire;

use App\Models\HistoryGaransi;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Models\User;
use App\Models\Customer;
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
            $users = User::where('cabang_id',getCabangId())->where('id',auth()->user()->id)->where('role','!=','Investor')->get();
        }else{
            $users = User::where('cabang_id',getCabangId())->where('role','!=','Investor')->get();
        }

        $serviceTransactions = ServiceTransaction::where('cabang_id',getCabangId())->orderBy('created_at', 'desc')->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::where('cabang_id',getCabangId())->get();
        $customer = Customer::get();

        return view('livewire.edit-history-garansi', [
            'serviceTransactions' => $serviceTransactions,
            'products' => $products,
            'serviceActions' => $serviceActions,
            'users' => $users,
            'customer' => $customer,
        ]);
    }
}
