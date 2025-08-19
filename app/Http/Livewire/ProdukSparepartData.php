<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProdukSparepartData extends Component
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


    
    use LivewireAlert;

    public $barcode;
    public $modalOpen = false;

    public function updatedBarcode($value)
    {
        if ($value) {
            $this->tambahStok($value);
            $this->barcode = ''; // reset input supaya bisa scan lagi
        }
    }

    public function tambahStok($barcode)
    {
        $product = Product::where('product_code', $barcode)->first();
        if ($product) {
            $product->stok += 1;
            $product->save();

            $this->alert('success', 'Berhasil tambah 1 stok produk '.$product->product_name);
        } else {
            $this->alert('error', 'Produk tidak ditemukan!');

            $this->dispatchBrowserEvent('stok-updated', [
                'message' => "Produk dengan barcode $barcode tidak ditemukan!"
            ]);
        }
    }


    public function render()
    {
        $spareparts = SubCategory::where('categories_id', '=', '2')->get();
        $model_series = ModelSerie::all();
        $toko = StoreSetting::find(1);
        $spareparts_count = Product::where('categories_id', '=', '2')->count();
        $sparepartitemready = Product::where('categories_id', 2)->where('stok', '>', 0)->count();
        $sparepartstokready = Product::where('categories_id', 2)->where('stok', '>', 0)->sum('stok');
        $sparepartmodalready = Product::where('categories_id', 2)->where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));
        $sparepartstokhabis = Product::where('categories_id', 2)->where('stok', 0)->count();
        $sparepartnominalterjual = Product::where('categories_id', 2)->where('stok', 0)->sum('harga_jual');

        $topProducts = OrderDetail::select(
            'order_details.products_id',
            'products.product_name',
            'products.harga_jual',
            DB::raw('SUM(order_details.quantity) as total_terjual'),
            DB::raw('SUM(order_details.quantity * products.harga_jual) as omzet')
        )
        ->join('products', 'products.id', '=', 'order_details.products_id')
        ->groupBy('order_details.products_id', 'products.product_name', 'products.harga_jual')
        ->orderByDesc('total_terjual')
        ->where('products.categories_id', 2)
        ->limit(5)
        ->get();


        return view('livewire.produk-sparepart-data', [
            'topProducts' => $topProducts,
            'toko' => $toko,
            'spareparts' => $spareparts,
            'model_series' => $model_series,
            'spareparts_count' => $spareparts_count,
            'sparepartitemready' => $sparepartitemready,
            'sparepartstokready' => $sparepartstokready,
            'sparepartmodalready' => $sparepartmodalready,
            'sparepartstokhabis' => $sparepartstokhabis,
            'sparepartnominalterjual' => $sparepartnominalterjual,
            'products' => $this->search === null ?
                Product::latest()->where('categories_id', '=', '2')->paginate($this->paginate) :
                Product::latest()->where('categories_id', '=', '2')->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
