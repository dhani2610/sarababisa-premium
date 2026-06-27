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

class BelumLunasData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $type;
    public $kondisi = ['Sudah jadi', 'Tidak bisa', 'Dibatalkan', 'Menunggu konfirmasi'];
    public $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->type = Type::pluck('id')->toArray();
    }

    public function updatedKondisi($value, $index)
    {
        if (!$value) { unset($this->kondisi[$index]); }
    }

    public function render()
    {
        $toko = User::find(1);
        $types = Type::where('cabang_id',getCabangId())->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $workers = User::where('cabang_id',getCabangId())->get();
        $actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $storeSetting = StoreSetting::where('cabang_id',getCabangId())->first();

         $customers = Customer::where('cabang_id', getCabangId())
        ->whereHas('servicetransaction', function ($query) {
            $query->where('tipe_status_pembayaran', 0);
        })
        ->get();
        // Hitung khusus untuk badge Belum Lunas
        $jumlah_belum_lunas = ServiceTransaction::where('cabang_id',getCabangId())->where('tipe_status_pembayaran', '0')->count();

        return view('livewire.belum-lunas-data', [
            'toko' => $toko,
            'users' => $users,
            'workers' => $workers,
            'customers' => $customers,
            'types' => $types,
            'brands' => $brands,
            'model_series' => $model_series,
            'storeSetting' => $storeSetting,
            'capacities' => $capacities,
            'actions' => $actions,
            'jumlah_belum_lunas' => $jumlah_belum_lunas, // Pass ke view
        ]);
    }
}
