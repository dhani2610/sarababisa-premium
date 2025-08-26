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
use App\Models\StoreSetting;

class AdminProsesData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $type;

    public function mount()
    {
        $this->type = Type::pluck('id')->toArray();
    }

    
    public $service_id;
    public $pin;
    public $pola;

    public function openPinModal($id)
    {
        $this->service_id = $id;
        $service = ServiceTransaction::find($id);

        $this->pin = $service->pin;
        $this->pola = $service->pola;

        // kirim event sesuai ID
        $this->dispatchBrowserEvent('open-pin-modal-' . $id, [
            'pola' => $this->pola,
        ]);
    }

    public $status = [
        'Belum cek',
        'Sedang Tes',
        'Menunggu Konfirmasi',
        'Sedang Dikerjakan',
        'Menunggu Sparepart'
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
        $users = User::where('role', 'Teknisi')->get();
        $sales = User::where('role', 'Sales')->get();
        $tokoSetting = StoreSetting::find(1);
        $penerima = User::all();
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $service_actions = ServiceAction::all();
        $products = Product::whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();
        $jumlah_bisa_diambil = ServiceTransaction::where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->count();


        $prosess = ServiceTransaction::when(
                $this->search,
                function ($q) {
                    $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orWhere('nama_barang', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orWhere('imei', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil']);
                }
            )->when($this->type, function ($q) {
                $q->whereIn('types_id', $this->type);
            })->when($this->status, function ($q) {
                $q->whereIn('status_servis', $this->status);
            })->orderBy('created_at','desc')->paginate($this->paginate);
            
        return view('livewire.admin-proses-data', [
            'toko' => $toko,
            'tokoSetting' => $tokoSetting,
            'processes_count' => $processes_count,
            'jumlah_bisa_diambil' => $jumlah_bisa_diambil,
            'jumlah_sudah_diambil' => $jumlah_sudah_diambil,
            'users' => $users,
            'sales' => $sales,
            'penerima' => $penerima,
            'customers' => $customers,
            'types' => $types,
            'brands' => $brands,
            'model_series' => $model_series,
            'capacities' => $capacities,
            'service_actions' => $service_actions,
            'products' => $products,
            'processes' => $prosess,
        ]);
    }
}
