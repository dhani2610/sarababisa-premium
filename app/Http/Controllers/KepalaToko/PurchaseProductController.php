<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Capacity;
use App\Models\ModelSerie;
use App\Models\Color;
use App\Models\User;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Brand;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Barryvdh\DomPDF\Facade\Pdf;
class PurchaseProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/pembelian/index');
    }

    public function cetak(Request $request)
    {
        // 1. Ambil data Toko/User (Sesuaikan ID user pemilik toko)
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // 2. Filter Tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // 3. Query Data Pembelian
        // Logic disamakan dengan index: exclude 'Tukar Tambah'
        $query = Purchase::with(['product', 'supplier']) // Eager load biar cepat
            ->where('cabang_id', getCabangId())
            ->whereDate('date', '>=', $start_date) // Menggunakan kolom 'date' sesuai tampilan tabel
            ->whereDate('date', '<=', $end_date)
            ->where(function ($q) {
                $q->whereNull('keterangan')
                  ->orWhere('keterangan', '!=', 'Tukar Tambah');
            })
            ->orderBy('date', 'asc');

        $purchases = $query->get();

        // 4. Hitung Ringkasan (Summary)
        $total_item = $query->sum('quantity');
        $total_pembelian = $query->sum('total_price');

        // 5. Generate PDF
        $pdf = Pdf::loadView('pages.kepalatoko.pembelian.cetak-pdf', [
            'users' => $users,
            'imagePath' => $imagePath,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'purchases' => $purchases,
            'total_item' => $total_item,
            'total_pembelian' => $total_pembelian
        ]);

        $filename = 'Laporan Pembelian ' . $start_date . ' sd ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        // Dapatkan data pembelian yang akan dihapus
        $purchases = Purchase::whereIn('id', $selectedIds)->get();

        foreach ($purchases as $item) {
            // Kurangi stok produk terkait
            $products = $item->product;
            $products->stok -= $item->quantity;
            $products->save();
        }

        Purchase::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data pembelian produk berhasil dihapus.']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::where('cabang_id',getCabangId())->get();
        $products = Product::where('cabang_id',getCabangId())->get();
        $categories = Category::all();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $colors = Color::where('cabang_id',getCabangId())->get();
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $spareparts = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '2')->get();
        $accessories = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '3')->get();
        $tools = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '4')->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        return view('pages/kepalatoko/pembelian/create', compact('customers','suppliers', 'products', 'categories','capacities','model_series','colors','spareparts','accessories','tools','brands','toko'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    function cleanNumber($value)
    {
        return (int) str_replace(['.', ','], '', $value);
    }
    // public function store(Request $request)
    // {
    //     function cleanNumber($value)
    //     {
    //         return (int) str_replace(['.', ','], '', $value);
    //     }
    //     if ($request->products_id == null) {

    //         $notification = array(
    //             'message' => 'Sorry you do not select any item',
    //             'alert-type' => 'error'
    //         );
    //         return redirect()->back()->with($notification);
    //     } else {
    //         $count_product = count($request->products_id);
    //         for ($i = 0; $i < $count_product; $i++) {

    //             $tipe = $request->tipe_select[$i];
    //             $purchase = new Purchase();
    //             $purchase->date = date('Y-m-d', strtotime($request->date[$i]));
    //             $purchase->reference_number = $request->reference_number[$i];
    //             $purchase->suppliers_id = $request->suppliers_id[$i];

    //             $purchase->products_id = $request->products_id[$i];
    //             $purchase->quantity = $request->quantity[$i];
    //             $purchase->product_price = $request->product_price[$i];
    //             $purchase->total_price = cleanNumber($request->total_price[$i]);
    //             $purchase->keterangan = $request->keterangan[$i];

    //             $product_name = Product::find($purchase->products_id[$i]);

    //             if ($tipe == 'Pelanggan') {
    //                 $suppliers_name = Customer::find($request->suppliers_id[$i]);
    //                 // dd($suppliers_name,$request->suppliers_id[$i]);
    //                 $purchase->suppliers_name = $suppliers_name->nama;
    //             }else{
    //                 $suppliers_name = Supplier::find($purchase->suppliers_id);
    //                 $purchase->suppliers_name = $suppliers_name->name;
    //             }

    //             $purchase->product_name = $product_name->product_name ?? '-';
    //             $purchase->cabang_id = getCabangId();

    //             $purchase->save();

    //             $products = Product::find($purchase->products_id[$i]);
    //             if (!empty($request->nomor_seri[$i])) {
    //                 // dd($products->nomor_seri == $request->nomor_seri[$i],$products->nomor_seri,$request->nomor_seri[$i]);
    //                 if ($products->nomor_seri == $request->nomor_seri[$i]) {
    //                     // add new stock to the product
    //                     $products->stok += $request->quantity[$i];
    //                     $products->save();
    //                 }else{
    //                     $namakategori = Category::find(1);

    //                     $productsNew = new Product();
    //                     $productsNew->product_name = $product_name->product_name ?? '-';
    //                     $productsNew->categories_id = 1;
    //                     $productsNew->category_name = $namakategori->category_name;
    //                     $productsNew->capacities_id = $request->capacities_id[$i];
    //                     $productsNew->harga_modal = $request->product_price[$i];
    //                     $productsNew->harga_jual = $request->harga_jual_pelanggan[$i];
    //                     $productsNew->harga_jual_toko = $request->harga_jual_toko[$i];
    //                     $productsNew->ram = $request->ram[$i];
    //                     $productsNew->warna = $request->warna[$i];
    //                     $productsNew->nomor_seri = $request->nomor_seri[$i];
    //                     $productsNew->stok_minimal = 1;
    //                     $productsNew->stok = $request->quantity[$i];
    //                     $productsNew->keterangan = $request->keterangan[$i];
    //                     $productsNew->cabang_id = getCabangId();
    //                     $productsNew->save();
    //                 }
    //             }else{
    //                 // add new stock to the product
    //                 $products->stok += $request->quantity[$i];
    //                 $products->save();
    //             }


    //             if ($request->product_price[$i] > 0) {
    //                 # code...
    //                 Expense::create([
    //                     'name' => 'Pembelian produk '. $purchase->product_name,
    //                     'price' => cleanNumber($purchase->total_price),
    //                     'users_id' => auth()->user()->id
    //                 ]);
    //             }

    //         }

    //     }

    //     return redirect()->route('purchase.index');
    // }


    public function store(Request $request)
    {
        // Fungsi helper untuk membersihkan format angka
        if (!function_exists('cleanNumber')) {
            function cleanNumber($value)
            {
                return (int) str_replace(['.', ','], '', $value);
            }
        }

        if ($request->products_id == null) {
            $notification = array(
                'message' => 'Sorry you do not select any item',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        } else {
            $count_product = count($request->products_id);

            for ($i = 0; $i < $count_product; $i++) {

                $tipe = $request->tipe_select[$i];

                // Ambil data produk di awal loop agar efisien
                $productModel = Product::find($request->products_id[$i]);

                $purchase = new Purchase();
                $purchase->date = date('Y-m-d', strtotime($request->date[$i]));
                $purchase->reference_number = $request->reference_number[$i];
                $purchase->suppliers_id = $request->suppliers_id[$i];
                $purchase->products_id = $request->products_id[$i];
                $purchase->quantity = $request->quantity[$i];
                $purchase->product_price = $request->product_price[$i];
                $purchase->total_price = cleanNumber($request->total_price[$i]);
                $purchase->keterangan = $request->keterangan[$i];

                // Set Nama Supplier/Pelanggan
                if ($tipe == 'Pelanggan') {
                    $customer = Customer::find($request->suppliers_id[$i]);
                    $purchase->suppliers_name = $customer->nama;
                } else {
                    // Asumsi suppliers_id sudah single value dari input array
                    $supplier = Supplier::find($request->suppliers_id[$i]);
                    $purchase->suppliers_name = $supplier->name;
                }

                $purchase->product_name = $productModel->product_name ?? '-';
                $purchase->cabang_id = getCabangId();
                // dd($request->all(),$productModel,$request->nomor_seri[$i],$productModel->nomor_seri == $request->nomor_seri[$i]);
                $purchase->save();

                // LOGIKA UPDATE STOK ATAU BUAT PRODUK BARU
                if (!empty($request->nomor_seri[$i])) {

                    // Cek apakah nomor seri sama dengan produk yang dipilih
                    if ($productModel->nomor_seri == $request->nomor_seri[$i]) {
                        // Jika sama, tambahkan stok ke produk tersebut
                        $productModel->stok += $request->quantity[$i];
                        $productModel->save();
                    } else {
                        // Jika beda, BUAT PRODUK BARU (Duplikat Data)
                        $namakategori = Category::find(1);

                        // --- [MULAI] LOGIKA PENGECEKAN NAMA DUPLIKAT ---
                        $baseName = $productModel->product_name ?? '-';
                        $finalName = $baseName;

                        // Selama nama tersebut masih ada di database, tambahkan titik (.)
                        // Gunakan withTrashed() jika ingin mengecek data yang sudah dihapus juga
                        // while (Product::where('product_name', $finalName)->exists()) {
                        //     $finalName = $finalName . '.';
                        // }
                        // --- [SELESAI] LOGIKA PENGECEKAN NAMA DUPLIKAT ---


                        // --- [MULAI] LOGIKA PENGECEKAN NOMOR SERI DUPLIKAT (BARU) ---
                        $baseNomorSeri = $request->nomor_seri[$i];
                        $finalNomorSeri = $baseNomorSeri;

                        // Pastikan nomor seri tidak kosong sebelum dicek
                        if (!empty($finalNomorSeri)) {
                            while (Product::where('nomor_seri', $finalNomorSeri)->exists()) {
                                $finalNomorSeri = $finalNomorSeri . '.';
                            }
                        }
                        // --- [SELESAI] LOGIKA PENGECEKAN NOMOR SERI DUPLIKAT ---

                        $productsNew = new Product();
                        $productsNew->product_name = $baseName; // Pakai nama yang sudah ada titiknya
                        $productsNew->categories_id = 1;
                        $productsNew->warna = $productModel->warna;
                        $productsNew->kondisi = $productModel->kondisi;
                        $productsNew->brands_id = $productModel->brands_id;
                        $productsNew->model_series_id = $productModel->model_series_id;
                        $productsNew->capacities_id = $productModel->capacities_id;
                        $productsNew->category_name = $namakategori->category_name;
                        $productsNew->capacities_id = $request->capacities_id[$i];
                        $productsNew->harga_modal = $request->product_price[$i];
                        $productsNew->harga_jual = $request->harga_jual_pelanggan[$i];
                        $productsNew->harga_jual_toko = $request->harga_jual_toko[$i];
                        $productsNew->ram = $request->ram[$i];
                        $productsNew->warna = $request->warna[$i];
                        $productsNew->nomor_seri = $finalNomorSeri;
                        $productsNew->stok_minimal = 1;
                        $productsNew->stok = $request->quantity[$i];
                        $productsNew->keterangan = $request->keterangan[$i];
                        $productsNew->cabang_id = getCabangId();

                        $productsNew->save();
                    }
                } else {
                    // Jika tidak ada input nomor seri, update stok produk yang ada
                    $productModel->stok += $request->quantity[$i];
                    $productModel->save();
                }

                // Simpan Pengeluaran (Expense)
                if ($request->product_price[$i] > 0) {
                    Expense::create([
                        'name' => 'Pembelian produk ' . $purchase->product_name,
                        'price' => cleanNumber($purchase->total_price),
                        'users_id' => auth()->user()->id,
                        'cabang_id' => getCabangId()
                    ]);
                }
            }
        }

        return redirect()->route('purchase.index');
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Purchase::findOrFail($id);

        $item->delete();

        $products = Product::find($item->products_id);
        if ($products != null) {
            $products->stok -= $item->quantity;
            $products->save();
        }

        return redirect()->back();
    }
}
