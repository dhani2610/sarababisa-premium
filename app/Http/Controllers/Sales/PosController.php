<?php

namespace App\Http\Controllers\Sales;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Gloudemans\Shoppingcart\Facades\Cart;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/sales/pos/index');
    }

    public function show($id)
    {
        $order = Order::with('customer', 'detailOrders')->where('id', $id)->first();
        $toko = StoreSetting::where('cabang_id',$order->cabang_id)->first();

        return view('pages/sales/pos/cetak', compact('order', 'toko'));
    }

    public function addcart(Request $request)
    {
        Cart::add([
            'id' => $request->id,
            'name' => $request->name,
            'qty' => $request->qty,
            'price' => $request->price,
            'weight' => 20,
            'options' => [
                'modal' => $request->modal,
                'harga_asli' => $request->harga_asli,
                'garansi' => $request->garansi,
                'garansi_imei' => $request->garansi_imei,
                'ppn' => $request->ppn
            ]
        ]);

        return redirect()->route('sales-pos');
    }

    public function CartUpdate(Request $request, $rowId)
    {
        $qty = $request->qty;
        Cart::update($rowId, $qty);

        return redirect()->route('sales-pos');
    }

    public function applyDiscount(Request $request)
    {
        $discount = $request->input('discount');

        // Memperoleh daftar item dalam keranjang
        $cartItems = Cart::content();

        foreach ($cartItems as $cartItem) {
            // Menghitung total harga sebelum diskon
            $totalBeforeDiscount = $cartItem->qty * $cartItem->price;

            // Menghitung jumlah diskon berdasarkan persentase
            $discountAmount = $totalBeforeDiscount * ($discount / 100);

            // Menghitung total harga setelah diskon
            $totalAfterDiscount = $totalBeforeDiscount - $discountAmount;

            // Memperbarui harga item dengan harga setelah diskon
            Cart::update($cartItem->rowId, [
                'price' => $totalAfterDiscount / $cartItem->qty
            ]);
        }

        return redirect()->route('sales-pos');
    }

    public function CartRemove($rowId)
    {
        Cart::remove($rowId);

        return redirect()->route('sales-pos');
    }

    public function CompleteOrder(Request $request)
    {
        $contents = Cart::content();
        if ($contents->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang belanja masih kosong! Silakan masukkan produk terlebih dahulu.');
        }

        $request->validate([
            'customers_id' => 'required',
            'users_id' => 'required',
            'payment_method' => 'required',
            'pay' => 'required',
            'sub_total' => 'required',
        ], [
            'customers_id.required' => 'Pelanggan wajib dipilih!',
            'users_id.required' => 'Kasir / Sales wajib dipilih!',
            'payment_method.required' => 'Metode pembayaran wajib dipilih!',
            'pay.required' => 'Nominal bayar wajib diisi!',
            'sub_total.required' => 'Sub total wajib diisi!',
        ]);

        $nama_pelanggan = Customer::find($request->customers_id);
        if (!$nama_pelanggan) {
            return redirect()->back()->with('error', 'Data pelanggan tidak ditemukan!');
        }

        $persen_sales = User::find($request->users_id);
        if (!$persen_sales) {
            return redirect()->back()->with('error', 'Data sales/kasir tidak ditemukan!');
        }

        try {
            $rtotal = (float)str_replace('.', '', $request->sub_total);
            $rpay = (float)str_replace('.', '', $request->pay);
            $mtotal = max(0, $rtotal - $rpay);

            $data = array();
            $data['customers_id'] = $request->customers_id;
            $data['nama_pelanggan'] = $nama_pelanggan->nama;
            $data['users_id'] = $request->users_id;
            $data['order_date'] = $request->order_date ?? Carbon::today()->format('Y-m-d');
            $data['total_products'] = $request->total_products ?? $contents->count();
            $data['sub_total'] = $rtotal;

            $data['invoice_no'] = '' . mt_rand(date('Ymd00'), date('Ymd99'));
            $data['payment_method'] = $request->payment_method;
            $data['pay'] = $rpay;
            $data['due'] = $mtotal;
            $data['cabang_id'] = getCabangId();
            $data['created_at'] = Carbon::now();

            $orders_id = Order::insertGetId($data);

            $pdata = array();
            foreach ($contents as $content) {

                $garansi = Carbon::now();
                if ($content->options->garansi != null) {
                    $expired = $garansi->addDays(
                        $content->options->garansi
                    );
                } else {
                    $expired = null;
                }

                $garansi_imei = Carbon::now();
                if ($content->options->garansi_imei != null) {
                    $expired_imei = $garansi_imei->addDays(
                        $content->options->garansi_imei
                    );
                } else {
                    $expired_imei = null;
                }

                $ppn = $content->price * $content->qty * ($content->options->ppn / 100);

                $pdata['orders_id'] = $orders_id;
                $pdata['products_id'] = $content->id;
                $pdata['product_name'] = $content->name;
                $pdata['quantity'] = $content->qty;
                $pdata['garansi'] = $expired;
                $pdata['garansi_imei'] = $expired_imei;
                $pdata['price'] = $content->price;
                $pdata['ppn'] = $ppn;
                $pdata['total'] = $content->total + $ppn;
                $pdata['sub_total'] = $content->options->harga_asli * $content->qty;
                $pdata['modal'] = $content->options->modal * $content->qty;
                $pdata['profit'] = $content->total - ($content->options->modal * $content->qty);
                $pdata['profit_toko'] = ($content->total - ($content->options->modal * $content->qty)) - ($content->total - ($content->options->modal * $content->qty)) / 100 * $persen_sales->persen;
                $pdata['users_id'] = $request->users_id;
                $pdata['persen_sales'] = $persen_sales->persen;
                $pdata['created_at'] = Carbon::now();
                $pdata['updated_at'] = Carbon::now();
                $pdata['payment_method'] = $request->payment_method;

                OrderDetail::insert($pdata);
            } // end foreach

            // make stock management
            foreach ($contents as $content) {
                $product = Product::where('id', $content->id)->first();
                if ($product) {
                    $product->stok = $product->stok - $content->qty;
                    $product->update();
                }
            } // end foreach

            Cart::destroy();
            toast('Transaksi POS berhasil disimpan.', 'success');

            return redirect()->route('sales-transaksi-produk.index')->with('success', 'Transaksi POS berhasil disimpan.');
        } catch (\Throwable $e) {
            \Log::error('Gagal CompleteOrder Sales POS: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }
}
