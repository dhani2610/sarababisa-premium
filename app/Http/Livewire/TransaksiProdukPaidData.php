<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class TransaksiProdukPaidData extends Component
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
        $jumlah_semua = Order::where('cabang_id',getCabangId())->get()->count();
        $jumlah_lunas = Order::where('cabang_id',getCabangId())->where('due', '0')->count();
        $jumlah_tidaklunas = Order::where('cabang_id',getCabangId())->where('due', '>', '0')->count();

        return view('livewire.transaksi-produk-paid-data', [
            'jumlah_semua' => $jumlah_semua,
            'jumlah_lunas' => $jumlah_lunas,
            'jumlah_tidaklunas' => $jumlah_tidaklunas,
            'orders' => $this->search === null ?
                Order::where('cabang_id',getCabangId())->orderByRaw('is_approve IS NULL DESC')->latest()->where('due', '0')->paginate($this->paginate) :
                Order::where('cabang_id',getCabangId())->orderByRaw('is_approve IS NULL DESC')->latest()->where('due', '0')->where('invoice_no', 'like', '%' . $this->search . '%')->orWhere('nama_pelanggan', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
