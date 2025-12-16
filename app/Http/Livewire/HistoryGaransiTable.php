<?php

namespace App\Http\Livewire;

use App\Models\HistoryGaransi;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\Customer;
use App\Models\ServiceTransaction;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class HistoryGaransiTable extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search = '';
    public $statusFilter = null; // null = semua, 1 = proses, 2 = selesai

    protected $updatesQueryString = [
        'search'        => ['except' => ''],
        'statusFilter'  => ['except' => ''],
    ];


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $cabang = getCabangId();

        /** --------------------------------------------------------
         *  MAIN QUERY
         *  -------------------------------------------------------*/
        $query = HistoryGaransi::with(['service', 'teknisi', 'penerima', 'pelanggan'])
            ->where('cabang_id', $cabang)
            ->latest();

        /** --------------------------------------------------------
         *  SEARCH (nomor servis)
         *  -------------------------------------------------------*/
        if (!empty($this->search)) {
            $query->whereHas('service', function ($q) {
                $q->where('nomor_servis', 'like', '%' . $this->search . '%');
            });
        }

        /** --------------------------------------------------------
         *  FILTER STATUS (pakai GET)
         *  -------------------------------------------------------*/
        $status = request()->get('status'); // ambil ?status=1 atau ?status=2

        if (!empty($status)) {
            $query->where('status', intval($status));
        }


        /** --------------------------------------------------------
         *  ROLE TEKNISI → hanya lihat servis miliknya
         *  -------------------------------------------------------*/
        if ($user->role === 'Teknisi') {
            $query->where('teknisi_id', $user->id);
            $users = User::where('cabang_id', $cabang)
                ->where('id', $user->id)
                ->where('role', '!=', 'Investor')
                ->get();
        } else {
            $users = User::where('cabang_id', $cabang)
                ->where('id','!=',1)
                ->where('role', '!=', 'Investor')
                ->get();
        }

        /** --------------------------------------------------------
         *  DATA PENDUKUNG
         *  -------------------------------------------------------*/
        $customer = Customer::where('cabang_id',getCabangId())->get();
        $serviceTransactions = ServiceTransaction::where('cabang_id', $cabang)
            ->latest()
            ->get();

        $products = Product::where('cabang_id', $cabang)
            ->whereHas('subCategory.category', function ($q) {
                $q->where('category_name', 'Sparepart');
            })
            ->where('stok', '>=', 1)
            ->get();

        $serviceActions = ServiceAction::where('cabang_id', $cabang)->get();

        /** --------------------------------------------------------
         *  COUNTER (lebih cepat)
         *  -------------------------------------------------------*/
        $baseCounter = HistoryGaransi::where('cabang_id', $cabang);

        return view('livewire.history-garansi', [
            'data'          => $query->paginate($this->paginate),
            'users'         => $users,
            'customer'      => $customer,
            'serviceTransactions' => $serviceTransactions,
            'products'      => $products,
            'serviceActions'=> $serviceActions,

            'count'         => $baseCounter->count(),
            'prosesCount'   => $baseCounter->clone()->where('status', 1)->count(),
            'selesaiCount'  => $baseCounter->clone()->where('status', 2)->count(),
            'dibatalkanCount'  => $baseCounter->clone()->where('status', 3)->count(),
        ]);
    }
}
