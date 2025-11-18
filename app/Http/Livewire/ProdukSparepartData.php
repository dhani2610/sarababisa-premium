<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;

class ProdukSparepartData extends Component
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
        $spareparts = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '2')->get();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $spareparts_count = Product::where('cabang_id',getCabangId())->where('categories_id', '=', '2')->count();
        $sparepartitemready = Product::where('cabang_id',getCabangId())->where('categories_id', 2)->where('stok', '>', 0)->count();
        $sparepartstokready = Product::where('cabang_id',getCabangId())->where('categories_id', 2)->where('stok', '>', 0)->sum('stok');
        $sparepartmodalready = Product::where('cabang_id',getCabangId())->where('categories_id', 2)->where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));
        $sparepartstokhabis = Product::where('cabang_id',getCabangId())->where('categories_id', 2)->where('stok', 0)->count();
        $sparepartnominalterjual = Product::where('cabang_id',getCabangId())->where('categories_id', 2)->where('stok', 0)->sum('harga_jual');

        $topProducts =[];


        return view('livewire.produk-sparepart-data', [
            'topProducts' => $topProducts,
            'toko' => $toko,
            'spareparts' => $spareparts,
            'model_series' => $model_series,
            'spareparts_count' => $spareparts_count,
            'sparepartitemready' => $sparepartitemready,
            'sparepartstokready' => $sparepartstokready,
            'sparepartmodalready' => $sparepartmodalready,
            'sparepartstokhabis' => $sparepartstokhabis,
            'sparepartnominalterjual' => $sparepartnominalterjual,
            'products' => $this->search === null ?
                Product::where('cabang_id',getCabangId())->latest()->where('categories_id', '=', '2')->paginate($this->paginate) :
                Product::where('cabang_id',getCabangId())->latest()->where('categories_id', '=', '2')->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
