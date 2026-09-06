<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\Term;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class TransaksiProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jumlah_semua = Order::all()->count();
        $jumlah_lunas = Order::where('due', '0')->count();
        $jumlah_tidaklunas = Order::where('due', '>', '0')->count();

        return view('pages/admintoko/produk/transaksi', compact(
            'jumlah_semua',
            'jumlah_lunas',
            'jumlah_tidaklunas'
        ));
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
    public function show($orders_id)
    {
        $toko = User::find(1);
        $order = Order::with('customer')->where('id', $orders_id)->first();
        $orderItem = OrderDetail::with('product')->where('orders_id', $orders_id)->orderBy('id', 'DESC')->get();

        $produkDetails = '';
        foreach ($orderItem as $item) {

            // Tambahkan nomor seri jika kategori produk adalah 1
            if ($item->product->categories_id === 1) {
                $produkDetails .= $item->product_name . ' IMEI ' . $item->product->nomor_seri . ' (Rp ' . number_format($item->price, 0, ',', '.') . ' x ' . $item->quantity . ' pcs)%0A';
            } else {
                $produkDetails .= $item->product_name . ' (Rp ' . number_format($item->price, 0, ',', '.') . ' x ' . $item->quantity . ' pcs)%0A';
            }
        }

        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');
        $totalTax = $orderItem->sum('ppn');
        return view('pages.admintoko.produk.transaksi-detail', compact('order', 'orderItem', 'total', 'subtotal', 'totalTax', 'toko', 'produkDetails'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function cetaktermal($orders_id)
    {
        $order = Order::with('customer', 'user')->where('id', $orders_id)->first();
        $orderItem = OrderDetail::with('product')->where('orders_id', $orders_id)->orderBy('id', 'DESC')->get();
        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');
        $users = User::find(1);
        
        if ($order->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$order->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        $totalTax = $orderItem->sum('ppn');
        $totalWithoutTax = $order->sub_total - $totalTax;

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $order->invoice_no;
        $namaPelanggan = $order->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.produk.cetak-termal', [
        // return View('pages.kepalatoko.produk.cetak-termal', [
            'order' => $order,
            'users' => $users,
            'orderItem' => $orderItem,
            'total' => $total,
            'subtotal' => $subtotal,
            'imagePath' => $imagePath,
            'totalTax' => $totalTax,
            'totalWithoutTax' => $totalWithoutTax,
        ]);

        $filename = 'Nota Penjualan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    public function cetakinkjet($orders_id)
    {
        $order = Order::with('customer')->where('id', $orders_id)->first();
        $orderItem = OrderDetail::with('product')->where('orders_id', $orders_id)->orderBy('id', 'DESC')->get();
        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');
        $users = User::find(1);


        if ($order->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$order->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini. lalu setting kop di pengaturan toko melalui akun kepala toko', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }
        $terms = Term::find(3);
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $order->invoice_no;
        $namaPelanggan = $order->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.produk.lunas-cetak-inkjet', [
        // return View('pages.kepalatoko.produk.lunas-cetak-inkjet', [
            'order' => $order,
            'users' => $users,
            'terms' => $terms,
            'orderItem' => $orderItem,
            'total' => $total,
            'subtotal' => $subtotal,
            'imagePath' => $imagePath,
            'toko' => $toko,
        ]);

        $filename = 'Nota Penjualan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setPaper('a4', 'landscape')->setOption('isRemoteEnabled', true)->stream($filename);
    }

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
        $item = Order::findOrFail($id);

        if (\App\Helpers\TransactionApprovalHelper::requiresApproval()) {
            \App\Helpers\TransactionApprovalHelper::createRequest('pos', $item);
            toast('Permintaan hapus transaksi POS telah diajukan ke Kepala Toko untuk persetujuan.', 'info');
            return redirect()->back();
        }

        $item->delete();

        OrderDetail::where('orders_id', $item->id)->delete();

        return redirect()->back()->with('success', 'Data transaksi berhasil dihapus.');
    }
}
