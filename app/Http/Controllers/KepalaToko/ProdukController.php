<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Exports\ProdukExport;
use App\Imports\ProdukImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/produk/index');
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


    // public function downloadBarcode($id)
    // {
    //     $product = Product::findOrFail($id);

    //     if (!$product->product_code) {
    //         return redirect()->back()->with('error', 'Produk ini belum memiliki kode produk.');
    //     }

    //     // Buat barcode PNG
    //     $generator = new BarcodeGeneratorPNG();
    //     $barcodeData = $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128);

    //     // Simpan ke file sementara
    //     $fileName = 'barcode_' . $product->product_code .'-'.$product->product_name. '.png';
    //     $filePath = storage_path('app/public/' . $fileName);
    //     file_put_contents($filePath, $barcodeData);

    //     // Download file
    //     return response()->download($filePath)->deleteFileAfterSend(true);
    // }

    public function downloadBarcode($id)
    {
        $product = Product::findOrFail($id);

        if (!$product->product_code) {
            return redirect()->back()->with('error', 'Produk ini belum memiliki kode produk.');
        }

        $generator = new BarcodeGeneratorPNG();
        $barcodeData = base64_encode(
            $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128)
        );

        // Buat PDF dengan view
        $pdf = Pdf::loadView('pages.kepalatoko.cetak-barcode', [
        // return view('pages.kepalatoko.cetak-barcode', [
            'product' => $product,
            'barcodeData' => $barcodeData
        ])->setPaper([0, 0, 226.77, 141.73]); // ukuran kertas kecil (80x50mm)
        

        return $pdf->stream('barcode_' . $product->product_code . '.pdf');
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
    public function store(Request $request)
    {
        //
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Product::findOrFail($id);
        $categories = SubCategory::all();
        $toko = StoreSetting::find(1);

        return view('pages.kepalatoko.produk.edit', [
            'item' => $item,
            'categories' => $categories,
            'toko' => $toko
        ]);
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ProdukData', $namafile);
        Excel::import(new ProdukImport, \public_path('/ProdukData/' . $namafile));
        return redirect()->route('item.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new ProdukExport, 'produk.xlsx');
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $pilihan = $request->stok;

        // Mengambil data produk habis
        $empty_products = Product::where('stok', 0)->get();

        // Menghitung data produk habis
        $jumlah_item_habis = Product::where('stok', 0)->count();

        // Mengambil data produk tersedia
        $available_products = Product::where('stok', '>', 0)->get();

        // Menghitung data produk tersedia
        $jumlah_item_tersedia = Product::where('stok', '>', 0)->count();

        $modal_stok_tersedia = Product::where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));

        // Menghitung stok produk tersedia
        $jumlah_stok_tersedia = Product::where('stok', '>', 0)->sum('stok');

        if ($pilihan === "tersedia") {
            $products = $available_products;
        } else {
            $products = $empty_products;
        }

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-produk', [
            'users' => $users,
            'imagePath' => $imagePath,
            'products' => $products,
            'pilihan' => $pilihan,
            'jumlah_item_habis' => $jumlah_item_habis,
            'jumlah_item_tersedia' => $jumlah_item_tersedia,
            'jumlah_stok_tersedia' => $jumlah_stok_tersedia,
            'modal_stok_tersedia' => $modal_stok_tersedia,
        ]);

        $filename = 'Laporan Produk.pdf';

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
        // Create product
        $item->update([
            'product_name' => $request->product_name,
            'product_code' => $request->product_code,
            'sub_categories_id' => $request->sub_categories_id,
            'category_name' => $namakategori->category_name,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'keterangan' => $request->keterangan,
            'nomor_seri' => $request->nomor_seri,
            'garansi' => $request->garansi,
            'garansi_imei' => $request->garansi_imei,
            'ppn' => $request->ppn
        ]);

        return redirect()->route('item.index');
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

        return redirect()->route('item.index');
    }
}
