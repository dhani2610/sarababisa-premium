<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ProdukData extends Component
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
        $categories = Category::all();
        $spareparts = SubCategory::where('categories_id', '=', '2')->get();
        $accessories = SubCategory::where('categories_id', '=', '3')->get();
        $tools = SubCategory::where('categories_id', '=', '4')->get();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $colors = Color::all();
        $products_count = Product::all()->count();
        $toko = StoreSetting::find(1);
        $itemready = Product::where('stok', '>', 0)->count();
        $stokready = Product::where('stok', '>', 0)->sum('stok');
        $modalready = Product::where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));
        $stokhabis = Product::where('stok', 0)->count();
        $nominalterjual = Product::where('stok', 0)->sum('harga_jual');


        $productCategoty = Category::orderBy('created_at','asc')->get();
        return view('livewire.produk-data', [
            'brands' => $brands,
            'capacities' => $capacities,
            'model_series' => $model_series,
            'colors' => $colors,
            'toko' => $toko,
            'categories' => $categories,
            'spareparts' => $spareparts,
            'accessories' => $accessories,
            'tools' => $tools,
            'products_count' => $products_count,
            'itemready' => $itemready,
            'stokready' => $stokready,
            'modalready' => $modalready,
            'stokhabis' => $stokhabis,
            'productCategoty' => $productCategoty,
            'nominalterjual' => $nominalterjual,
            'products' => $this->search === null ?
                Product::latest()->paginate($this->paginate) :
                Product::latest()->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
