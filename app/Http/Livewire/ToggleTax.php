<?php

namespace App\Http\Livewire;

use App\Models\StoreSetting;
use Livewire\Component;

class ToggleTax extends Component
{
    public $taxApplied = false;
    public $ppn;

    public function mount()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first();
        $this->taxApplied = $storeSettings->is_tax;
        $this->ppn = $storeSettings->ppn; // ambil dari kolom ppn
    }

    public function render()
    {
        return view('livewire.toggle-tax');
    }

    public function updatedTaxApplied()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first();
        $storeSettings->update([
            'is_tax' => $this->taxApplied,
        ]);
    }

    public function updatedPpn()
    {
        $storeSettings = StoreSetting::where('cabang_id',getCabangId())->first();
        $storeSettings->update([
            'ppn' => $this->ppn, // simpan ke kolom ppn
        ]);
    }
}
