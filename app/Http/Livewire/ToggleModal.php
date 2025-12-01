<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleModal extends Component
{
    public $modalApplied = false;

    public function mount()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first(); // Ganti 1 dengan ID yang sesuai
        $this->modalApplied = $storeSettings->is_modal;
    }

    public function render()
    {
        return view('livewire.toggle-modal');
    }

    public function updatedModalApplied()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first(); // Ganti 1 dengan ID yang sesuai
        $storeSettings->update([
            'is_modal' => $this->modalApplied,
        ]);
    }
}
