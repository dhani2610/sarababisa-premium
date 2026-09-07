<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Capacity;
use App\Models\Color;
use App\Models\User;
use App\Models\Retur;
use App\Models\Product;
use Livewire\Component;
use App\Models\Customer;
use App\Models\ModelSerie;
use App\Models\Purchase;
use Livewire\WithPagination;

class TukarTambahData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $payment_method;

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
        $purchases_count = Purchase::where('cabang_id',getCabangId())->whereNull('keterangan')
            ->orWhere('keterangan', '!=', 'Tukar Tambah')->count();
        $returs_count = Retur::where('cabang_id',getCabangId())->get()->count();
        $trade_ins_count = Purchase::where('cabang_id',getCabangId())->where('keterangan', '=', 'Tukar Tambah')->count();
        $products = Product::where('cabang_id',getCabangId())->where('categories_id', '1')->get();
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $sales = User::forCabang()->where('role', 'Sales')->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $colors = Color::where('cabang_id',getCabangId())->get();
        return view('livewire.tukar-tambah-data', [
            'purchases_count' => $purchases_count,
            'returs_count' => $returs_count,
            'trade_ins_count' => $trade_ins_count,
            'products' => $products,
            'customers' => $customers,
            'sales' => $sales,
            'brands' => $brands,
            'model_series' => $model_series,
            'capacities' => $capacities,
            'colors' => $colors,
            'tradeins' => $this->search === null ?
                Purchase::where('cabang_id',getCabangId())->latest()->where('keterangan', 'Tukar Tambah')->paginate($this->paginate) :
                Purchase::where('cabang_id',getCabangId())->latest()->where('keterangan', 'Tukar Tambah')->where('reference_number', 'like', '%' . $this->search . '%')->orWhere('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
