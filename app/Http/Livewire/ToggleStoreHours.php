<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleStoreHours extends Component
{
    public $is_close = false; // Default false (boolean untuk UI)
    public $time_open_toko;
    public $time_close_toko;

    public function mount()
    {
        $settings = StoreSetting::where('cabang_id', getCabangId())->first();

        if ($settings) {
            // Convert Integer (0/1) dari DB ke Boolean (false/true) untuk Toggle UI
            $this->is_close = $settings->is_close === 1;

            // Format jam agar terbaca oleh input type="time" (H:i)
            $this->time_open_toko = $settings->time_open_toko ? date('H:i', strtotime($settings->time_open_toko)) : null;
            $this->time_close_toko = $settings->time_close_toko ? date('H:i', strtotime($settings->time_close_toko)) : null;
        }
    }

    public function updated($propertyName)
    {
        // Persiapan data yang akan disimpan
        $dataToUpdate = [];

        if ($propertyName === 'is_close') {
            // Convert Boolean UI kembali ke Integer untuk Database
            $dataToUpdate['is_close'] = $this->is_close ? 1 : 0;
        } else {
            // Untuk time_open dan time_close langsung simpan value-nya
            $dataToUpdate[$propertyName] = $this->{$propertyName};
        }

        StoreSetting::where('cabang_id', getCabangId())
            ->update($dataToUpdate);

        $this->dispatchBrowserEvent('notify', ['message' => 'Pengaturan jam operasional tersimpan!']);
    }

    public function render()
    {
        return view('livewire.toggle-store-hours');
    }
}
