<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Livewire\WithPagination;

class AdminLaporanPenjualanData extends Component
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
        })->sum('quantity');
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        return view('livewire.admin-laporan-penjualan-data', [
            'toko' => $toko,
            'jumlah' => $jumlah,
            'product_transactions' => $this->search === null ?
                OrderDetail::whereHas('order', function ($query) {
                    $query->where('is_approve', 'Setuju');
                })->latest()->paginate($this->paginate) :
                OrderDetail::whereHas('order', function ($query) {
                    $query->where('is_approve', 'Setuju');
                })->latest()->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
