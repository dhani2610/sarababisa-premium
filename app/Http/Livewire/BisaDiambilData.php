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

class BisaDiambilData extends Component
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
        $toko = User::find(1);
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $types = Type::where('cabang_id',getCabangId())->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $users = User::forCabang()->where('role', 'Teknisi')->get();
        $workers = User::forCabang()->get();
        $actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $storeSetting = StoreSetting::where('cabang_id',getCabangId())->first();;

        $processes_count = ServiceTransaction::where('cabang_id',getCabangId())->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->count();
        $jumlah_belum_disetujui = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->where('is_approve', '=', null)->count();

        $bisadiambil = ServiceTransaction::where('cabang_id',getCabangId())->when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('tindakan_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('nama_barang', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil')->orWhere('imei', 'like', '%' . $this->search . '%')->where('status_servis', 'Bisa Diambil');
            })->when($this->type, function ($q) {
                $q->whereIn('types_id', $this->type);
            })->when($this->kondisi, function ($q) {
                $q->whereIn('kondisi_servis', $this->kondisi)->where('status_servis', 'Bisa Diambil');
            })->orderBy('created_at','desc')->paginate($this->paginate);
        return view('livewire.bisa-diambil-data', [
            'toko' => $toko,
            'users' => $users,
            'workers' => $workers,
            'customers' => $customers,
            'types' => $types,
            'brands' => $brands,
            'model_series' => $model_series,
            'capacities' => $capacities,
            'actions' => $actions,
            'processes_count' => $processes_count,
            'jumlah_bisa_diambil' => $jumlah_bisa_diambil,
            'jumlah_sudah_diambil' => $jumlah_sudah_diambil,
            'jumlah_belum_disetujui' => $jumlah_belum_disetujui,
            'bisadiambil' => $bisadiambil,
            'storeSetting' => $storeSetting,
        ]);
    }
}
