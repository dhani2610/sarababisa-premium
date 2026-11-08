<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Type;
use App\Models\Order;
use App\Models\Budget;
use App\Models\Worker;
use App\Models\User;
use App\Models\TeknisiServis;
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
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    /**
     * Displays the dashboard screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $currentMonth = now()->month;
        $cabang = getCabangId();
        $currentDate = Carbon::now();

        if ($request->has('filter_month') && $request->filter_month != '') {
            $date = Carbon::parse($request->filter_month);
        } else {
            $date = Carbon::now();
        }

        $year = $date->year;
        $month = $date->month;
        $monthStart = $date->copy()->startOfMonth()->toDateString();
        $monthEnd = $date->copy()->endOfMonth()->toDateString();
        $monthStartTime = $monthStart . ' 00:00:00';
        $monthEndTime = $monthEnd . ' 23:59:59';

        $todayStr = today()->toDateString();
        $todayStartTime = $todayStr . ' 00:00:00';
        $todayEndTime = $todayStr . ' 23:59:59';

        // TYPES & PROFIT SERVIS PER TYPE (1 grouped query using index-friendly date range)
        $types = Type::where('cabang_id', $cabang)->get();
        $typeProfits = DB::table('service_transactions')
            ->whereNull('deleted_at')
            ->where('cabang_id', $cabang)
            ->where('is_approve', 'Setuju')
            ->whereBetween('tgl_disetujui', [$monthStart, $monthEnd])
            ->whereNotNull('types_id')
            ->groupBy('types_id')
            ->selectRaw('types_id, COALESCE(SUM(profit), 0) as total_profit')
            ->pluck('total_profit', 'types_id');

        foreach ($types as $t) {
            $t->filtered_profit = (float) ($typeProfits[$t->id] ?? 0);
        }

        // CATEGORIES & CATEGORY SALES (1 grouped query using index-friendly date range)
        $categories = Category::get();
        $catSalesMap = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->join('products', 'order_details.products_id', '=', 'products.id')
            ->whereNull('order_details.deleted_at')
            ->whereNull('orders.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('orders.cabang_id', $cabang)
            ->where('orders.is_approve', 'Setuju')
            ->whereBetween('orders.tgl_disetujui', [$monthStart, $monthEnd])
            ->groupBy('products.categories_id')
            ->selectRaw('products.categories_id as cat_id, COALESCE(SUM(order_details.profit_toko), 0) as total_sales')
            ->pluck('total_sales', 'cat_id');

        $categorySales = [];
        foreach ($categories as $category) {
            $categorySales[] = [
                'category' => $category->category_name,
                'total_sales' => (float) ($catSalesMap[$category->id] ?? 0),
            ];
        }

        // PENGELUARAN (Bulanan, Harian, dan Approval dalam 1 query agregat)
        $expenseStats = DB::table('expenses')
            ->whereNull('deleted_at')
            ->where('cabang_id', $cabang)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN is_approve = 'Setuju' AND created_at BETWEEN ? AND ? THEN price ELSE 0 END), 0) as total_pengeluaran,
                COUNT(CASE WHEN is_approve IS NULL THEN 1 END) as approve_pengeluaran,
                COALESCE(SUM(CASE WHEN tipe = 0 AND created_at BETWEEN ? AND ? THEN price ELSE 0 END), 0) as hari_toko,
                COALESCE(SUM(CASE WHEN tipe = 1 AND created_at BETWEEN ? AND ? THEN price ELSE 0 END), 0) as hari_servis,
                COALESCE(SUM(CASE WHEN tipe = 2 AND is_approve = 'Setuju' AND created_at BETWEEN ? AND ? THEN price ELSE 0 END), 0) as hari_penjualan
            ", [$monthStartTime, $monthEndTime, $todayStartTime, $todayEndTime, $todayStartTime, $todayEndTime, $todayStartTime, $todayEndTime])
            ->first();

        $totalpengeluaran = (float) ($expenseStats->total_pengeluaran ?? 0);
        $approvepengeluaran = (int) ($expenseStats->approve_pengeluaran ?? 0);
        $haripengeluaranToko = (float) ($expenseStats->hari_toko ?? 0);
        $haripengeluaranServis = (float) ($expenseStats->hari_servis ?? 0);
        $haripengeluaranPenjualan = (float) ($expenseStats->hari_penjualan ?? 0);

        $totalinsiden = Incident::where('cabang_id', $cabang)
            ->whereBetween('created_at', [$monthStartTime, $monthEndTime])
            ->sum('biaya_toko');

        // PEMBELIAN (Bulanan & Harian dalam 1 query)
        $purchaseStats = DB::table('purchases')
            ->where('cabang_id', $cabang)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_price ELSE 0 END), 0) as bulan_pembelian,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_price ELSE 0 END), 0) as hari_pembelian
            ", [$monthStartTime, $monthEndTime, $todayStartTime, $todayEndTime])
            ->first();

        $totalpembelian = (float) ($purchaseStats->bulan_pembelian ?? 0);
        $haripembelian = (float) ($purchaseStats->hari_pembelian ?? 0);

        // REMINDER SERVIS, APPROVAL SERVIS, PROFIT BULANAN & HARIAN SERVIS (1 query agregat)
        $reminderCutoff = $currentDate->copy()->subDays(7)->toDateTimeString();
        $servisStats = DB::table('service_transactions')
            ->whereNull('deleted_at')
            ->where('cabang_id', $cabang)
            ->selectRaw("
                COUNT(CASE WHEN status_servis = 'Belum cek' AND created_at < ? THEN 1 END) as reminders,
                COUNT(CASE WHEN is_approve IS NULL AND status_servis = 'Sudah Diambil' THEN 1 END) as approve_servis,
                COALESCE(SUM(CASE WHEN is_approve = 'Setuju' AND tgl_disetujui BETWEEN ? AND ? THEN profit ELSE 0 END), 0) as bulan_profit,
                COALESCE(SUM(CASE WHEN status_servis = 'Sudah Diambil' AND tgl_ambil BETWEEN ? AND ? THEN omzet ELSE 0 END), 0) as hari_omzet,
                COALESCE(SUM(CASE WHEN status_servis = 'Sudah Diambil' AND tgl_ambil BETWEEN ? AND ? THEN profit ELSE 0 END), 0) as hari_profit
            ", [$reminderCutoff, $monthStart, $monthEnd, $todayStr, $todayEndTime, $todayStr, $todayEndTime])
            ->first();

        $reminders = (int) ($servisStats->reminders ?? 0);
        $approveservis = (int) ($servisStats->approve_servis ?? 0);
        $bulanprofitbersihservis = (float) ($servisStats->bulan_profit ?? 0);
        $bulanprofitkotorservis = $bulanprofitbersihservis;
        $hariomzetservis = (float) ($servisStats->hari_omzet ?? 0);
        $hariprofitkotorservis = (float) ($servisStats->hari_profit ?? 0);

        $approvepenjualan = Order::where('cabang_id', $cabang)
            ->where('is_approve', null)->count();

        $approvekasbon = Debt::where('cabang_id', $cabang)
            ->where('is_approve', null)->count();

        // STOK HABIS
        $stokhabis = Product::where('cabang_id', $cabang)
            ->where('stok', '<=', DB::raw('`stok_minimal`'))->count();

        // TOTAL BUDGET
        $totalbudgets = Budget::where('cabang_id', $cabang)->sum('total');

        // PROFIT PENJUALAN BULANAN (Bersih & Kotor dalam 1 query JOIN dengan index-friendly BETWEEN)
        $orderProfitStats = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->whereNull('order_details.deleted_at')
            ->whereNull('orders.deleted_at')
            ->where('orders.cabang_id', $cabang)
            ->whereBetween('orders.tgl_disetujui', [$monthStart, $monthEnd])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN orders.is_approve = 'Setuju' THEN order_details.profit_toko ELSE 0 END), 0) as profit_bersih,
                COALESCE(SUM(order_details.profit), 0) as profit_kotor
            ")
            ->first();

        $bulanprofitbersihpenjualan = (float) ($orderProfitStats->profit_bersih ?? 0);
        $bulanprofitkotorpenjualan = (float) ($orderProfitStats->profit_kotor ?? 0);

        $bulantotalprofitbersih = $bulanprofitbersihservis + $bulanprofitbersihpenjualan;
        $bulantotalprofitkotor = $bulanprofitkotorservis + $bulanprofitkotorpenjualan;

        // OMZET & PROFIT PENJUALAN HARIAN (1 query dengan index-friendly BETWEEN)
        $hariPenjualanStats = DB::table('order_details')
            ->whereNull('deleted_at')
            ->where('cabang_id', $cabang)
            ->whereBetween('created_at', [$todayStartTime, $todayEndTime])
            ->selectRaw('COALESCE(SUM(total), 0) as hari_omzet, COALESCE(SUM(profit), 0) as hari_profit')
            ->first();

        $hariomzetpenjualan = (float) ($hariPenjualanStats->hari_omzet ?? 0);
        $hariprofitkotorpenjualan = (float) ($hariPenjualanStats->hari_profit ?? 0);

        $haritotalomzet = $hariomzetservis + $hariomzetpenjualan;
        $haritotalprofitkotor = $hariprofitkotorservis + $hariprofitkotorpenjualan;

        // TARGET & INVENTORY
        $targets = Target::where('cabang_id', $cabang)->get();
        $hasData = $targets->isNotEmpty();

        $inventories = Inventory::where('cabang_id', $cabang)
            ->where('masa_penggantian', '<', $currentDate)
            ->count();

        // TOTAL BONUS KARYAWAN (Bulk calculation instead of N+1 per worker per transaction)
        $worker = Worker::where('cabang_id', $cabang)->get();
        $total_bonus_karyawan = calculateTotalWorkerBonusForCabang($worker, $cabang, $monthStart, $monthEnd);

        // dd($total_bonus_karyawan);
        return view('pages/kepalatoko/dashboard', compact(
            'types',
            'total_bonus_karyawan',
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

    public function calculateBonus($id, $start_date, $end_date)
    {
        $user = User::find($id);
        $bonus = 0;

        if ($user->role === 'Teknisi') {
            // $total_bonus_interface_main = ServiceTransaction::where('users_id', $user->id)
            //     ->where('status_servis', 'Sudah Diambil')
            //     ->where('is_approve', 'Setuju')
            //     ->where('cabang_id', getCabangId())
            //     ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
            //     ->whereDate('tgl_ambil', '>=', $start_date)
            //     ->whereDate('tgl_ambil', '<=', $end_date)
            //     ->sum('bonus_interface');

            // $total_profit_hardware_main = ServiceTransaction::where('users_id', $user->id)
            //     ->where('status_servis', 'Sudah Diambil')
            //     ->where('is_approve', 'Setuju')
            //     ->where('cabang_id', getCabangId())
            //     ->where('tipe', 'Hardware')
            //     ->whereDate('tgl_ambil', '>=', $start_date)
            //     ->whereDate('tgl_ambil', '<=', $end_date)
            //     ->sum('profit');

            // $bonus_hardware_main = ($total_profit_hardware_main / 100) * $user->persen;

            // $total_bonus_interface_detail = TeknisiServis::where('users_id', $user->id)
            //     ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
            //     ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
            //         $query->where('is_approve', 'Setuju')
            //             ->where('status_servis', 'Sudah Diambil')
            //             ->whereDate('tgl_ambil', '>=', $start_date)
            //             ->whereDate('tgl_ambil', '<=', $end_date);
            //     })
            //     ->sum('bonus_interface');

            // $total_bonus_hardware_detail = TeknisiServis::where('users_id', $user->id)
            //     ->where('tipe', 'Hardware')
            //     ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
            //         $query->where('is_approve', 'Setuju')
            //             ->where('status_servis', 'Sudah Diambil')
            //             ->whereDate('tgl_ambil', '>=', $start_date)
            //             ->whereDate('tgl_ambil', '<=', $end_date);
            //     })
            //     ->get()
            //     ->sum(function ($item) {
            //         return $item->profit * ($item->persen_teknisi / 100);
            //     });

            // $bonus = $total_bonus_interface_main + $bonus_hardware_main + $total_bonus_interface_detail + $total_bonus_hardware_detail;

            $bonus_cek = ($user->filteredServiceTransaction->where('tipe','Hardware')->sum('profit') / 100) * $user->persen;
            $bonus = $bonus_cek + $user->filteredServiceTransaction->where('tipe','Interface')->sum('bonus_interface');

        } elseif ($user->role === 'Sales') {
            $bonus = $user->filteredSale->sum('profit') / 100;
            $bonus *= $user->persen;

        } else {
            $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen'; // default biar aman
            $persen = $user->persen ?? 0;
            $nominalBonus = $user->nominal_bonus_admin ?? 0;

            $totalProfitService = $user->filteredAdminService->sum('profit');
            $totalProfitSale = $user->filteredAdminSale->sum('profit');
            $totalNotaService = $user->filteredAdminService->count();
            $totalNotaSale = $user->filteredAdminSale->count();

            if ($tipeBonusNota === 'Persen') {
                $bonus = (($totalProfitService + $totalProfitSale) / 100) * $persen;
            } elseif ($tipeBonusNota === 'Tetap') {
                $totalNota = $totalNotaService + $totalNotaSale;
                $bonus = $totalNota * $nominalBonus;
            } else {
                $bonus = 0;
            }
        }

        return $bonus;
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
