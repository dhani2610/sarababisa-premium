<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleSettingOvertime extends Component
{
    public $nominal_overtime;

    public function mount()
    {
        $setting = StoreSetting::find(1);
        if ($setting) {
            $this->nominal_overtime = number_format($setting->nominal_overtime, 0, '', '.');
        }
    }

    public function render()
    {
        return view('livewire.toggle-setting-overtime');
    }

    public function saveSetting()
    {
        $izin = (int) str_replace('.', '', $this->nominal_overtime);

        $this->validate([
            'nominal_overtime' => 'nullable',
        ]);

        $setting = StoreSetting::find(1);
        if ($setting) {
            $setting->update([
                'nominal_overtime' => $izin,
            ]);
        }

        session()->flash('success', 'Pengaturan potongan berhasil diperbarui.');
        return redirect()->route('sistem');
    }
}
