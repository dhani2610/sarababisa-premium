<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\Order;

class ApprovePenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $item = Order::findOrFail($id);

        return view('pages.kepalatoko.produk.transaksi-penjualan-approve', [
            'item' => $item
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
        $item = Order::with('detailOrders')->findOrFail($id);
        $oldApprove = $item->is_approve;
        $newApprove = $request->input('is_approve');

        $data = $request->all();
        $item->update($data);

        // Jika status diubah menjadi Ditolak dan sebelumnya bukan Ditolak, kembalikan stok
        if ($newApprove === 'Ditolak' && $oldApprove !== 'Ditolak') {
            $item->returnStock();
            toast('Transaksi produk berhasil ditolak dan stok telah dikembalikan.', 'info');
        } elseif ($newApprove === 'Setuju' && $oldApprove === 'Ditolak') {
            $item->deductStock();
            toast('Transaksi produk berhasil disetujui.', 'success');
        } else {
            toast('Transaksi produk berhasil diperbarui.', 'success');
        }

        return redirect()->route('transaksi-produk.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
