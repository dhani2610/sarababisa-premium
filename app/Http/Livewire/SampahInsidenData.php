<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Incident;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class SampahInsidenData extends Component
{
    use WithPagination;

    public $paginate = 10;

    public function render()
    {
        $items = Incident::where('cabang_id',getCabangId())->onlyTrashed()->get();
        $items_count = $items->where('cabang_id',getCabangId())->whereNotNull('deleted_at')->count();

        return view('livewire.sampah-insiden-data', [
            'items_count' => $items_count,
            'items' => Incident::where('cabang_id',getCabangId())->onlyTrashed()->latest()->paginate($this->paginate)
        ]);
    }
}
