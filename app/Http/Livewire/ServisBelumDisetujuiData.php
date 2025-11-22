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
    public $type;

    public function mount()
    {
        $this->type = Type::where('cabang_id',getCabangId())->pluck('id')->toArray();
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
        $toko = User::find(1);
        $types = Type::where('cabang_id',getCabangId())->get();
        $processes_count = ServiceTransaction::where('cabang_id',getCabangId())->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->count();
        $jumlah_belum_disetujui = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->where('is_approve', '=', null)->count();
        return view('livewire.servis-belum-disetujui-data', [
            'toko' => $toko,
            'types' => $types,
            'processes_count' => $processes_count,
            'jumlah_bisa_diambil' => $jumlah_bisa_diambil,
            'jumlah_sudah_diambil' => $jumlah_sudah_diambil,
            'jumlah_belum_disetujui' => $jumlah_belum_disetujui,
            'service_transactions' => ServiceTransaction::where('cabang_id',getCabangId())->orderBy('tgl_ambil', 'desc')->when($this->search, function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->where('is_approve', null)->orWhere('nomor_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->where('is_approve', null)->orWhere('tindakan_servis', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->where('is_approve', null)->orWhere('nama_barang', 'like', '%' . $this->search . '%')->where('status_servis', 'Sudah Diambil')->where('is_approve', null);
            })->when($this->type, function ($q) {
                $q->whereIn('types_id', $this->type);
            })->when($this->kondisi, function ($q) {
                $q->whereIn('kondisi_servis', $this->kondisi)->where('status_servis', 'Sudah Diambil')->where('is_approve', null);
            })->paginate($this->paginate),
        ]);
    }
}
