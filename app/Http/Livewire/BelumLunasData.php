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
    public $type = [];
    public $kondisi = ['Sudah jadi', 'Tidak bisa', 'Dibatalkan', 'Menunggu konfirmasi'];
    public $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        //
    }

    public function updatedKondisi($value, $index)
    {
        if (!$value) { unset($this->kondisi[$index]); }
    }

    public function render()
    {
        $cabangId = getCabangId();
        $toko = User::find(1);
        $types = Type::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $brands = Brand::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $capacities = Capacity::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $model_series = collect();
        $workers = User::forCabang()->select('id', 'name', 'role')->get();
        $users = $workers->where('role', 'Teknisi')->values();
        $actions = ServiceAction::select('id', 'nama_tindakan')->where('cabang_id', $cabangId)->get();
        $storeSetting = StoreSetting::where('cabang_id', $cabangId)->first();

        $rawCustomers = Customer::select('id', 'nama', 'nomor_hp')
            ->where('cabang_id', $cabangId)
            ->whereHas('servicetransaction', function ($query) {
                $query->where('tipe_status_pembayaran', 0);
            })
            ->get();

        $customers = $rawCustomers->unique(function ($item) {
            $p = preg_replace('/[^0-9]/', '', $item->nomor_hp ?? '');
            if (str_starts_with($p, '62')) $p = substr($p, 2);
            if (str_starts_with($p, '0')) $p = substr($p, 1);
            if (!empty($p)) {
                return 'phone_' . $p;
            }
            return 'name_' . strtolower(trim($item->nama));
        })->values();
        // Hitung khusus untuk badge Belum Lunas
        $jumlah_belum_lunas = ServiceTransaction::where('cabang_id', $cabangId)->where('tipe_status_pembayaran', '0')->count();

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
