<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceTransaction;
use App\Models\User;

class LaporanServisData extends Component
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
        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $jumlah = ServiceTransaction::where('cabang_id',getCabangId())->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->count();
        return view('livewire.laporan-servis-data', [
            'jumlah' => $jumlah,
            'users' => $users,
            'services' => $this->search === null ?
                ServiceTransaction::where('cabang_id',getCabangId())->orderBy('tgl_disetujui', 'desc')->where('is_approve', 'Setuju')->whereIn('kondisi_servis', ['Sudah jadi', 'Tidak bisa', 'Dibatalkan'])->paginate($this->paginate) :
                ServiceTransaction::where('cabang_id',getCabangId())->orderBy('tgl_disetujui', 'desc')->where('is_approve', 'Setuju')->whereIn('kondisi_servis', ['Sudah jadi', 'Tidak bisa', 'Dibatalkan'])->where('created_at', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
