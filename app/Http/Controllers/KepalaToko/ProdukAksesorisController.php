<?php

namespace App\Http\Controllers\KepalaToko;

use App\Exports\AksesorisExport;
use App\Models\Product;
use App\Models\Category;
use App\Models\ModelSerie;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\ProductRequest;
use App\Imports\AksesorisImport;
use Maatwebsite\Excel\Facades\Excel;

class ProdukAksesorisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/produk/aksesoris');
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
    public function store(ProductRequest $request)
    {
        $namakategori = Category::find($request->categories_id);

        // Create product
        Product::create([
            'product_name' => $request->product_name,
            'product_code' => $request->product_code,
            'categories_id' => $request->categories_id,
            'sub_categories_id' => $request->sub_categories_id,
            'category_name' => $namakategori->category_name,
            'model_series_id' => $request->model_series_id,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'keterangan' => $request->keterangan,
            'garansi' => $request->garansi,
            'ppn' => $request->ppn
        ]);

        // return redirect()->route('aksesoris.index');
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
        Excel::import(new AksesorisImport, \public_path('/ProdukData/' . $namafile));
        return redirect()->route('aksesoris.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new AksesorisExport, 'aksesoris.xlsx');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Product::findOrFail($id);
        $accessories = SubCategory::where('categories_id', '=', '3')->get();
        $model_series = ModelSerie::all();
        $toko = StoreSetting::find(1);

        return view('pages.kepalatoko.produk.aksesoris-edit', [
            'item' => $item,
            'accessories' => $accessories,
            'model_series' => $model_series,
            'toko' => $toko
        ]);
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
        // Create product
        $item->update([
            'model_series_id' => $request->model_series_id,
            'product_name' => $request->product_name,
            'product_code' => $request->product_code,
            'categories_id' => $request->categories_id,
            'sub_categories_id' => $request->sub_categories_id,
            'category_name' => $namakategori->category_name,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'keterangan' => $request->keterangan,
            'garansi' => $request->garansi,
            'ppn' => $request->ppn
        ]);

        return redirect()->route('aksesoris.index');
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

        return redirect()->route('aksesoris.index');
    }
}
