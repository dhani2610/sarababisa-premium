<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

class CustomerData extends Component
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

    protected $listeners = [
        'customerStored' => 'handleStored'
    ];

    public function render()
    {
        $customers_count = Customer::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.customer-data', [
            'customers_count' => $customers_count,
            'customers' => $this->search === null ?
                Customer::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Customer::where('cabang_id',getCabangId())->latest()->where('nama', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }

    public function handleStored($customer)
    {
        session()->flash('message', 'Data pelanggan ' . $customer['nama'] . ' berhasil ditambahkan');
    }
}
