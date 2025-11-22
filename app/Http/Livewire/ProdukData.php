<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use Livewire\Component;
use App\Models\Capacity;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;

class ProdukData extends Component
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

            $this->alert('success', 'Berhasil tambah 1 stok produk ' . $product->product_name);
        } else {
            $this->alert('error', 'Produk tidak ditemukan!');

            $this->dispatchBrowserEvent('stok-updated', [
                'message' => "Produk dengan barcode $barcode tidak ditemukan!"
            ]);
        }
    }

    public function render()
    {
        $categories = Category::all();
        $spareparts = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '2')->get();
        $accessories = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '3')->get();
        $tools = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '4')->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $colors = Color::where('cabang_id',getCabangId())->get();
        $products_count = Product::where('cabang_id',getCabangId())->get()->count();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        $itemready = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->count();
        $stokready = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->sum('stok');
        $modalready = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));
        $stokhabis = Product::where('cabang_id',getCabangId())->where('stok', 0)->count();
        $nominalterjual = Product::where('cabang_id',getCabangId())->where('stok', 0)->sum('harga_jual');

        $topProducts = OrderDetail::where('products.cabang_id',getCabangId())->select(
            'order_details.products_id',
            'products.product_name',
            'products.harga_jual',
            DB::raw('SUM(order_details.quantity) as total_terjual'),
            DB::raw('SUM(order_details.quantity * products.harga_jual) as omzet')
        )
            ->join('products', 'products.id', '=', 'order_details.products_id')
            ->groupBy('order_details.products_id', 'products.product_name', 'products.harga_jual')
            ->orderByDesc('total_terjual')
            ->limit('5')

            ->get();


        $productCategoty = Category::orderBy('created_at', 'asc')->get();
        return view('livewire.produk-data', [
            'topProducts' => $topProducts,
            'brands' => $brands,
            'capacities' => $capacities,
            'model_series' => $model_series,
            'colors' => $colors,
            'toko' => $toko,
            'categories' => $categories,
            'spareparts' => $spareparts,
            'accessories' => $accessories,
            'tools' => $tools,
            'products_count' => $products_count,
            'itemready' => $itemready,
            'stokready' => $stokready,
            'modalready' => $modalready,
            'stokhabis' => $stokhabis,
            'productCategoty' => $productCategoty,
            'nominalterjual' => $nominalterjual,
            'products' => $this->search === null ?
                Product::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                Product::where('cabang_id',getCabangId())->latest()->where('product_name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
