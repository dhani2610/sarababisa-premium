<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\Capacity;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\HandphoneExport;
use App\Imports\HandphoneImport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\HandphoneRequest;

class ProdukHandphoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/kepalatoko/produk/handphone',compact('toko'));
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Product::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiOrder');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data produk yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        Product::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data produk berhasil dihapus.']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HandphoneRequest $request)
    {
        $namakategori = Category::find($request->categories_id);

        $merek = Brand::find($request->brands_id);
        $model = ModelSerie::find($request->model_series_id);

        $productName = $merek->name . ' ' . $model->name;

        // Create product
        Product::create([
            'product_name' => $productName,
            'product_code' => $request->product_code,
            'categories_id' => $request->categories_id,
            'category_name' => $namakategori->category_name,
            'warna' => $request->warna,
            'kondisi' => $request->kondisi,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'capacities_id' => $request->capacities_id,
            'ram' => $request->ram,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual_toko' => $request->harga_jual_toko,
            'harga_jual' => $request->harga_jual,
            'keterangan' => $request->keterangan,
            'nomor_seri' => $request->nomor_seri,
            'garansi' => $request->garansi,
            'garansi_imei' => $request->garansi_imei,
            'ppn' => $request->ppn,
            'cabang_id' => getCabangId(),
        ]);

        // return redirect()->route('handphone.index');
        toast('Data berhasil disimpan.', 'success');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ProdukData', $namafile);
        Excel::import(new HandphoneImport, \public_path('/ProdukData/' . $namafile));
        return redirect()->route('handphone.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new HandphoneExport, 'handphone.xlsx');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Product::where('cabang_id',getCabangId())->findOrFail($id);
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $colors = Color::where('cabang_id',getCabangId())->get();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages.kepalatoko.produk.handphone-edit', [
            'item' => $item,
            'brands' => $brands,
            'model_series' => $model_series,
            'capacities' => $capacities,
            'colors' => $colors,
            'toko' => $toko
        ]);
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $pilihan = $request->stok;

        // Mengambil data produk habis
        $empty_products = Product::where('categories_id', 1)->where('stok', 0)->get();

        // Menghitung data produk habis
        $jumlah_item_habis = Product::where('categories_id', 1)->where('stok', 0)->count();

        // Mengambil data produk tersedia
        $available_products = Product::where('categories_id', 1)->where('stok', '>', 0)->get();

        // Menghitung data produk tersedia
        $jumlah_item_tersedia = Product::where('categories_id', 1)->where('stok', '>', 0)->count();

        $modal_stok_tersedia = Product::where('categories_id', 1)->where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));

        // Menghitung stok produk tersedia
        $jumlah_stok_tersedia = Product::where('categories_id', 1)->where('stok', '>', 0)->sum('stok');

        if ($pilihan === "tersedia") {
            $products = $available_products;
        } else {
            $products = $empty_products;
        }

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-produk-handphone', [
            'users' => $users,
            'imagePath' => $imagePath,
            'products' => $products,
            'pilihan' => $pilihan,
            'jumlah_item_habis' => $jumlah_item_habis,
            'jumlah_item_tersedia' => $jumlah_item_tersedia,
            'jumlah_stok_tersedia' => $jumlah_stok_tersedia,
            'modal_stok_tersedia' => $modal_stok_tersedia,
        ]);

        $filename = 'Laporan Produk Handphone.pdf';

        return $pdf->download($filename);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = Product::findOrFail($id);
        $namakategori = Category::find($request->categories_id);
        $merek = Brand::find($request->brands_id);
        $model = ModelSerie::find($request->model_series_id);

        $productName = $merek->name . ' ' . $model->name;
        // Create product
        $item->update([
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'product_name' => $productName,
            'ram' => $request->ram,
            'capacities_id' => $request->capacities_id,
            'warna' => $request->warna,
            'kondisi' => $request->kondisi,
            'product_code' => $request->product_code,
            'categories_id' => $request->categories_id,
            'category_name' => $namakategori->category_name,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_jual_toko' => $request->harga_jual_toko,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'keterangan' => $request->keterangan,
            'nomor_seri' => $request->nomor_seri,
            'garansi' => $request->garansi,
            'garansi_imei' => $request->garansi_imei,
            'ppn' => $request->ppn
        ]);

        // dd($request->all(),$item);


        return redirect()->route('handphone.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Product::findOrFail($id);

        if (
            $item->relasiOrder()->exists()
        ) {
            toast('Data Produk yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Produk berhasil dihapus.', 'success');

        return redirect()->route('handphone.index');
    }
}
