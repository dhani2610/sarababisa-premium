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
        // 1. Mengambil logo dan nama toko
        if (getCabangId() == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', getCabangId())
                ->where('id', '!=', 1)
                ->where('role', 'Kepala Toko')
                ->orderBy('id', 'asc')
                ->first();
        }

        // Fallback jika user kepala toko tidak ditemukan, ambil admin utama agar tidak error
        if (!$users) {
             $users = User::find(1);
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);
        $toko = StoreSetting::where('cabang_id', getCabangId())->first();

        // 2. Filter Input
        $start_date = $request->start_date;
        $end_date   = $request->end_date;
        $tipe       = $request->tipe; // Ambil input tipe
        $kategori   = $request->kategori; // Ambil input kategori

        // 3. SIAPKAN QUERY BUILDER (Agar tidak menulis ulang where berkal-kali)

        // Query untuk OrderDetail (Data Barang/Penjualan)
        $detailQuery = OrderDetail::with('order')
            ->where('cabang_id', getCabangId());

        // Query untuk Order (Data Pembayaran: Tunai, Transfer, Kredit)
        $orderQuery = Order::where('cabang_id', getCabangId());

        // --- LOGIKA FILTER TIPE ---
        // --- LOGIKA FILTER TIPE ---
        if ($tipe == 'Sudah Disetujui') {

            // A. Filter untuk OrderDetail (cek ke tabel parent 'order')
            $detailQuery->whereHas('order', function ($q) use ($start_date, $end_date) {
                $q->where('is_approve', 'Setuju')
                  ->whereDate('tgl_disetujui', '>=', $start_date)
                  ->whereDate('tgl_disetujui', '<=', $end_date);
            });

            // B. Filter untuk Order (tabel parent langsung)
            $orderQuery->where('is_approve', 'Setuju')
                       ->whereDate('tgl_disetujui', '>=', $start_date)
                       ->whereDate('tgl_disetujui', '<=', $end_date);

        } else {
            // KONDISI: Belum Disetujui
            // Syarat: is_approve NULL & tgl_disetujui NULL
            // Range Tanggal: Menggunakan created_at (karena tgl_disetujui kosong)

            // A. Filter untuk OrderDetail (Gunakan whereHas untuk cek parent order)
            $detailQuery->whereHas('order', function ($q) use ($start_date, $end_date) {
                $q->whereNull('is_approve')      // is_approve harus NULL
                  ->whereNull('tgl_disetujui')   // tgl_disetujui harus NULL
                  ->whereDate('created_at', '>=', $start_date) // Range pakai created_at
                  ->whereDate('created_at', '<=', $end_date);
            });

            // B. Filter untuk Order (tabel parent langsung)
            $orderQuery->whereNull('is_approve')     // is_approve harus NULL
                       ->whereNull('tgl_disetujui')  // tgl_disetujui harus NULL
                       ->whereDate('created_at', '>=', $start_date) // Range pakai created_at
                       ->whereDate('created_at', '<=', $end_date);
        }

         if (!empty($kategori)) {
            // Filter berdasarkan kategori produk
            $detailQuery->whereHas('product', function ($q) use ($kategori) {
                $q->where('categories_id', $kategori);
            });
        }

        // 4. EKSEKUSI DATA (Menggunakan clone agar query dasar tidak berubah)

        // Ambil Data List Penjualan
        $orders = (clone $detailQuery)->orderBy('created_at', 'asc')->get();

        // Hitung Statistik dari OrderDetail
        $total_biaya     = (clone $detailQuery)->sum('total');
        $total_profit    = (clone $detailQuery)->sum('profit');
        $total_penjualan = (clone $detailQuery)->sum('quantity');
        $total_modal     = (clone $detailQuery)->sum('modal');

        // Hitung Diskon (Subtotal - Total)
        $sum_total       = (clone $detailQuery)->sum('total');
        $sum_sub_total   = (clone $detailQuery)->sum('sub_total');
        $total_diskon    = $sum_sub_total - $sum_total;

        // Hitung Statistik Pembayaran dari Order
        $total_tunai    = (clone $orderQuery)->sum('tunai');
        $total_transfer = (clone $orderQuery)->whereNotIn('payment_method',getMetodePembayaran()->pluck('nama'))->sum('transfer');
        $total_kredit   = (clone $orderQuery)->sum('due');


        $dataOtherMetodePembayaran = [];
        foreach (getMetodePembayaran() as $key => $value) {
            $dt['metode'] = $value->nama;
            $dt['total']    = (clone $orderQuery)->where('payment_method',$value->nama)->sum('transfer');
            array_push($dataOtherMetodePembayaran,$dt);
        }
        // 5. RENDER PDF
        $pdf = PDF::loadView('pages.admintoko.cetak-laporan-penjualan', [
            'users'           => $users,
            'toko'            => $toko,
            'imagePath'       => $imagePath,
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'orders'          => $orders,
            'total_penjualan' => $total_penjualan,
            'total_modal'     => $total_modal,
            'total_biaya'     => $total_biaya,
            'total_profit'    => $total_profit,
            'total_diskon'    => $total_diskon,
            'total_tunai'     => $total_tunai,
            'total_transfer'  => $total_transfer,
            'dataOtherMetodePembayaran'  => $dataOtherMetodePembayaran,
            'total_kredit'    => $total_kredit,
            'tipe_laporan'    => $tipe // Opsional: kirim ke view untuk judul
        ]);

        $filename = 'Laporan Transaksi Penjualan (' . ($tipe ?: 'Default') . ') ' . $start_date . ' sd ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }

    // public function cetak(Request $request)
    // {
    //     // Mengambil logo dan nama toko
    //     $users = User::find(1);

    //     if (getCabangId() == 1) {
    //         $users = User::find(1);
    //     }else{
    //         $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
    //     }

    //     $logo = $users->profile_photo_path;
    //     $imagePath = public_path('storage/' . $logo);

    //     // Filter tanggal
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     // Mengambil data penjualan
    //     $orders = OrderDetail::with('order')->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Menghitung total biaya
    //     $total_biaya = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('total');

    //     // Menghitung total profit
    //     $total_profit = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('profit');

    //     // Menghitung total item penjualan
    //     $total_penjualan = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('quantity');;

    //     // Menghitung total modal
    //     $total_modal = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('modal');

    //     // Menghitung total diskon
    //     $total = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('total');
    //     $sub_total = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('sub_total');
    //     $total_diskon = $sub_total - $total;

    //     // Menghitung total pembayaran tunai
    //     $total_tunai = Order::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('tunai');

    //     // Menghitung total pembayaran transfer
    //     $total_transfer = Order::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('transfer');

    //     // Menghitung total pembayaran kredit
    //     $total_kredit = Order::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id', getCabangId())
    //         ->sum('due');

    //     $toko = StoreSetting::where('cabang_id',getCabangId())->first();

    //     $pdf = Pdf::loadView('pages.admintoko.cetak-laporan-penjualan', [
    //     // return view('pages.admintoko.cetak-laporan-penjualan', [
    //     // $pdf = Pdf::loadView('pages.admintoko.cetak-laporan-penjualan', [
    //         'users' => $users,
    //         'toko' => $toko,
    //         'imagePath' => $imagePath,
    //         'start_date' => $start_date,
    //         'end_date' => $end_date,
    //         'orders' => $orders,
    //         'total_penjualan' => $total_penjualan,
    //         'total_modal' => $total_modal,
    //         'total_biaya' => $total_biaya,
    //         'total_profit' => $total_profit,
    //         'total_diskon' => $total_diskon,
    //         'total_tunai' => $total_tunai,
    //         'total_transfer' => $total_transfer,
    //         'total_kredit' => $total_kredit,
    //     ]);

    //     $filename = 'Laporan Transaksi Penjualan' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

    //     return $pdf->stream($filename);
    // }
}
