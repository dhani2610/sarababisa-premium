<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class TopProduk extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $categoryId;
    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->categoryId = request()->query('id', $this->categoryId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    use LivewireAlert;
    public $barcode;
    public $modalOpen = false;



    public function render()
    {
        $categories = Category::all();

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
            ->when($this->categoryId, function ($query) {
                $query->where('products.categories_id', $this->categoryId);
            })
            ->when($this->search, function ($query) {
                $query->where('products.product_name', 'like', '%' . $this->search . '%');
            })
            ->paginate($this->paginate);

        $productCategoty = Category::orderBy('created_at', 'asc')->get();

        return view('livewire.top-produk', [
            'topProducts' => $topProducts,
            'categories' => $categories,
        ]);
    }
}
