<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class SampahProdukData extends Component
{
    use WithPagination;

    public $search;

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public $paginate = 10;

    public function render()
    {
        $items = Product::where('cabang_id',getCabangId())->onlyTrashed()->get();
        $items_count = $items->where('cabang_id',getCabangId())->whereNotNull('deleted_at')->count();

        return view('livewire.sampah-produk-data', [
            'items_count' => $items_count,
            'items' => Product::where('cabang_id',getCabangId())->onlyTrashed()->latest()->when($this->search, function ($q) {
                $q->where('product_name', 'like', '%' . $this->search . '%')->onlyTrashed();
            })->paginate($this->paginate),
        ]);
    }
}
