<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class SampahPenjualanData extends Component
{
    use WithPagination;

    public $search;

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public $paginate = 10;

    public function render()
    {
        $items = Order::where('cabang_id',getCabangId())->onlyTrashed()->get();
        $items_count = $items->where('cabang_id',getCabangId())->whereNotNull('deleted_at')->count();

        return view('livewire.sampah-penjualan-data', [
            'items_count' => $items_count,
            'items' => Order::where('orders.cabang_id',getCabangId())->onlyTrashed()->select('orders.*', 'order_details.modal', 'order_details.quantity', 'order_details.product_name')->join('order_details', 'orders.id', '=', 'order_details.orders_id')->latest()->when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->onlyTrashed()->orWhere('invoice_no', 'like', '%' . $this->search . '%')->onlyTrashed()->orWhere('product_name', 'like', '%' . $this->search . '%')->onlyTrashed();
            })->paginate($this->paginate),
        ]);
    }
}
