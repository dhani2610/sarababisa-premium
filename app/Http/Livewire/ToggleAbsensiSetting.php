<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleAbsensiSetting extends Component
{
    public $active_setting_absensi;
    public $jam_masuk;
    public $jam_pulang;

    public function mount()
    {
        $setting = StoreSetting::find(1);
        $this->active_setting_absensi = $setting->active_setting_absensi;
        $this->jam_masuk = $setting->jam_masuk;
        $this->jam_pulang = $setting->jam_pulang;
    }

    public function render()
    {
        return view('livewire.toggle-absensi-setting');
    }

    public function updatedActiveSettingAbsensi($value)
    {
        // Jika nonaktif, kosongkan jam masuk/pulang
        if (!$value) {
            $this->jam_masuk = null;
            $this->jam_pulang = null;
        }
    }

    public function saveSetting()
    {
        $setting = StoreSetting::find(1);

        $setting->update([
            'active_setting_absensi' => $this->active_setting_absensi,
            'jam_masuk' => $this->active_setting_absensi ? $this->jam_masuk : null,
            'jam_pulang' => $this->active_setting_absensi ? $this->jam_pulang : null,
        ]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Pengaturan absensi berhasil disimpan.'
        ]);
    }
}
