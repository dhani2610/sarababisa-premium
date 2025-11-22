<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleEditProduk extends Component
{
    public $is_edit_produk = false;

    public function mount()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first(); // Ganti 1 dengan ID yang sesuai
        $this->is_edit_produk = $storeSettings->is_edit_produk;
    }

    public function render()
    {
        return view('livewire.toggle-edit-produk');
    }

    public function saveSetting()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first(); // Ganti sesuai kebutuhan
        $storeSettings->update([
            'is_edit_produk' => $this->is_edit_produk,
        ]);
    }
}
