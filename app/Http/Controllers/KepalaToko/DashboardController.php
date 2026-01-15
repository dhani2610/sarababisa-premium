<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Type;
use App\Models\Order;
use App\Models\Budget;
use App\Models\Target;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Category;
use App\Models\Incident;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Purchase;

class DashboardController extends Controller
{

    /**
     * Displays the dashboard screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $currentMonth = now()->month;
        $cabang = getCabangId();
        $currentDate = Carbon::now();

        // TYPES & CATEGORIES
        $types = Type::with('service')
            ->where('cabang_id', $cabang)
            ->get();

        $categories = Category::where('cabang_id', $cabang)->get();

        // CATEGORY SALES
        $categorySales = [];
        foreach ($categories as $category) {
            $totalSales = OrderDetail::where('cabang_id', $cabang)
                ->totalSales($category->id);

            $categorySales[] = [
                'category' => $category->category_name,
                'total_sales' => $totalSales,
            ];
        }

        // PENGELUARAN
        $totalpengeluaran = Expense::where('cabang_id', $cabang)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('is_approve', 'Setuju')
            ->sum('price');

        $totalinsiden = Incident::where('cabang_id', $cabang)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('biaya_toko');

        $totalpembelian = Purchase::where('cabang_id', $cabang)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_price');

        // REMINDER SERVIS
        $transactions = ServiceTransaction::where('cabang_id', $cabang)
            ->where('status_servis', 'Belum cek')
            ->get();

        $reminders = $transactions->filter(function ($transaction) use ($currentDate) {
            return $transaction->created_at->addDays(7)->isPast();
        })->count();

        // APPROVAL
        $approveservis = ServiceTransaction::where('cabang_id', $cabang)
            ->where('is_approve', null)
            ->where('status_servis', 'Sudah Diambil')
            ->count();

        $approvepenjualan = Order::where('cabang_id', $cabang)
            ->where('is_approve', null)->count();

        $approvekasbon = Debt::where('cabang_id', $cabang)
            ->where('is_approve', null)->count();

        $approvepengeluaran = Expense::where('cabang_id', $cabang)
            ->where('is_approve', null)->count();

        // STOK HABIS
        $stokhabis = Product::where('cabang_id', $cabang)
            ->where('stok', '<=', DB::raw('`stok_minimal`'))->count();

        // TOTAL BUDGET
        $totalbudgets = Budget::where('cabang_id', $cabang)->sum('total');

        // PROFIT SERVIS BULANAN
        $bulanprofitbersihservis = ServiceTransaction::where('cabang_id', $cabang)
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->where('is_approve', 'Setuju')
            ->sum('profittoko');

        // PROFIT PENJUALAN BULANAN
        $profitpenjualan = Order::where('cabang_id', $cabang)
            ->whereHas('detailOrders', function ($q) {
                $q->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month)
                ->where('is_approve', 'Setuju');
            })
            ->with(['detailOrders' => function ($q) {
                $q->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                ->groupBy('orders_id');
            }])
            ->get();

        $bulanprofitbersihpenjualan = $profitpenjualan->sum(
            fn($order) => $order->detailOrders->sum('total_profit')
        );

        $bulantotalprofitbersih = $bulanprofitbersihservis + $bulanprofitbersihpenjualan;

        // PROFIT KOTOR SERVIS
        $bulanprofitkotorservis = ServiceTransaction::where('cabang_id', $cabang)
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->where('is_approve', 'Setuju')
            ->sum('profit');

        // PROFIT KOTOR PENJUALAN
        $bulanprofitkotorpenjualan = Order::where('cabang_id', $cabang)
            ->whereHas('detailOrders', function ($q) {
                $q->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month);
            })
            ->with(['detailOrders' => function ($q) {
                $q->select('orders_id', DB::raw('SUM(profit) as total_profit'))
                ->groupBy('orders_id');
            }])
            ->get()
            ->sum(fn($order) => $order->detailOrders->sum('total_profit'));

        $bulantotalprofitkotor = $bulanprofitkotorservis + $bulanprofitkotorpenjualan;

        // DATA HARIAN
        $haripembelian = Purchase::where('cabang_id', $cabang)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereDate('created_at', today())
            ->sum('total_price');

        $haripengeluaranToko = Expense::where('cabang_id', $cabang)
            ->where('tipe', 0)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->whereDate('tgl_disetujui', today())
            ->sum('price');

        $haripengeluaranServis = Expense::where('cabang_id', $cabang)
            ->where('tipe', 1)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->whereDate('tgl_disetujui', today())
            ->sum('price');
        $haripengeluaranPenjualan = Expense::where('cabang_id', $cabang)
            ->where('tipe', 2)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->whereDate('tgl_disetujui', today())
            ->sum('price');

        $hariomzetservis = ServiceTransaction::where('cabang_id', $cabang)
            ->where('status_servis', 'Sudah Diambil')
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->whereDate('tgl_disetujui', today())
            ->sum('omzet');

        $hariomzetpenjualan = OrderDetail::where('cabang_id', $cabang)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereDate('created_at', today())
            ->sum('total');

        $haritotalomzet = $hariomzetservis + $hariomzetpenjualan;

        $hariprofitkotorservis = ServiceTransaction::where('cabang_id', $cabang)
            // ->where('status_servis', 'Sudah Diambil')
            ->whereYear('tgl_ambil', now()->year)
            ->whereMonth('tgl_ambil', now()->month)
            ->whereDate('tgl_ambil', today())
            ->sum('profittoko');

        $hariprofitkotorpenjualan = OrderDetail::where('cabang_id', $cabang)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereDate('created_at', today())
            ->sum('profit');

        $haritotalprofitkotor = $hariprofitkotorservis + $hariprofitkotorpenjualan;
        // dd($bulantotalprofitkotor, $bulantotalprofitbersih);
        // TARGET & INVENTORY
        $targets = Target::where('cabang_id', $cabang)->get();
        $hasData = $targets->isNotEmpty();

        $inventories = Inventory::where('cabang_id', $cabang)
            ->where('masa_penggantian', '<', $currentDate)
            ->count();
                    // @dd($totalbudgets);

        return view('pages/kepalatoko/dashboard', compact(
            'types',
            'categories',
            'categorySales',
            'totalpengeluaran',
            'totalinsiden',
            'totalpembelian',
            'approveservis',
            'approvepenjualan',
            'approvekasbon',
            'approvepengeluaran',
            'totalbudgets',
            'haripengeluaranToko',
            'haripengeluaranServis',
            'haripengeluaranPenjualan',
            'haripembelian',
            'haritotalomzet',
            'haritotalprofitkotor',
            'bulantotalprofitbersih',
            'bulantotalprofitkotor',
            'bulanprofitbersihservis',
            'bulanprofitbersihpenjualan',
            'reminders',
            'stokhabis',
            'targets',
            'hasData',
            'inventories'
        ));
    }

    // public function index()
    // {
    //     $currentMonth = now()->month;

    //     $types = Type::with('service')->get();

    //     $categories = Category::all();

    //     $categorySales = [];
    //     foreach ($categories as $category) {
    //         $totalSales = OrderDetail::totalSales($category->id);
    //         $categorySales[] = [
    //             'category' => $category->category_name,
    //             'total_sales' => $totalSales,
    //         ];
    //     }

    //     $totalpengeluaran = Expense::whereYear('created_at', now()->year)
    //         ->whereMonth('created_at', now()->month)
    //         ->where('is_approve', 'Setuju')
    //         ->sum('price');
    //     $totalinsiden = Incident::whereYear('created_at', now()->year)
    //         ->whereMonth('created_at', now()->month)
    //         ->sum('biaya_toko');
    //     $totalpembelian = Purchase::whereYear('created_at', now()->year)
    //         ->whereMonth('created_at', now()->month)
    //         ->sum('total_price');

    //     // Ambil data transaksi servis yang memiliki status "Belum cek"
    //     $transactions = ServiceTransaction::where('status_servis', 'Belum cek')->get();

    //     // Cek apakah ada transaksi yang lebih dari 7 hari dari data dibuat
    //     $currentDate = Carbon::now();
    //     $reminderThreshold = 7; // Jumlah hari sebelum pengingat ditampilkan
    //     $reminders = $transactions->filter(function ($transaction) use ($currentDate, $reminderThreshold) {
    //         return $transaction->created_at->addDays($reminderThreshold)->isPast();
    //     })->count();

    //     $approveservis = ServiceTransaction::where('is_approve', null)
    //         ->where('status_servis', 'Sudah Diambil')
    //         ->count();
    //     $approvepenjualan = Order::where('is_approve', null)->count();
    //     $approvekasbon = Debt::where('is_approve', null)->count();
    //     $approvepengeluaran = Expense::where('is_approve', null)->count();
    //     $stokhabis = Product::where('stok', '<=', DB::raw('`stok_minimal`'))->count();

    //     $totalbudgets = Budget::all()->sum('total');

    //     $bulanprofitbersihservis = ServiceTransaction::whereYear('tgl_disetujui', now()->year)
    //         ->whereMonth('tgl_disetujui', now()->month)
    //         ->where('is_approve', 'Setuju')
    //         ->get()
    //         ->sum('profittoko');

    //     $profitpenjualan = Order::whereHas('detailOrders', function ($query) {
    //         $query->whereYear('tgl_disetujui', now()->year)
    //             ->whereMonth('tgl_disetujui', now()->month)
    //             ->where('is_approve', 'Setuju');
    //     })
    //         ->with(['detailOrders' => function ($query) {
    //             $query->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
    //                 ->groupBy('orders_id');
    //         }])
    //         ->select('id')
    //         ->get();

    //     $bulanprofitbersihpenjualan = $profitpenjualan->sum(function ($order) {
    //         return $order->detailOrders->sum('total_profit');
    //     });

    //     $bulantotalprofitbersih = ($bulanprofitbersihservis + $bulanprofitbersihpenjualan);

    //     $bulanprofitkotorservis = ServiceTransaction::whereYear('tgl_disetujui', now()->year)
    //         ->whereMonth('tgl_disetujui', now()->month)
    //         ->where('is_approve', 'Setuju')
    //         ->get()
    //         ->sum('profit');

    //     $rumusprofitkotorpenjualan = Order::whereHas('detailOrders', function ($query) {
    //         $query->whereYear('tgl_disetujui', now()->year)
    //             ->whereMonth('tgl_disetujui', now()->month);
    //     })
    //         ->with(['detailOrders' => function ($query) {
    //             $query->select('orders_id', DB::raw('SUM(profit) as total_profit'))
    //                 ->groupBy('orders_id');
    //         }])
    //         ->select('id')
    //         ->get();

    //     $bulanprofitkotorpenjualan = $rumusprofitkotorpenjualan->sum(function ($order) {
    //         return $order->detailOrders->sum('total_profit');
    //     });

    //     $bulantotalprofitkotor = ($bulanprofitkotorservis + $bulanprofitkotorpenjualan);

    //     $haripembelian = Purchase::whereYear('created_at', now()->year)
    //         ->whereMonth('created_at', now()->month)
    //         ->whereDate('created_at', today())
    //         ->sum('total_price');
    //     $haripengeluaran = Expense::where('is_approve', 'Setuju')
    //         ->whereYear('tgl_disetujui', now()->year)
    //         ->whereMonth('tgl_disetujui', now()->month)
    //         ->whereDate('tgl_disetujui', today())
    //         ->sum('price');
    //     $hariomzetservis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereYear('tgl_ambil', now()->year)
    //         ->whereMonth('tgl_ambil', now()->month)
    //         ->whereDate('tgl_ambil', today())
    //         ->get()
    //         ->sum('omzet');
    //     $hariomzetpenjualan = OrderDetail::whereYear('created_at', now()->year)
    //         ->whereMonth('created_at', now()->month)->whereDate('created_at', today())
    //         ->get()
    //         ->sum('total');
    //     $haritotalomzet = ($hariomzetservis + $hariomzetpenjualan);

    //     $hariprofitkotorservis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereYear('tgl_disetujui', now()->year)
    //         ->whereMonth('tgl_disetujui', now()->month)
    //         ->whereDate('tgl_disetujui', today())
    //         ->get()
    //         ->sum('profit');
    //     $hariprofitkotorpenjualan = OrderDetail::whereYear('created_at', now()->year)
    //         ->whereMonth('created_at', now()->month)->whereDate('created_at', today())
    //         ->get()
    //         ->sum('profit');
    //     $haritotalprofitkotor = $hariprofitkotorservis + $hariprofitkotorpenjualan;

    //     $targets = Target::all();
    //     $hasData = $targets->isNotEmpty();

    //     $inventories = Inventory::where('masa_penggantian', '<', $currentDate)->count();

    //     return view('pages/kepalatoko/dashboard', compact(
    //         'types',
    //         'categories',
    //         'categorySales',
    //         'totalpengeluaran',
    //         'totalinsiden',
    //         'totalpembelian',
    //         'approveservis',
    //         'approvepenjualan',
    //         'approvekasbon',
    //         'approvepengeluaran',
    //         'totalbudgets',
    //         'haripengeluaran',
    //         'haripembelian',
    //         'haritotalomzet',
    //         'haritotalprofitkotor',
    //         'bulantotalprofitbersih',
    //         'bulantotalprofitkotor',
    //         'bulanprofitbersihservis',
    //         'bulanprofitbersihpenjualan',
    //         'reminders',
    //         'stokhabis',
    //         'targets',
    //         'hasData',
    //         'inventories'
    //     ));
    // }
}
