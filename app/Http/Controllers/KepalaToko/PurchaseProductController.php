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
use App\Models\Customer;

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

        return view('pages/kepalatoko/pembelian/create', compact('customers','suppliers', 'products', 'categories','capacities','model_series','colors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
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
                $purchase = new Purchase();
                $purchase->date = date('Y-m-d', strtotime($request->date[$i]));
                $purchase->reference_number = $request->reference_number[$i];
                $purchase->suppliers_id = $request->suppliers_id[$i];

                $purchase->products_id = $request->products_id[$i];
                $purchase->quantity = $request->quantity[$i];
                $purchase->product_price = $request->product_price[$i];
                $purchase->total_price = $request->total_price[$i];
                $purchase->keterangan = $request->keterangan[$i];

                $product_name = Product::find($purchase->products_id);
                if ($tipe == 'Pelanggan') {
                    $suppliers_name = Customer::find($request->suppliers_id[$i]);
                    // dd($suppliers_name,$request->suppliers_id[$i]);
                    $purchase->suppliers_name = $suppliers_name->nama;
                }else{
                    $suppliers_name = Supplier::find($purchase->suppliers_id);
                    $purchase->suppliers_name = $suppliers_name->name;
                }

                $purchase->product_name = $product_name->product_name;
                $purchase->cabang_id = getCabangId();

                $purchase->save();

                $products = Product::find($purchase->products_id);
                if (!empty($request->nomor_seri[$i])) {

                    if ($products->nomor_seri == $request->nomor_seri[$i]) {
                        // add new stock to the product
                        $products->stok += $request->quantity[$i];
                        $products->save();
                    }else{
                        $namakategori = Category::find(1);

                        $productsNew = new Product();
                        $productsNew->product_name = $product_name->product_name;
                        $productsNew->categories_id = 1;
                        $productsNew->category_name = $namakategori->category_name;
                        $productsNew->capacities_id = $request->capacities_id[$i];
                        $productsNew->harga_modal = $request->product_price[$i];
                        $productsNew->harga_jual = $request->product_price[$i];
                        $productsNew->harga_jual_toko = $request->product_price[$i];
                        $productsNew->ram = $request->ram[$i];
                        $productsNew->warna = $request->warna[$i];
                        $productsNew->nomor_seri = $request->nomor_seri[$i];
                        $productsNew->stok_minimal = 1;
                        $productsNew->stok = $request->quantity[$i];
                        $productsNew->keterangan = $request->keterangan[$i];
                        $productsNew->cabang_id = getCabangId();
                        $productsNew->save();
                    }
                }else{
                    // add new stock to the product
                    $products->stok += $request->quantity[$i];
                    $products->save();
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
