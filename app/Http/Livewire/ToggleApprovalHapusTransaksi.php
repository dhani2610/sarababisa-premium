<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleApprovalHapusTransaksi extends Component
{
    public $approval_hapus_transaksi = false;

    public function mount()
    {
        $storeSettings = StoreSetting::where('cabang_id', getCabangId())->first();
        $this->approval_hapus_transaksi = (bool) ($storeSettings->approval_hapus_transaksi ?? false);
    }

    public function render()
    {
        return view('livewire.toggle-approval-hapus-transaksi');
    }

    public function saveSetting()
    {
        $storeSettings = StoreSetting::where('cabang_id', getCabangId())->first();
        if ($storeSettings) {
            $storeSettings->update([
                'approval_hapus_transaksi' => $this->approval_hapus_transaksi,
            ]);
        }
    }

    public function updatedApprovalHapusTransaksi()
    {
        $this->saveSetting();
    }
}
