<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceTransaction;
use App\Models\User;

class LaporanServisPajakData extends Component
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
        $users = User::where('role', 'Teknisi')->get();
        $jumlah = ServiceTransaction::where('ppn','>',0)->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->count();
        return view('livewire.laporan-servis-pajak-data', [
            'jumlah' => $jumlah,
            'users' => $users,
            'services' => $this->search === null ?
                ServiceTransaction::where('ppn','>',0)->orderBy('tgl_disetujui', 'desc')->where('is_approve', 'Setuju')->whereIn('kondisi_servis', ['Sudah jadi', 'Tidak bisa', 'Dibatalkan'])->paginate($this->paginate) :
                ServiceTransaction::where('ppn','>',0)->orderBy('tgl_disetujui', 'desc')->where('is_approve', 'Setuju')->whereIn('kondisi_servis', ['Sudah jadi', 'Tidak bisa', 'Dibatalkan'])->where('created_at', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
