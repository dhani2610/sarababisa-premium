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
            $this->nominal_potongan_izin = number_format($setting->nominal_potongan_izin, 0, '', '.');
            $this->nominal_potongan_alfa = number_format($setting->nominal_potongan_alfa, 0, '', '.');
            $this->nominal_potongan_sakit = number_format($setting->nominal_potongan_sakit, 0, '', '.');
        }
    }

    public function render()
    {
        return view('livewire.toggle-potongan-izin');
    }

    public function saveSetting()
    {
        $izin = (int) str_replace('.', '', $this->nominal_potongan_izin);
        $alfa = (int) str_replace('.', '', $this->nominal_potongan_alfa);
        $sakit = (int) str_replace('.', '', $this->nominal_potongan_sakit);

        $this->validate([
            'nominal_potongan_izin' => 'nullable',
            'nominal_potongan_alfa' => 'nullable',
            'nominal_potongan_sakit' => 'nullable',
        ]);

        $setting = StoreSetting::find(1);
        if ($setting) {
            $setting->update([
                'nominal_potongan_izin' => $izin,
                'nominal_potongan_alfa' => $alfa,
                'nominal_potongan_sakit' => $sakit,
            ]);
        }

        session()->flash('success', 'Pengaturan potongan berhasil diperbarui.');
        return redirect()->route('sistem');
    }
}
