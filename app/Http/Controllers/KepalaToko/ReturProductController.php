<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Retur;

class ReturProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/pembelian/retur');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        // Dapatkan data retur yang akan dihapus
        $returs = Retur::whereIn('id', $selectedIds)->get();

        foreach ($returs as $item) {
            // Kurangi stok produk terkait
            $products = $item->purchase->product;
            $products->stok += $item->retur_quantity;
            $products->save();
        }

        Retur::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data retur pembelian produk berhasil dihapus.']);
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
        $purchase = Purchase::find($request->purchases_id);

        // Transaction create
        Retur::create([
            'purchases_id' => $request->purchases_id,
            'suppliers_id' => $purchase->suppliers_id,
            'supplier_name' => $purchase->suppliers_name,
            'product_name' => $purchase->product_name,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
            'retur_quantity' => $request->retur_quantity,
            'retur_credit' => $purchase->product_price * $request->retur_quantity,
            'date' => $request->date,
            'cabang_id' => getCabangId(),
        ]);

        $products = Product::find($purchase->products_id);
        $products->stok -= $request->retur_quantity;
        $products->save();

        return redirect()->route('retur.index');
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
        $item = Retur::findOrFail($id);

        $item->delete();

        $products = Product::find($item->purchase->products_id);
        $products->stok += $item->retur_quantity;
        $products->save();

        return redirect()->route('retur.index');
    }
}
