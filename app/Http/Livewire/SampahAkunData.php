<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SampahAkunData extends Component
{
    use WithPagination;

    public $paginate = 10;

    public function render()
    {
        $sampahAkun = User::where('cabang_id',getCabangId())->onlyTrashed()->get();
        $users_count = $sampahAkun->where('cabang_id',getCabangId())->whereNotNull('deleted_at')->count();

        return view('livewire.sampah-akun-data', [
            'users_count' => $users_count,
            'users' => User::where('cabang_id',getCabangId())->onlyTrashed()->latest()->paginate($this->paginate)
        ]);
    }
}
