<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class SalesLaporanPenjualanData extends Component
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
        $jumlah = OrderDetail::where('users_id', Auth::user()->id)->whereHas('order', function ($query) {
            $query->where('is_approve', 'Setuju');
        })->sum('quantity');
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        return view('livewire.sales-laporan-penjualan-data', [
            'jumlah' => $jumlah,
            'toko' => $toko,
            'product_transactions' => $this->search === null ?
                OrderDetail::where('users_id', Auth::user()->id)->whereHas('order', function ($query) {
                    $query->where('is_approve', 'Setuju');
                })->latest()->paginate($this->paginate) :
                OrderDetail::where('users_id', Auth::user()->id)->whereHas('order', function ($query) {
                    $query->where('is_approve', 'Setuju');
                })->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
