<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Validation\Rule;

class MasterIzin extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $selected = []; // selected IDs for bulk delete
    public $selectAll = false;

    // form fields
    public $modelId;
    public $user_id;
    public $tipe = 'izin';
    public $keterangan;
    public $tanggal;
    public $nominal_potongan = '0'; // as formatted string (e.g. "1.000.000")

    protected function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'tipe' => ['required', Rule::in(['izin', 'sakit', 'alfa'])],
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
        ];
    }


    protected $queryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected $listeners = ['setUserId'];

    public function setUserId($value)
    {
        $this->user_id = $value;
    }


    public function render()
    {
        $query = Izin::with('user')->latest();

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })->orWhere('keterangan', 'like', '%' . $this->search . '%');
        }

        $count = $query->count();

        return view('livewire.master-izin', [
            'izins' => $query->paginate($this->paginate),
            'users' => User::select('id', 'name')
                ->whereIn('role', ['Admin Toko', 'Teknisi', 'Sales'])
                ->orderBy('name')
                ->get(),

            'count' => $count,
        ]);
    }

    // reset form
    public function resetForm()
    {
        $this->reset(['modelId', 'user_id', 'tipe', 'keterangan', 'tanggal', 'nominal_potongan']);
        $this->tipe = 'izin';
        $this->nominal_potongan = '0';
        $this->resetValidation();
    }

    // open create modal (handled in blade by wiring to this)
    public function create()
    {
        $this->resetForm();
        $this->dispatchBrowserEvent('openModalCreate');
    }

    public function store()
    {
        $this->validate();

        // sanitize nominal_potongan: remove non-digit
        $numeric = preg_replace('/\D/', '', $this->nominal_potongan);
        $nominal = $numeric === '' ? 0 : (int)$numeric;

        Izin::create([
            'user_id' => $this->user_id,
            'tipe' => $this->tipe,
            'keterangan' => $this->keterangan,
            'tanggal' => $this->tanggal,
            'nominal_potongan' => $nominal,
        ]);

        session()->flash('message', 'Data izin berhasil ditambahkan.');
        $this->dispatchBrowserEvent('closeModalCreate');
        $this->resetForm();
    }

    public function edit($id)
    {
        $item = Izin::findOrFail($id);
        $this->modelId = $item->id;
        $this->user_id = $item->user_id;
        $this->tipe = $item->tipe;
        $this->keterangan = $item->keterangan;
        $this->tanggal = $item->tanggal->format('Y-m-d');
        $this->nominal_potongan = number_format($item->nominal_potongan, 0, ',', '.');
        $this->dispatchBrowserEvent('openModalEdit');
    }

    public function update()
    {
        $this->validate();

        $item = Izin::findOrFail($this->modelId);

        $numeric = preg_replace('/\D/', '', $this->nominal_potongan);
        $nominal = $numeric === '' ? 0 : (int)$numeric;

        $item->update([
            'user_id' => $this->user_id,
            'tipe' => $this->tipe,
            'keterangan' => $this->keterangan,
            'tanggal' => $this->tanggal,
            'nominal_potongan' => $nominal,
        ]);

        session()->flash('message', 'Data izin berhasil diupdate.');
        $this->dispatchBrowserEvent('closeModalEdit');
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('openConfirmDelete', ['id' => $id]);
    }

    public function delete($id)
    {
        $item = Izin::findOrFail($id);
        $item->delete();
        session()->flash('message', 'Data izin berhasil dihapus.');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = Izin::pluck('id')->map(fn($v) => (string)$v)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            session()->flash('message', 'Tidak ada data yang dipilih.');
            return;
        }
        Izin::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('message', 'Data terpilih berhasil dihapus.');
    }
}
