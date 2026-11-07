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
    public $type = [];

    public function mount()
    {
        //
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
        $cabangId = getCabangId();
        $toko = User::find(1);
        $customers = collect();
        $types = Type::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $brands = Brand::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $capacities = Capacity::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $model_series = collect();
        $workers = User::forCabang()->select('id', 'name', 'role')->get();
        $users = $workers->where('role', 'Teknisi')->values();
        $actions = ServiceAction::select('id', 'nama_tindakan')->where('cabang_id', $cabangId)->get();
        $storeSetting = StoreSetting::where('cabang_id', $cabangId)->first();

        $isTeknisi = auth()->check() && auth()->user()->role === 'Teknisi';
        $teknisiServisIds = $isTeknisi ? servisIdMultiTeknisi(auth()->id()) : [];

        $counts = ServiceTransaction::where('cabang_id', $cabangId)
            ->when($isTeknisi, function ($q) use ($teknisiServisIds) {
                $q->whereIn('id', $teknisiServisIds);
            })
            ->selectRaw("
                SUM(CASE WHEN status_servis NOT IN ('Bisa Diambil', 'Sudah Diambil') THEN 1 ELSE 0 END) as processes_count,
                SUM(CASE WHEN status_servis = 'Bisa Diambil' THEN 1 ELSE 0 END) as jumlah_bisa_diambil,
                SUM(CASE WHEN status_servis = 'Sudah Diambil' THEN 1 ELSE 0 END) as jumlah_sudah_diambil,
                SUM(CASE WHEN status_servis = 'Sudah Diambil' AND is_approve IS NULL THEN 1 ELSE 0 END) as jumlah_belum_disetujui
            ")
            ->first();

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
            'processes_count' => (int) ($counts->processes_count ?? 0),
            'jumlah_bisa_diambil' => (int) ($counts->jumlah_bisa_diambil ?? 0),
            'jumlah_sudah_diambil' => (int) ($counts->jumlah_sudah_diambil ?? 0),
            'jumlah_belum_disetujui' => (int) ($counts->jumlah_belum_disetujui ?? 0),
            'bisadiambil' => collect(),
            'storeSetting' => $storeSetting,
        ]);
    }
}
