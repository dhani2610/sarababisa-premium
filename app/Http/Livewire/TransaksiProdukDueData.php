<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class TransaksiProdukDueData extends Component
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
        $cabangId = getCabangId();
        $counts = Order::where('cabang_id', $cabangId)
            ->selectRaw("
                COUNT(*) as jumlah_semua,
                SUM(CASE WHEN due = 0 THEN 1 ELSE 0 END) as jumlah_lunas,
                SUM(CASE WHEN due > 0 THEN 1 ELSE 0 END) as jumlah_tidaklunas
            ")
            ->first();

        return view('livewire.transaksi-produk-due-data', [
            'jumlah_semua' => (int) ($counts->jumlah_semua ?? 0),
            'jumlah_lunas' => (int) ($counts->jumlah_lunas ?? 0),
            'jumlah_tidaklunas' => (int) ($counts->jumlah_tidaklunas ?? 0),
            'orders' => $this->search === null ?
                Order::where('cabang_id', $cabangId)->orderByRaw('is_approve IS NULL DESC')->latest()->where('due', '>', '0')->paginate($this->paginate) :
                Order::where('cabang_id', $cabangId)->orderByRaw('is_approve IS NULL DESC')->latest()->where('due', '>', '0')
                ->where(function ($q) {
                    $q->where('invoice_no', 'like', '%' . $this->search . '%')
                      ->orWhere('nama_pelanggan', 'like', '%' . $this->search . '%');
                })->paginate($this->paginate)
        ]);
    }
}
