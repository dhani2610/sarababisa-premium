<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrderDetail;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $cabang_id = getCabangId();
        $product_transactions = OrderDetail::where('cabang_id',$cabang_id)->with('product', 'user')->get();
        $count = OrderDetail::where('cabang_id',$cabang_id)->get()->count();

        $rumusomzethari = Order::whereHas('detailOrders', function ($query) use ($cabang_id) {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', $currentYear)
                ->whereMonth('tgl_disetujui', $currentMonth)
                ->whereDate('tgl_disetujui', today())
                ->where('cabang_id',$cabang_id);
        })
            ->with(['detailOrders' => function ($query) use ($cabang_id) {
                $query->where('cabang_id',$cabang_id)->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzethari = $rumusomzethari->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofithari = Order::whereHas('detailOrders', function ($query) use ($cabang_id) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', $currentYear)
                ->whereMonth('tgl_disetujui', $currentMonth)
                ->whereDate('tgl_disetujui', today())
                ->where('cabang_id',$cabang_id);
        })
            ->with(['detailOrders' => function ($query) use ($cabang_id) {
                $query->where('cabang_id',$cabang_id)->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profithari = $rumusprofithari->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $rumusomzetbulan = Order::whereHas('detailOrders', function ($query) use ($cabang_id) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month)
                ->where('cabang_id',$cabang_id);
        })
            ->with(['detailOrders' => function ($query) use ($cabang_id) {
                $query->where('cabang_id',$cabang_id)->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzetbulan = $rumusomzetbulan->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofitbulan = Order::whereHas('detailOrders', function ($query) use ($cabang_id) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month)
                ->where('cabang_id',$cabang_id);
        })
            ->with(['detailOrders' => function ($query) use ($cabang_id) {
                $query->where('cabang_id',$cabang_id)->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profitbulan = $rumusprofitbulan->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $rumusomzettahun = Order::whereHas('detailOrders', function ($query) use ($cabang_id) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->where('cabang_id',$cabang_id);
        })
            ->with(['detailOrders' => function ($query) use ($cabang_id) {
                $query->where('cabang_id',$cabang_id)->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzettahun = $rumusomzettahun->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofittahun = Order::whereHas('detailOrders', function ($query) use ($cabang_id) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->where('cabang_id',$cabang_id);
        })
            ->with(['detailOrders' => function ($query) use ($cabang_id) {
                $query->where('cabang_id',$cabang_id)->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profittahun = $rumusprofittahun->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        return view('pages/kepalatoko/laporan-penjualan', compact('product_transactions', 'count', 'omzethari', 'profithari', 'omzetbulan', 'profitbulan', 'omzettahun', 'profittahun'));
    }
    public function indexPajak()
    {
        $product_transactions = OrderDetail::where('cabang_id', getCabangId())->with('product', 'user')->get();
        $count = OrderDetail::where('cabang_id', getCabangId())->get()->count();

        $rumusomzethari = Order::where('cabang_id', getCabangId())->whereHas('detailOrders', function ($query) {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', $currentYear)
                ->whereMonth('tgl_disetujui', $currentMonth)
                ->whereDate('tgl_disetujui', today());
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzethari = $rumusomzethari->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofithari = Order::where('cabang_id', getCabangId())->whereHas('detailOrders', function ($query) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', $currentYear)
                ->whereMonth('tgl_disetujui', $currentMonth)
                ->where('ppn', '!=','0')
                ->whereDate('tgl_disetujui', today());
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(ppn) as total_ppn'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profithari = $rumusprofithari->sum(function ($order) {
            return $order->detailOrders->sum('total_ppn');
        });

        $rumusomzetbulan = Order::where('cabang_id', getCabangId())->whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month);
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzetbulan = $rumusomzetbulan->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofitbulan = Order::where('cabang_id', getCabangId())->whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month);
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(ppn) as total_ppn'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profitbulan = $rumusprofitbulan->sum(function ($order) {
            return $order->detailOrders->sum('total_ppn');
        });

        $rumusomzettahun = Order::where('cabang_id', getCabangId())->whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->where('ppn', '!=','0')
                ->whereYear('tgl_disetujui', now()->year);
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $omzettahun = $rumusomzettahun->sum(function ($order) {
            return $order->detailOrders->sum('total_omzet');
        });

        $rumusprofittahun = Order::where('cabang_id', getCabangId())->whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year);
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(ppn) as total_ppn'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $profittahun = $rumusprofittahun->sum(function ($order) {
            return $order->detailOrders->sum('total_ppn');
        });

        return view('pages/kepalatoko/laporan-penjualan-pajak', compact('product_transactions', 'count', 'omzethari', 'profithari', 'omzetbulan', 'profitbulan', 'omzettahun', 'profittahun'));
    }

    public function cetak(Request $request)
    {
        // 1. Mengambil logo dan nama toko (Kepala Toko)
        if (getCabangId() == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', getCabangId())
                ->where('id', '!=', 1)
                ->where('role', 'Kepala Toko')
                ->orderBy('id', 'asc')
                ->first();
        }

        // Fallback jika user kepala toko tidak ditemukan
        if (!$users) {
             $users = User::find(1);
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // 2. Filter Input
        $start_date = $request->start_date;
        $end_date   = $request->end_date;
        $tipe       = $request->tipe; // Ambil input tipe (Sudah Disetujui / Belum Disetujui)

        // 3. SIAPKAN QUERY BUILDER DASAR
        // $detailQuery: Untuk tabel order_details (Data Barang, Profit, Modal)
        $detailQuery = OrderDetail::with('order')
            ->where('cabang_id', getCabangId());

        // $orderQuery: Untuk tabel orders (Data Pembayaran: Tunai, Transfer, Kredit)
        $orderQuery = Order::where('cabang_id', getCabangId());


        // 4. TERAPKAN FILTER LOGIC
        if ($tipe == 'Sudah Disetujui') {
            // KONDISI: SUDAH DISETUJUI
            // Cek is_approve = 'Setuju' dan filter berdasarkan tgl_disetujui

            // A. Filter OrderDetail (cek parentnya via whereHas)
            $detailQuery->whereHas('order', function ($q) use ($start_date, $end_date) {
                $q->where('is_approve', 'Setuju')
                  ->whereDate('tgl_disetujui', '>=', $start_date)
                  ->whereDate('tgl_disetujui', '<=', $end_date);
            });

            // B. Filter Order (langsung di tabelnya)
            $orderQuery->where('is_approve', 'Setuju')
                       ->whereDate('tgl_disetujui', '>=', $start_date)
                       ->whereDate('tgl_disetujui', '<=', $end_date);

        } else {
            // KONDISI: BELUM DISETUJUI (Default)
            // Cek is_approve NULL, tgl_disetujui NULL, dan filter berdasarkan created_at

            // A. Filter OrderDetail
            $detailQuery->whereHas('order', function ($q) use ($start_date, $end_date) {
                $q->whereNull('is_approve')      // Harus belum ada status approve
                  ->whereNull('tgl_disetujui')   // Harus belum ada tanggal setuju
                  ->whereDate('created_at', '>=', $start_date) // Range pakai created_at
                  ->whereDate('created_at', '<=', $end_date);
            });

            // B. Filter Order
            $orderQuery->whereNull('is_approve')
                       ->whereNull('tgl_disetujui')
                       ->whereDate('created_at', '>=', $start_date)
                       ->whereDate('created_at', '<=', $end_date);
        }

        // 5. EKSEKUSI DATA (Gunakan CLONE agar query dasar tetap bersih)

        // Ambil List Data
        $orders = (clone $detailQuery)->orderBy('created_at', 'asc')->get();

        // Hitung Data dari OrderDetail (Barang)
        $total_biaya     = (clone $detailQuery)->sum('total');
        $total_profit    = (clone $detailQuery)->sum('profit');
        $total_penjualan = (clone $detailQuery)->sum('quantity'); // Total Item
        $total_modal     = (clone $detailQuery)->sum('modal');

        // Hitung Diskon (Subtotal - Total)
        $sum_total       = (clone $detailQuery)->sum('total');
        $sum_sub_total   = (clone $detailQuery)->sum('sub_total');
        $total_diskon    = $sum_sub_total - $sum_total;

        // Hitung Data dari Order (Pembayaran)
        $total_tunai    = (clone $orderQuery)->sum('tunai');
        $total_transfer = (clone $orderQuery)->sum('transfer');
        $total_kredit   = (clone $orderQuery)->sum('due'); // due = hutang/kredit


        $expenses = Expense::with('user')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->where('tipe', 2)
            ->orderBy('created_at', 'asc')
            ->get();
        $total_pengeluaran = $expenses->sum('price');

        // 6. RENDER PDF
        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-penjualan', [
            'users'           => $users,
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
            'total_kredit'    => $total_kredit,
            'pengeluaran_data'          => $expenses, // Kirim variabel expenses yg sudah di query
            'total_pengeluaran'          => $total_pengeluaran, // Kirim variabel expenses yg sudah di query
            'tipe_laporan'    => $tipe // (Opsional) jika ingin menampilkan judul tipe di PDF
        ]);

        $filename = 'Laporan Transaksi Penjualan (' . ($tipe ?: 'Default') . ') ' . $start_date . ' sd ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
    // public function cetak(Request $request)
    // {
    //     // Mengambil logo dan nama toko
    //     $users = User::find(1);

    //     $logo = $users->profile_photo_path;
    //     $imagePath = public_path('storage/' . $logo);

    //     // Filter tanggal
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     // Mengambil data penjualan
    //     $orders = OrderDetail::with('order')->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Menghitung total biaya
    //     $total_biaya = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('total');

    //     // Menghitung total profit
    //     $total_profit = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('profit');

    //     // Menghitung total item penjualan
    //     $total_penjualan = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('quantity');;

    //     // Menghitung total modal
    //     $total_modal = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('modal');

    //     // Menghitung total diskon
    //     $total = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('total');
    //     $sub_total = OrderDetail::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('sub_total');
    //     $total_diskon = $sub_total - $total;

    //     // Menghitung total pembayaran tunai
    //     $total_tunai = Order::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('tunai');

    //     // Menghitung total pembayaran transfer
    //     $total_transfer = Order::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('transfer');

    //     // Menghitung total pembayaran kredit
    //     $total_kredit = Order::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('due');

    //         // dd($orders);
    //         // return  response()->json($orders);
    //         // return view('pages.kepalatoko.cetak-laporan-penjualan', [
    //     $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-penjualan', [
    //         'users' => $users,
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
    public function cetakPajak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data penjualan
        $orders = OrderDetail::with('order')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->orderBy('created_at', 'asc')
            ->get();

        // Menghitung total biaya
        $total_biaya = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('total');

        // Menghitung total profit
        $total_profit = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('ppn');

        // Menghitung total item penjualan
        $total_penjualan = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('quantity');;

        // Menghitung total modal
        $total_modal = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('modal');

        // Menghitung total diskon
        $total = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('total');
        $sub_total = OrderDetail::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('sub_total');
        $total_diskon = $sub_total - $total;

        // Menghitung total pembayaran tunai
        $total_tunai = Order::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('tunai');

        // Menghitung total pembayaran transfer
        $total_transfer = Order::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('transfer');

        // Menghitung total pembayaran kredit
        $total_kredit = Order::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('due');

        $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-penjualan-pajak', [
        // return View('pages.kepalatoko.cetak-laporan-penjualan-pajak', [
            'users' => $users,
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
