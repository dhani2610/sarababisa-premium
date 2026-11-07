<?php

namespace App\Http\Livewire;

use App\Models\Type;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceTransaction;

class ServisBelumDisetujuiData extends Component
{
    use WithPagination;

    public $paginate = 50;
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

    public function render()
    {
        $cabangId = getCabangId();
        $toko = User::find(1);
        $types = Type::select('id', 'name')->where('cabang_id', $cabangId)->get();
        $counts = ServiceTransaction::where('cabang_id', $cabangId)
            ->selectRaw("
                SUM(CASE WHEN status_servis NOT IN ('Bisa Diambil', 'Sudah Diambil') THEN 1 ELSE 0 END) as processes_count,
                SUM(CASE WHEN status_servis = 'Bisa Diambil' THEN 1 ELSE 0 END) as jumlah_bisa_diambil,
                SUM(CASE WHEN status_servis = 'Sudah Diambil' THEN 1 ELSE 0 END) as jumlah_sudah_diambil,
                SUM(CASE WHEN status_servis = 'Sudah Diambil' AND is_approve IS NULL THEN 1 ELSE 0 END) as jumlah_belum_disetujui
            ")
            ->first();

        return view('livewire.servis-belum-disetujui-data', [
            'toko' => $toko,
            'types' => $types,
            'processes_count' => (int) ($counts->processes_count ?? 0),
            'jumlah_bisa_diambil' => (int) ($counts->jumlah_bisa_diambil ?? 0),
            'jumlah_sudah_diambil' => (int) ($counts->jumlah_sudah_diambil ?? 0),
            'jumlah_belum_disetujui' => (int) ($counts->jumlah_belum_disetujui ?? 0),
            'service_transactions' => collect(),
        ]);
    }
}
