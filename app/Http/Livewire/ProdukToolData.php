<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;

class ProdukToolData extends Component
{
    use WithPagination, LivewireAlert, WithFileUploads;

    public $paginate = 10;
    public $search;


    public $fotoProduk, $produkId;
    public $showFotoModal = false;

    protected $rules = [
        'fotoProduk' => 'required|image|mimes:png,jpg,jpeg,webp|max:1024',
    ];

    protected $messages = [
        'fotoProduk.required' => 'Foto produk wajib diunggah.',
        'fotoProduk.image'    => 'File yang diunggah harus berupa gambar.',
        'fotoProduk.mimes'    => 'Format foto harus PNG, JPG, JPEG, atau WEBP.',
        'fotoProduk.max'      => 'Ukuran foto maksimal 1 MB.',
'fotoProduk.uploaded' => 'Upload foto produk gagal. Pastikan ukuran tidak lebih dari 1 MB.',

    ];


    public function openFotoModal($id)
    {
        $this->produkId = $id;
        $product = Product::find($id);

        if ($product && $product->foto) {
            // kosongkan input upload, tapi simpan info foto lama
            $this->fotoProduk = null;
        } else {
            $this->fotoProduk = null;
        }

        $this->showFotoModal = true;
    }


    public function saveFoto()
    {
        $this->validate();

        $product = Product::find($this->produkId);
        if (!$product) {
            $this->alert('error', 'Produk tidak ditemukan!');
            return;
        }

        $filename = 'produk_' . $this->produkId . '.' . $this->fotoProduk->getClientOriginalExtension();
        $path = $this->fotoProduk->storeAs('produk-foto', $filename, 'public');

        $product->foto = $path;
        $product->save();

        $this->alert('success', 'Foto produk berhasil disimpan!');
        $this->reset(['fotoProduk', 'produkId', 'showFotoModal']);
    }


    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public $barcode;
    public $modalOpen = false;

    public function updatedBarcode($value)
    {
        if ($value) {
            $this->tambahStok($value);
            $this->barcode = ''; // reset input supaya bisa scan lagi
        }
    }

    public function tambahStok($barcode)
    {
        $product = Product::where('product_code', $barcode)->first();
        if ($product) {
            $product->stok += 1;
            $product->save();

            $this->alert('success', 'Berhasil tambah 1 stok produk '.$product->product_name);
        } else {
            $this->alert('error', 'Produk tidak ditemukan!');

            $this->dispatchBrowserEvent('stok-updated', [
                'message' => "Produk dengan barcode $barcode tidak ditemukan!"
            ]);
        }
    }
    public function render()
    {
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        $tools = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '4')->get();
        $tools_count = Product::where('cabang_id',getCabangId())->where('categories_id', '=', '4')->count();
        $toolitemready = Product::where('cabang_id',getCabangId())->where('categories_id', 4)->where('stok', '>', 0)->count();
        $toolstokready = Product::where('cabang_id',getCabangId())->where('categories_id', 4)->where('stok', '>', 0)->sum('stok');
        $toolmodalready = Product::where('cabang_id',getCabangId())->where('categories_id', 4)->where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));
        $toolstokhabis = Product::where('cabang_id',getCabangId())->where('categories_id', 4)->where('stok', 0)->count();
        $toolnominalterjual = Product::where('cabang_id',getCabangId())->where('categories_id', 4)->where('stok', 0)->sum('harga_jual');

        $topProducts = [];

        return view('livewire.produk-tool-data', [
            'topProducts' => $topProducts,
            'toko' => $toko,
            'tools' => $tools,
            'tools_count' => $tools_count,
            'toolitemready' => $toolitemready,
            'toolstokready' => $toolstokready,
            'toolmodalready' => $toolmodalready,
            'toolstokhabis' => $toolstokhabis,
            'toolnominalterjual' => $toolnominalterjual,
            'products' => $this->search === null ?
                Product::where('cabang_id',getCabangId())->latest()->where('categories_id', '=', '4')->paginate($this->paginate) :
                Product::where('cabang_id',getCabangId())->latest()->where('categories_id', '=', '4')->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
