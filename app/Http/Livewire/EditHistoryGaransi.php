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
            $users = User::where('id','!=',1)->where('cabang_id',getCabangId())->where('id',auth()->user()->id)->where('role','!=','Investor')->get();
        }else{
            $users = User::where('id','!=',1)->where('cabang_id',getCabangId())->where('role','!=','Investor')->get();
        }

        $serviceTransactions = ServiceTransaction::where('cabang_id',getCabangId())->orderBy('created_at', 'desc')->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::where('cabang_id',getCabangId())->get();
        $customer = Customer::get();
        // $historyGaransi = HIstoryGaransi::find

          // 1. Decode JSON ke Array
        $qcMasuk = $this->historyGaransi->fungsi_masuk ? json_decode($this->historyGaransi->fungsi_masuk, true) : [];
        $qcKeluar = $this->historyGaransi->fungsi_keluar ? json_decode($this->historyGaransi->fungsi_keluar, true) : [];
        // dd($qcMasuk,$items->qc_masuk);
        if ($qcMasuk != null) {
            # code...
            $qcItems = array_keys($qcMasuk);
        }else{
            $qcItems = [];
        }

        if (empty($qcItems) && !empty($qcKeluar)) {
            $qcItems = array_keys($qcKeluar);
        }

        if (empty($qcItems)) {
            $qcItems = [];
        }

        return view('livewire.edit-history-garansi', [
            'serviceTransactions' => $serviceTransactions,
            'products' => $products,
            'serviceActions' => $serviceActions,
            'users' => $users,
            'customer' => $customer,
            'qcItems' => $qcItems,
            'qcMasuk' => $qcMasuk,
            'qcKeluar' => $qcKeluar,
        ]);
    }
}
