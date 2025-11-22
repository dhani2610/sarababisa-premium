<?php

namespace App\Http\Livewire;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\ModelSerie;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\Auth;

class TeknisiLaporanServisData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::all();
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $jumlah = ServiceTransaction::where('is_approve', 'Setuju')->where('users_id', Auth::user()->id)->count();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        return view('livewire.teknisi-laporan-servis-data', [
            'jumlah' => $jumlah,
            'users' => $users,
            'customers' => $customers,
            'types' => $types,
            'brands' => $brands,
            'capacities' => $capacities,
            'model_series' => $model_series,
            'toko' => $toko,
            'services' => $this->search === null ?
                ServiceTransaction::latest()->where('is_approve', 'Setuju')->where('users_id', Auth::user()->id)->paginate($this->paginate) :
                ServiceTransaction::latest()->where('is_approve', 'Setuju')->where('users_id', Auth::user()->id)->where('created_at', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
