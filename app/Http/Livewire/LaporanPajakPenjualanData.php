<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Livewire\WithPagination;

class LaporanPajakPenjualanData extends Component
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
        $jumlah = OrderDetail::whereHas('order', function ($query) {
            $query->where('is_approve', 'Setuju');
            $query->where('is_approve', 'Setuju');
        })->sum('quantity');
        $toko = StoreSetting::find(1);

        $product_transactions = $this->search === null ?
                OrderDetail::whereHas('order', function ($query) {
                    $query->where('is_approve', 'Setuju');
                    $query->where('ppn', '!=','0');
                })->latest()->paginate($this->paginate) :
                OrderDetail::whereHas('order', function ($query) {
                    $query->where('is_approve', 'Setuju');
                    $query->where('ppn', '!=','0');
                })->latest()->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate);
        return view('livewire.laporan-pajak-penjualan-data', [
            'toko' => $toko,
            'jumlah' => $jumlah,
            'product_transactions' => $product_transactions
        ]);
    }
}
