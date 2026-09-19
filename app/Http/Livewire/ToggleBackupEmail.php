<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleBackupEmail extends Component
{
    public $backup_email;

    public function mount()
    {
        $setting = StoreSetting::where('cabang_id', getCabangId())->first();
        $this->backup_email = $setting ? $setting->backup_email : null;
    }

    public function render()
    {
        return view('livewire.toggle-backup-email');
    }

    public function saveSetting()
    {
        $this->validate([
            'backup_email' => 'nullable|email',
        ]);

        $setting = StoreSetting::where('cabang_id', getCabangId())->first();
        if ($setting) {
            $setting->update([
                'backup_email' => $this->backup_email ?: null,
            ]);
        }

        session()->flash('success', 'Email penerima backup berhasil diperbarui.');
        $this->emit('notify', ['message' => 'Email penerima backup berhasil disimpan.']);
    }
}
