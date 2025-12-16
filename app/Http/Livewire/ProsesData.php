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
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProsesData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $type;

    public function mount()
    {
        $this->type = Type::where('cabang_id',getCabangId())->pluck('id')->toArray();
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

    use LivewireAlert;


    public function updatedStatus($value, $index)
    {
        if (!$value) {
            unset($this->status[$index]);
        }
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
    public function render()
    {
        $toko = User::find(1);
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $types = Type::where('cabang_id',getCabangId())->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        // dd($brands,$model_series);

        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $sales = User::where('cabang_id',getCabangId())->where('role', 'Sales')->get();
        $penerima = User::where('cabang_id',getCabangId())->whereNotIn('role',['Investor','Kepala Toko'])->get();
        $service_actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();
        $processes_count = ServiceTransaction::where('cabang_id',getCabangId())->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->count();
        $jumlah_belum_disetujui = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->where('is_approve', '=', null)->count();

        $process = ServiceTransaction::where('cabang_id',getCabangId())->when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orWhere('nama_barang', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orWhere('imei', 'like', '%' . $this->search . '%')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil']);
            })->when($this->type, function ($q) {
                $q->whereIn('types_id', $this->type);
            })->when($this->status, function ($q) {
                $q->whereIn('status_servis', $this->status);
            })->orderBy('created_at','desc')->paginate($this->paginate);
        return view('livewire.proses-data', [
            'toko' => $toko,
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
            'processes_count' => $processes_count,
            'jumlah_bisa_diambil' => $jumlah_bisa_diambil,
            'jumlah_sudah_diambil' => $jumlah_sudah_diambil,
            'jumlah_belum_disetujui' => $jumlah_belum_disetujui,
            'processes' => $process,
        ]);
    }
}
