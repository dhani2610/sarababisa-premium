<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleEditTransaksi extends Component
{
    public $is_edit_transaksi = false;

    public function mount()
    {
        $storeSettings = StoreSetting::find(1); // Ganti 1 dengan ID yang sesuai
        $this->is_edit_transaksi = $storeSettings->is_edit_transaksi;
    }

    public function render()
    {
        return view('livewire.toggle-edit-transaksi');
    }

    public function saveSetting()
    {
        $storeSettings = StoreSetting::find(1); // Ganti sesuai kebutuhan
        $storeSettings->update([
            'is_edit_transaksi' => $this->is_edit_transaksi,
        ]);
    }


    public function updatedis_edit_transaksi()
    {
        $storeSettings = StoreSetting::find(1); // Ganti 1 dengan ID yang sesuai
        $storeSettings->update([
            'is_edit_transaksi' => $this->is_edit_transaksi,
        ]);
    }
}
