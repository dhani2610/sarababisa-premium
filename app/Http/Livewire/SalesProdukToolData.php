<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;

class SalesProdukToolData extends Component
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
        $tools = SubCategory::where('categories_id', '=', '4')->get();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        $tools_count = Product::where('categories_id', '=', '4')->count();
        return view('livewire.sales-produk-tool-data', [
            'toko' => $toko,
            'tools' => $tools,
            'tools_count' => $tools_count,
            'products' => $this->search === null ?
                Product::latest()->where('categories_id', '=', '4')->paginate($this->paginate) :
                Product::latest()->where('categories_id', '=', '4')->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
