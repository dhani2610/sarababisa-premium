<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;

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
        return view('pages/kepalatoko/pembelian/create', compact('suppliers', 'products', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->products_id == null) {

            $notification = array(
                'message' => 'Sorry you do not select any item',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        } else {
            $count_product = count($request->products_id);
            for ($i = 0; $i < $count_product; $i++) {
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
                $suppliers_name = Supplier::find($purchase->suppliers_id);

                $purchase->product_name = $product_name->product_name;
                $purchase->suppliers_name = $suppliers_name->name;
                $purchase->cabang_id = getCabangId();

                $purchase->save();

                // add new stock to the product
                $products = Product::find($purchase->products_id);
                $products->stok += $request->quantity[$i];
                $products->save();
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
