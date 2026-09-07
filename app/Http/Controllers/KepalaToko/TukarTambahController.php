<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Term;
use App\Models\User;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TukarTambahController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/pembelian/tukar-tambah');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::where('cabang_id',getCabangId())->where('categories_id', '1')->where('stok', '>=', '1')->get();
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $sales = User::forCabang()->where('role', 'Sales')->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $colors = Color::where('cabang_id',getCabangId())->get();
        return view('pages/kepalatoko/pembelian/input-tukar-tambah', compact('customers', 'products', 'sales', 'brands', 'model_series', 'capacities', 'colors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $nama_pelanggan = Customer::find($request->customers_id);
        if ($request->payment_method === 'Tunai') {
            $tunai = $request->price;
            $transfer = 0;
        }

        if ($request->payment_method === 'Transfer') {
            $transfer = $request->price;
            $tunai = 0;
        }

        // Create sale
        $sale = Order::create([
            'customers_id' => $request->customers_id,
            'users_id' => $request->users_id,
            'order_date' => $request->order_date,
            'total_products' => 1,
            'sub_total' => $request->price,
            'invoice_no' => '' . mt_rand(date('Ymd00'), date('Ymd99')).time(),
            'nama_pelanggan' => $nama_pelanggan->nama,
            'payment_method' => $request->payment_method,
            'payment_status' => 1,
            'pay' => $request->price,
            'tunai' => $tunai,
            'transfer' => $transfer,
            'due' => 0,
            'is_approve' => 'Setuju',
            'tgl_disetujui' => date('Y-m-d'),
            'note' => 'Tukar Tambah',
            'cabang_id' => getCabangId(),
        ]);

        $produk_jual = Product::find($request->product_sale_id);

        $garansi = Carbon::now();
        if ($produk_jual->garansi != null) {
            $expired = $garansi->addDays(
                $produk_jual->garansi
            );
        } else {
            $expired = null;
        }

        $garansi_imei = Carbon::now();
        if ($produk_jual->garansi_imei != null) {
            $expired_imei = $garansi_imei->addDays(
                $produk_jual->garansi_imei
            );
        } else {
            $expired_imei = null;
        }
        $persen_sales = User::find($request->users_id);

        // Create sale detail
        OrderDetail::create([
            'orders_id' => $sale->id,
            'users_id' => $request->users_id,
            'persen_sales' => $persen_sales->persen,
            'products_id' => $request->product_sale_id,
            'product_name' => $produk_jual->product_name,
            'quantity' => 1,
            'garansi' => $expired,
            'garansi_imei' => $expired_imei,
            'price' => $request->price,
            'total' => $request->price,
            'sub_total' => $request->price,
            'product_discount_amount' => 0,
            'modal' => $produk_jual->harga_modal,
            'profit' => $request->price - $produk_jual->harga_modal,
            'profit_toko' => ($request->price - $produk_jual->harga_modal) - ($request->price - $produk_jual->harga_modal) / 100 * $persen_sales->persen,
            'payment_method' => $request->payment_method,
            'cabang_id' => getCabangId(),
            'note' => 'Tukar Tambah',
        ]);

        $merek = Brand::find($request->brands_id);
        $model = ModelSerie::find($request->model_series_id);

        $productName = $merek->name . ' ' . $model->name;

        // Create product
        $produk = Product::create([
            'product_name' => $productName,
            'nomor_seri' => $request->nomor_seri,
            'categories_id' => 1,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'category_name' => 'Handphone',
            'warna' => $request->warna,
            'kondisi' => 'Second',
            'ram' => $request->ram,
            'capacities_id' => $request->capacities_id,
            'stok' => 1,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'garansi' => $request->garansi,
            'garansi_imei' => $request->garansi_imei,
            'keterangan' => $request->keterangan,
            'product_code' => $request->product_code,
            'cabang_id' => getCabangId()
        ]);

        // Create product
        Purchase::create([
            'reference_number' => $sale->invoice_no,
            'customers_id' => $request->customers_id,
            'products_id' => $produk->id,
            'product_name' => $produk->product_name,
            'quantity' => 1,
            'product_price' => $request->harga_modal,
            'total_price' => $request->harga_modal,
            'cabang_id' => getCabangId(),
            'keterangan' => 'Tukar Tambah',
            'date' => date('Y-m-d')
        ]);

        return redirect()->route('tukar-tambah.index');
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

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $tradeInReports = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->join('purchases', 'purchases.reference_number', '=', 'orders.invoice_no')
            ->join('products as sold_products', 'order_details.products_id', '=', 'sold_products.id')
            ->join('products as traded_products', 'purchases.products_id', '=', 'traded_products.id')
            ->select(
                'orders.invoice_no as invoice',
                'orders.nama_pelanggan as pelanggan',
                'order_details.product_name as produk_dijual',
                'order_details.price as harga_jual',
                'sold_products.nomor_seri as NomorSeriDijual',  // Nomor seri dari produk yang dijual
                'purchases.product_name as produk_ditukar',
                'purchases.product_price as harga_tukar',
                'traded_products.nomor_seri as NomorSeriDitukar',  // Nomor seri dari produk yang ditukar
                DB::raw('order_details.price - purchases.product_price as sisa_pembayaran')
            )
            ->whereNotNull('purchases.reference_number')
            ->where('purchases.cabang_id', getCabangId())
            ->where('orders.cabang_id', getCabangId())
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->get();

        // Menghitung total item penjualan
        $total_item = Purchase::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->where('keterangan', 'Tukar Tambah')
            ->sum('quantity');
        // Menghitung total biaya
        $total_pembelian =
            Purchase::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->where('keterangan', 'Tukar Tambah')
            ->sum('product_price');


        $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-tukar-tambah', [
            'users' => $users,
            'imagePath' => $imagePath,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_item' => $total_item,
            'total_pembelian' => $total_pembelian,
            'tradeInReports' => $tradeInReports,
        ]);

        $filename = 'Laporan Tukar Tambah' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }

    public function cetakinkjet($reference_number)
    {
        $order = Order::with('customer')->where('invoice_no', $reference_number)->first();

        // Ambil orders_id dari order yang ditemukan
        $ordersId = $order->id;

        // Ambil data order detail berdasarkan orders_id yang ditemukan
        $orderItem = OrderDetail::with('product', 'order')
            ->where('orders_id', $ordersId)
            ->get();

        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');

        $produkTukarTambah = Purchase::with('product')->where('reference_number', $reference_number)->get();
        $hargaTukar = Purchase::where('reference_number', $reference_number)->sum('product_price');

        $users = User::find(1);
        $terms = Term::find(3);
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $order->invoice_no;
        $namaPelanggan = $order->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.produk.tukar-tambah-cetak-inkjet', [
            'order' => $order,
            'users' => $users,
            'terms' => $terms,
            'orderItem' => $orderItem,
            'produkTukarTambah' => $produkTukarTambah,
            'hargaTukar' => $hargaTukar,
            'imagePath' => $imagePath,
            'total' => $total,
            'subtotal' => $subtotal,
            'toko' => $toko,
        ]);

        $filename = 'Nota Penjualan Tukar Tambah ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
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
            $products->delete();
        }

        return redirect()->back();
    }
}
