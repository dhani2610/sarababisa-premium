<?php

namespace App\Http\Livewire;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\ModelSerie;
use Livewire\WithPagination;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\Auth;

class TeknisiProsesData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $type;

    public function mount()
    {
        $this->type = Type::pluck('id')->toArray();
    }

    public $status = [
        'Belum cek',
        'Sedang Tes',
        'Menunggu Konfirmasi',
        'Sedang Dikerjakan',
        'Menunggu Sparepart',
        'Sudah jadi',
        'Tidak bisa',
        'Dibatalkan',
        'Menunggu konfirmasi'
    ];

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedStatus($value, $index)
    {
        if (!$value) {
            unset($this->status[$index]);
        }
    }

    public function render()
    {
        $toko = User::find(1);
        $processes_count = ServiceTransaction::whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $sales = User::where('role', 'Sales')->get();
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $actions = ServiceAction::all();
        $products = Product::whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();

        $service  = ServiceTransaction::when($this->search, function ($q) {
            $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')
                ->orWhere('nomor_servis', 'like', '%' . $this->search . '%')
                ->orWhere('nama_barang', 'like', '%' . $this->search . '%')
                ->orWhere('imei', 'like', '%' . $this->search . '%')
                ->orWhere('status_servis', 'like', '%' . $this->search . '%');
        })->when($this->status, function ($q) {
            // $q->whereIn('status_servis', $this->status);
        })->orderBy('created_at','desc')->paginate($this->paginate);
        // dd($this->status);


        // dd($service);
        return view('livewire.teknisi-proses-data', [
            'toko' => $toko,
            'processes_count' => $processes_count,
            'customers' => $customers,
            'types' => $types,
            'brands' => $brands,
            'model_series' => $model_series,
            'capacities' => $capacities,
            'actions' => $actions,
            'products' => $products,
            'sales' => $sales,
            'processes' => $service,
        ]);
    }
}
