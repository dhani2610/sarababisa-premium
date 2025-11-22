<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use Livewire\Component;

class CustomerCreate extends Component
{
    public $nama;
    public $kategori;
    public $nomor_hp;
    public $alamat;


    public function render()
    {
        return view('livewire.customer-create');
    }

    public function messages()
    {
        return [
            'nomor_hp.unique' => 'Mohon maaf, inputan tidak dapat diproses karena pelanggan dengan nomor hp ini sudah tersedia.',
        ];
    }

    public function store()
    {
        $this->validate([
            'nama' => 'required|min:3',
            'kategori' => 'required',
            'alamat' => 'required',
            'nomor_hp' => 'required'
        ]);
        $customer = Customer::create([
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'alamat' => $this->alamat,
            'nomor_hp' => $this->nomor_hp,
            'cabang_id' => getCabangId(),
        ]);

        $this->resetInput();

        $this->emit('customerStored', $customer);

        return redirect()->route('pelanggan.index');
    }

    private function resetInput()
    {
        $this->nama = null;
        $this->kategori = null;
        $this->alamat = null;
        $this->nomor_hp = null;
    }
}
