<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleFinancialSettings extends Component
{
    // Group Servis
    public $is_profit;
    public $is_modal;
    public $is_bonus;

    // Group Produk
    public $is_profit_produk;
    public $is_modal_produk;
    public $is_bonus_produk;

    public function mount()
    {
        $settings = StoreSetting::where('cabang_id', getCabangId())->first();

        if ($settings) {
            // Mapping Data Servis
            $this->is_profit = $settings->is_profit;
            $this->is_modal  = $settings->is_modal;
            $this->is_bonus  = $settings->is_bonus;

            // Mapping Data Produk
            $this->is_profit_produk = $settings->is_profit_produk;
            $this->is_modal_produk  = $settings->is_modal_produk;
            $this->is_bonus_produk  = $settings->is_bonus_produk;
        }
    }

    // Magic Method: Berjalan otomatis setiap kali ada properti yang berubah
    public function updated($propertyName)
    {
        // Update kolom database sesuai nama properti variable
        StoreSetting::where('cabang_id', getCabangId())
            ->update([
                $propertyName => $this->{$propertyName}
            ]);

        // Opsional: Kirim notifikasi
        $this->dispatchBrowserEvent('notify', ['message' => 'Pengaturan berhasil disimpan!']);
    }

    public function render()
    {
        return view('livewire.toggle-financial-settings');
    }
}
