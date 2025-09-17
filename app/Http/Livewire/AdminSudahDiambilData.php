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

class AdminSudahDiambilData extends Component
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

    public function render()
    {
        $users = User::where('role', 'Teknisi')->get();
        $toko = User::find(1);
        $workers = User::when(auth()->user()->role === 'Teknisi', function ($q) {
            $q->where('id',auth()->user()->id);
        })->get();
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $actions = ServiceAction::all();
        $jumlahsudahdiambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->when(auth()->user()->role === 'Teknisi', function ($q) {
            $q->where('penerima',auth()->user()->name);
        })->count();
        $processes_count = ServiceTransaction::whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->when(auth()->user()->role === 'Teknisi', function ($q) {
            $q->where('penerima',auth()->user()->name);
        })->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('status_servis', 'Bisa Diambil')->when(auth()->user()->role === 'Teknisi', function ($q) {
            $q->where('penerima',auth()->user()->name);
        })->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->when(auth()->user()->role === 'Teknisi', function ($q) {
            $q->where('penerima',auth()->user()->name);
        })->count();
        return view('livewire.admin-sudah-diambil-data', [
            'processes_count' => $processes_count,
            'jumlah_bisa_diambil' => $jumlah_bisa_diambil,
            'jumlah_sudah_diambil' => $jumlah_sudah_diambil,
            'jumlahsudahdiambil' => $jumlahsudahdiambil,
            'toko' => $toko,
            'users' => $users,
            'workers' => $workers,
            'customers' => $customers,
            'types' => $types,
            'brands' => $brands,
            'capacities' => $capacities,
            'model_series' => $model_series,
            'actions' => $actions,
            'service_transactions' =>
            ServiceTransaction::orderBy('tgl_ambil', 'desc')->when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->orWhere('tindakan_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->orWhere('nama_barang', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->orWhere('imei', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil');
            })->when($this->type, function ($q) {
                $q->whereIn('types_id', $this->type);
            })->when(auth()->user()->role === 'Teknisi', function ($q) {
                $q->where('penerima',auth()->user()->name);
            })->when($this->kondisi, function ($q) {
                $q->whereIn('kondisi_servis', $this->kondisi)->where('status_servis', 'Sudah Diambil');
            })->paginate($this->paginate),
        ]);
    }
}
