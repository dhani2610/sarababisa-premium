<?php

namespace App\Http\Livewire;

use App\Models\ServiceTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class SampahServisData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $sampahServis = ServiceTransaction::where('cabang_id',getCabangId())->onlyTrashed()->get();
        $services_count = $sampahServis->where('cabang_id',getCabangId())->whereNotNull('deleted_at')->count();

        return view('livewire.sampah-servis-data', [
            'services_count' => $services_count,
            'services' => ServiceTransaction::where('cabang_id',getCabangId())->onlyTrashed()->latest()->when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->onlyTrashed()->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->onlyTrashed()->orWhere('nama_barang', 'like', '%' . $this->search . '%')->onlyTrashed();
            })->paginate($this->paginate),
        ]);
    }
}
