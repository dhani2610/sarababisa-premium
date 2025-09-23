<?php

namespace App\Http\Controllers\AdminToko;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Barryvdh\DomPDF\PDF;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransaksiServisLangsungController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $nomor_servis = '' . mt_rand(date('Ymd00'), date('Ymd99'));
        $nama_pelanggan = Customer::find($request->customers_id);
        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;
        $persen_teknisi = User::find($request->users_id)->persen;

        if ($request->service_actions_id != null) {
            $tindakan_servis = ServiceAction::find($request->service_actions_id)->nama_tindakan;
        } else {
            $tindakan_servis = $request->tindakan_servis;
        }

        $garansi = Carbon::now();
        if ($request->garansi != null) {
            $expired = $garansi->addDays(
                $request->garansi
            );
        } else {
            $expired = null;
        }

        $profittransaksi = $request->biaya - $request->modal_sparepart - $request->diskon;
        $bagihasil = ($request->biaya - $request->modal_sparepart - $request->diskon) / 100;

        if ($request->cara_pembayaran === 'Tunai & Transfer') {
            $due = 0;
            if ($request->tunai != 0) {
                $transfer = $request->biaya - $request->tunai;
                $pay = $request->biaya;
                $tunai = $request->tunai;
            } else {
                $tunai = $request->biaya - $request->transfer;
                $pay = $request->biaya;
                $transfer = $request->transfer;
            }
        }

        if ($request->cara_pembayaran === 'Tunai') {
            $tunai = $request->biaya;
            $transfer = 0;
            $due = 0;
            $pay = $request->biaya;
        }

        if ($request->cara_pembayaran === 'Transfer') {
            $transfer = $request->biaya;
            $tunai = 0;
            $due = 0;
            $pay = $request->biaya;
        }

        if ($request->cara_pembayaran === 'Kredit') {
            $pay = $request->pay;
            $due = $request->biaya - $request->pay;
            if ($request->tunai) {
                $tunai = $request->pay;
                $transfer = 0;
            } elseif ($request->transfer) {
                $transfer = $request->pay;
                $tunai = 0;
            }
        }

        $waktu = Carbon::today();
        if ($request->tempo != null) {
            $tempo = $waktu->addDays(
                $request->tempo
            );
        } else {
            $tempo = null;
        }

        // Transaction create
        ServiceTransaction::create([
            'nomor_servis' => $nomor_servis,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'nama_barang' => $nama_barang,
            'kerusakan' => $request->kerusakan,
            'imei' => $request->imei,
            'warna' => $request->warna,
            'capacities_id' => $request->capacities_id,
            'kelengkapan' => $request->kelengkapan,
            'qc_masuk' => $request->qc_masuk,
            'status_servis' => "Sudah Diambil",
            'penerima' => $request->penerima,
            'users_id' => $request->users_id,
            'kondisi_servis' => "Sudah jadi",
            'service_actions_id' => $request->service_actions_id,
            'products_id' => $request->products_id,
            'tindakan_servis' => $tindakan_servis,
            'modal_sparepart' => $request->modal_sparepart,
            'biaya' => $request->biaya,
            'catatan' => $request->catatan,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - ($bagihasil *= Auth::user()->persen + $persen_teknisi),
            'qc_keluar' => $request->qc_keluar,
            'cara_pembayaran' => $request->cara_pembayaran,
            'diskon' => $request->diskon,
            'garansi' => $request->garansi,
            'exp_garansi' => $expired,
            'tgl_ambil' => $request->tgl_ambil,
            'pengambil' => $nama_pelanggan->nama,
            'is_admin_toko' => "Admin",
            'admin_id' => Auth::user()->id,
            'persen_admin' => Auth::user()->persen,
            'penyerah' => Auth::user()->name,
            'pay' => $pay,
            'due' => $due,
            'tempo' => $tempo,
            'tunai' => $tunai,
            'transfer' => $transfer,
        ]);

        if ($request->products_id != null) {
            $spareparts = Product::find($request->products_id);
            $spareparts->stok -= 1;
            $spareparts->save();

            // Menambahkan data transaksi produk sparepart
            $nama_pelanggan = Customer::find($request->customers_id)->nama;
            $order = new Order();
            $order->customers_id = $request->customers_id;
            $order->users_id = $request->sales_id;
            $order->order_date = Carbon::today()->locale('id')->translatedFormat('d F Y');
            $order->total_products = 1;
            $order->sub_total = $spareparts->harga_jual;
            $order->invoice_no = '' . mt_rand(date('Ymd00'), date('Ymd99'));
            $order->nama_pelanggan = $nama_pelanggan;
            $order->payment_method = "Tunai";
            $order->payment_status = 1;
            $order->pay = $spareparts->harga_jual;
            $order->due = 0;
            $order->discount_amount = 0;
            $order->is_approve = 'Setuju';
            $order->tgl_disetujui = Carbon::today();
            $order->save();

            // Menambahkan data detail transaksi produk sparepart
            $garansi = Carbon::now();
            if (
                $spareparts->garansi != null
            ) {
                $expired = $garansi->addDays(
                    $spareparts->garansi
                );
            } else {
                $expired = null;
            }
            if ($request->sales_id != 1) {
                $persen_sales = User::find($request->sales_id)->persen;
            } else {
                $persen_sales = null;
            }

            $orderDetail = new OrderDetail();
            $orderDetail->orders_id = $order->id;
            $orderDetail->users_id = $request->sales_id;
            $orderDetail->products_id = $request->products_id;
            $orderDetail->product_name = $spareparts->product_name;
            $orderDetail->quantity = 1;
            $orderDetail->price = $spareparts->harga_jual;
            $orderDetail->total = $spareparts->harga_jual;
            $orderDetail->sub_total = $spareparts->harga_jual;
            $orderDetail->modal = $spareparts->harga_modal;
            $orderDetail->profit = $spareparts->harga_jual - $spareparts->harga_modal;
            $orderDetail->persen_sales = $persen_sales;
            $orderDetail->profit_toko = ($spareparts->harga_jual - $spareparts->harga_modal) - ($spareparts->harga_jual - $spareparts->harga_modal) / 100 * $persen_sales;
            $orderDetail->garansi = $expired;
            $orderDetail->product_discount_amount = 0;
            $orderDetail->save();
        }

        return redirect()->route('admin-servis-sudah-diambil.index');
    }
}
