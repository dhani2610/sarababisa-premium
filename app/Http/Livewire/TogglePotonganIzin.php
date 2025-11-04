<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class TogglePotonganIzin extends Component
{
    public $nominal_potongan_izin;
    public $nominal_potongan_alfa;
    public $nominal_potongan_sakit;

    public function mount()
    {
        $setting = StoreSetting::find(1);
        if ($setting) {
            $this->nominal_potongan_izin = $setting->nominal_potongan_izin;
            $this->nominal_potongan_alfa = $setting->nominal_potongan_alfa;
            $this->nominal_potongan_sakit = $setting->nominal_potongan_sakit;
        }
    }

    public function render()
    {
        return view('livewire.toggle-potongan-izin');
    }

    public function saveSetting()
    {
        $this->validate([
            'nominal_potongan_izin' => 'nullable|integer|min:0',
            'nominal_potongan_alfa' => 'nullable|integer|min:0',
            'nominal_potongan_sakit' => 'nullable|integer|min:0',
        ]);

        $setting = StoreSetting::find(1);

        if ($setting) {
            $setting->update([
                'nominal_potongan_izin' => $this->nominal_potongan_izin ?: 0,
                'nominal_potongan_alfa' => $this->nominal_potongan_alfa ?: 0,
                'nominal_potongan_sakit' => $this->nominal_potongan_sakit ?: 0,
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Pengaturan potongan berhasil diperbarui.'
            ]);
        }
    }
}
