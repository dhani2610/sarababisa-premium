<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $product_transactions = OrderDetail::with('product', 'user')->get();
        $count = OrderDetail::all()->count();

        $rumusomzethari = Order::whereHas('detailOrders', function ($query) {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $query->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereYear('tgl_disetujui', $currentYear)
                ->whereMonth('tgl_disetujui', $currentMonth)
                ->whereDate('tgl_disetujui', today());
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzethari = $rumusomzethari->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofithari = Order::whereHas('detailOrders', function ($query) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $query->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereYear('tgl_disetujui', $currentYear)
                ->whereMonth('tgl_disetujui', $currentMonth)
                ->whereDate('tgl_disetujui', today());
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profithari = $rumusprofithari->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $rumusomzetbulan = Order::whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month);
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzetbulan = $rumusomzetbulan->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofitbulan = Order::whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month);
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profitbulan = $rumusprofitbulan->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $rumusomzettahun = Order::whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereYear('tgl_disetujui', now()->year);
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzettahun = $rumusomzettahun->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofittahun = Order::whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereYear('tgl_disetujui', now()->year);
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profittahun = $rumusprofittahun->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/admintoko/laporan-penjualan', compact('product_transactions', 'count', 'omzethari', 'profithari', 'omzetbulan', 'profitbulan', 'omzettahun', 'profittahun', 'toko'));
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        if (getCabangId() == 1) {
            $users = User::where('cabang_id',getCabangId())->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }else{
            $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data penjualan
        $orders = OrderDetail::with('order')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'asc')
            ->get();

        // Menghitung total biaya
        $total_biaya = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('total');

        // Menghitung total profit
        $total_profit = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('profit');

        // Menghitung total item penjualan
        $total_penjualan = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('quantity');;

        // Menghitung total modal
        $total_modal = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('modal');

        // Menghitung total diskon
        $total = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('total');
        $sub_total = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('sub_total');
        $total_diskon = $sub_total - $total;

        // Menghitung total pembayaran tunai
        $total_tunai = Order::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('tunai');

        // Menghitung total pembayaran transfer
        $total_transfer = Order::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('transfer');

        // Menghitung total pembayaran kredit
        $total_kredit = Order::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->sum('due');

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        $pdf = Pdf::loadView('pages.admintoko.cetak-laporan-penjualan', [
        // return view('pages.admintoko.cetak-laporan-penjualan', [
        // $pdf = Pdf::loadView('pages.admintoko.cetak-laporan-penjualan', [
            'users' => $users,
            'toko' => $toko,
            'imagePath' => $imagePath,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'orders' => $orders,
            'total_penjualan' => $total_penjualan,
            'total_modal' => $total_modal,
            'total_biaya' => $total_biaya,
            'total_profit' => $total_profit,
            'total_diskon' => $total_diskon,
            'total_tunai' => $total_tunai,
            'total_transfer' => $total_transfer,
            'total_kredit' => $total_kredit,
        ]);

        $filename = 'Laporan Transaksi Penjualan' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
}
