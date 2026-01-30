<?php

namespace App\Http\Controllers\AdminToko;

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
use App\Models\Brand;
use App\Models\Capacity;
use App\Models\Color;
use App\Models\ModelSerie;
use App\Models\SubCategory;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

         $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $colors = Color::all();
        $spareparts = SubCategory::where('categories_id', '=', '2')->get();
        $accessories = SubCategory::where('categories_id', '=', '3')->get();
        $tools = SubCategory::where('categories_id', '=', '4')->get();

        return view('pages/admintoko/pos/index',compact('brands','capacities','model_series','colors','toko','spareparts','accessories','tools'));
    }

    public function show($id)
    {
        $order = Order::with('customer', 'detailOrders')->where('id', $id)->first();
        $toko = StoreSetting::where('cabang_id',$order->cabang_id)->first();

        return view('pages/admintoko/pos/cetak', compact('order', 'toko'));
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

        return redirect()->route('admin-pos');
    }

    public function CartUpdate(Request $request, $rowId)
    {
        $qty = $request->qty;
        Cart::update($rowId, $qty);

        return redirect()->route('admin-pos');
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

        return redirect()->route('admin-pos');
    }

    public function CartRemove($rowId)
    {
        Cart::remove($rowId);

        return redirect()->route('admin-pos');
    }

    public function CompleteOrder(Request $request)
    {
        $rtotal = $request->sub_total;
        $rpay = $request->pay;
        $mtotal = $rtotal - $rpay;
        $nama_pelanggan = Customer::find($request->customers_id);

        $data = array();
        $data['customers_id'] = $request->customers_id;
        $data['nama_pelanggan'] = $nama_pelanggan->nama;
        $data['users_id'] = $request->users_id;
        $data['order_date'] = $request->order_date;
        $data['total_products'] = $request->total_products;
        $data['sub_total'] = $request->sub_total;

        $data['invoice_no'] = '' . mt_rand(date('Ymd00'), date('Ymd99'));
        $data['payment_method'] = $request->payment_method;
        $data['pay'] = $request->pay;
        $data['due'] = $mtotal;
        $data['created_at'] = Carbon::now();

        $orders_id = Order::insertGetId($data);
        $contents = Cart::content();

        $persen_sales = User::find($request->users_id);

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
            $pdata['profit_toko'] = ($content->total - ($content->options->modal * $content->qty)) - ($content->total - ($content->options->modal * $content->qty)) / 100 * ($persen_sales->persen + $request->persen_admin);
            $pdata['users_id'] = $request->users_id;
            $pdata['persen_sales'] = $persen_sales->persen;
            $pdata['is_admin_toko'] = $request->is_admin_toko;
            $pdata['admin_id'] = $request->admin_id;
            $pdata['persen_admin'] = $request->persen_admin;
            $pdata['created_at'] = Carbon::now();
            $pdata['updated_at'] = Carbon::now();
            $pdata['payment_method'] = $request->payment_method;

            OrderDetail::insert($pdata);
        } // end foreach

        // make stock management
        foreach ($contents as $content) {
            $product = Product::where('id', $content->id)->first();
            $product->stok = $product->stok - $content->qty;
            $product->update();
        } // end foreach

        Cart::destroy();

        return redirect()->route('admin-transaksi-produk.index');
    }
}
