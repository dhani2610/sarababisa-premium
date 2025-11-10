<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProduct extends Component
{
    use WithPagination;
    use LivewireAlert;

    public $product;

    public string $query = '';

    public $categories_id;

    public $search_results;

    public $showCount = 9;

    public $featured = false;
    public $barcode;

    protected $queryString = [
        'query'       => ['except' => ''],
        'categories_id' => ['except' => null],
        'showCount'   => ['except' => 9],
    ];

    public function loadMore()
    {
        $this->showCount = (int)$this->showCount + 5;
    }

    public function updatedBarcode($value)
    {
        $value = trim($value);
        if (!$value) return;

        // Cari produk berdasarkan barcode
        $product = Product::where('product_code', $value)->first();

        if ($product) {
            // Panggil fungsi yang sama seperti klik manual
            $this->selectProduct($product);
        }

        // Reset input untuk scan berikutnya
        $this->barcode = '';
    }


    public $customer_tipe = null; // default user

    protected $listeners = ['updateCustomerType' => 'setCustomerType'];

    public function setCustomerType($tipe)
    {
        $this->customer_tipe = $tipe;
    }

    public function selectProduct($product)
    {
        if ($this->customer_tipe !== null) {
            $product['customer_tipe'] = $this->customer_tipe;
            $this->emit('productSelected', $product);
        } else {
            $this->alert('error', 'Pilih pelanggan terlebih dahulu!');
        }
    }


    // public function selectProduct($product)
    // {
    //     $this->emit('productSelected', $product);
    // }

    public function getCategoriesProperty()
    {
        return Category::pluck('category_name', 'id');
    }

    // in case parametre is passed to mount method, else it will be null
    public function mount()
    {
        $this->search_results = [];
    }

    public function render()
    {
        $query = Product::latest()->with('category')->where('stok', '>=', '1')
            ->when($this->query, function ($query) {
                $query->where(function ($query) {
                    $query->where('product_name', 'like', '%' . $this->query . '%')->orWhere('product_code', 'like', '%' . $this->query . '%')->orWhere('nomor_seri', 'like', '%' . $this->query . '%');
                });
            })
            ->when($this->categories_id, function ($query) {
                $query->where('categories_id', $this->categories_id);
            });

        $products = $query->paginate($this->showCount);

        return view('livewire.search-product', [
            'products' => $products,
        ]);
    }

    // Reset query, category, and featured
    public function resetQuery()
    {
        // Reset query, category, and featured
        $this->reset(['query', 'categories_id']);
    }

    public function updatedQuery()
    {
        if (!empty($this->search_results)) {
            $this->product = $this->search_results[0];
            $this->emit('productSelected', $this->product);
        }
    }
}
