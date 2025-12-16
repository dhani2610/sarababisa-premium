<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use App\Models\ServiceTransaction;

class AdminLaporanServisData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $user;

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
        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $jumlah = ServiceTransaction::where('cabang_id',getCabangId())->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->count();
        $toko = StoreSetting::where('cabang_id',getCabangId())->where('cabang_id',getCabangId())->first();

        return view('livewire.admin-laporan-servis-data', [
            'jumlah' => $jumlah,
            'users' => $users,
            'toko' => $toko,
            'services' => $this->search === null ?
                ServiceTransaction::where('cabang_id',getCabangId())->orderBy('tgl_disetujui', 'desc')->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->where('users_id', 'like', '%' . $this->user . '%')->paginate($this->paginate) :
                ServiceTransaction::where('cabang_id',getCabangId())->orderBy('tgl_disetujui', 'desc')->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->where('created_at', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
