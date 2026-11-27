<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleAbsensiSetting extends Component
{
    public $active_setting_absensi;
    public $jam_masuk;
    public $jam_pulang;
    public $hari_absen = [];
    public $nominal_potongan_tidak_absen = 0;

    public $list_hari = [
        'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
    ];

    public function mount()
    {
        $setting = StoreSetting::where('cabang_id', getCabangId())->first();
        if ($setting) {
            $this->active_setting_absensi = (bool) $setting->active_setting_absensi;
            $this->jam_masuk = $setting->jam_masuk;
            $this->jam_pulang = $setting->jam_pulang;
            $this->hari_absen = is_array($setting->hari_absen) 
                ? $setting->hari_absen 
                : (json_decode($setting->hari_absen, true) ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $this->nominal_potongan_tidak_absen = $setting->nominal_potongan_tidak_absen ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.toggle-absensi-setting');
    }

    public function updatedActiveSettingAbsensi($value)
    {
        if (!$value) {
            $this->jam_masuk = null;
            $this->jam_pulang = null;
        }
    }

    public function saveSetting()
    {
        $setting = StoreSetting::where('cabang_id', getCabangId())->first();

        if ($setting) {
            $nominal = (int) str_replace(['.', ','], '', (string) $this->nominal_potongan_tidak_absen);

            $setting->update([
                'active_setting_absensi' => $this->active_setting_absensi ? 1 : 0,
                'jam_masuk' => $this->active_setting_absensi ? $this->jam_masuk : null,
                'jam_pulang' => $this->active_setting_absensi ? $this->jam_pulang : null,
                'hari_absen' => json_encode($this->hari_absen),
                'nominal_potongan_tidak_absen' => $nominal,
            ]);
        }

        session()->flash('success', 'Pengaturan absensi berhasil disimpan.');
        return redirect()->route('sistem');
    }
}
