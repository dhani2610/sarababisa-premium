<?php

namespace App\Http\Livewire;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\ModelSerie;
use Livewire\WithPagination;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Models\StoreSetting;

class AdminBisaDiambilData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $type;

    public function mount()
    {
        $this->type = Type::pluck('id')->toArray();
    }

    public $kondisi = [
        'Sudah jadi',
        'Tidak bisa',
        'Dibatalkan',
        'Menunggu konfirmasi'
    ];

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedKondisi($value, $index)
    {
        if (!$value) {
            unset($this->kondisi[$index]);
        }
    }

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

    public function render()
    {
        $customers = Customer::all();
        $tokoSetting = StoreSetting::find(1);
        $toko = User::find(1);
        $users = User::where('role', 'Teknisi')->get();
        $workers = User::all();
        $types = Type::all();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $actions = ServiceAction::all();
        $processes_count = ServiceTransaction::whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->count();
        $bisadiambil = ServiceTransaction::when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('tindakan_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('nama_barang', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('imei', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil');
            })->when($this->type, function ($q) {
                $q->whereIn('types_id', $this->type);
            })->when($this->kondisi, function ($q) {
                $q->whereIn('kondisi_servis', $this->kondisi)->where('status_servis', 'Bisa Diambil');
            })->orderBy('created_at','desc')->paginate($this->paginate);
        return view('livewire.admin-bisa-diambil-data', [
            'users' => $users,
            'toko' => $toko,
            'workers' => $workers,
            'customers' => $customers,
            'tokoSetting' => $tokoSetting,
            'types' => $types,
            'brands' => $brands,
            'model_series' => $model_series,
            'capacities' => $capacities,
            'actions' => $actions,
            'processes_count' => $processes_count,
            'jumlah_sudah_diambil' => $jumlah_sudah_diambil,
            'jumlah_bisa_diambil' => $jumlah_bisa_diambil,
            'bisadiambil' =>$bisadiambil,
        ]);
    }
}
