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
use App\Models\Cabang;
use App\Models\Refund;
use Illuminate\Http\Request;

class DashboardCabangController extends Controller
{

    public function index()
    {
        return view('pages/kepalatoko/dashboard-cabang');
    }

    public function getJsonChart(Request $request)
    {
        $cabang = Cabang::orderBy('id','asc')->get();

        // RANGE YANG DIPILIH USER
        $start = $request->start_month_year;   // example: "2024-01"
        $end   = $request->end_month_year;     // example: "2024-03"

        // Jika tidak ada input → default bulan ini
        if (!$start || !$end) {
            $start = now()->format('Y-m');
            $end   = now()->format('Y-m');
        }

        // AMBIL LIST BULAN DIANTARA RANGE
        $startDate = \Carbon\Carbon::parse($start . '-01');
        $endDate   = \Carbon\Carbon::parse($end . '-01');

        $listMonths = [];
        $listMonthsIndo = [];

        $indoMonths = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        while ($startDate <= $endDate) {

            // Format Y-m (misal 2025-01)
            $listMonths[] = $startDate->format('Y-m');

            // Format Indo: Jan 25
            $bulan = $indoMonths[(int)$startDate->format('n')];
            $tahun2 = $startDate->format('y');
            $listMonthsIndo[] = $bulan . ' ' . $tahun2;

            $startDate->addMonth();
        }


        // ===============================================================
        // PRE-AGGREGATE BULK QUERIES ACROSS ALL CABANG & MONTHS (8 QUERIES TOTAL)
        // ===============================================================
        $rangeStartDate = \Carbon\Carbon::parse($start . '-01')->startOfMonth()->toDateString();
        $rangeEndDate   = \Carbon\Carbon::parse($end . '-01')->endOfMonth()->toDateString();
        $rangeStartTime = $rangeStartDate . ' 00:00:00';
        $rangeEndTime   = $rangeEndDate . ' 23:59:59';

        // 1. Targets (Anggaran) per cabang & YYYY-MM
        $targetRows = DB::table('targets')
            ->whereBetween('created_at', [$rangeStartTime, $rangeEndTime])
            ->groupBy('cabang_id', DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->selectRaw("cabang_id, DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(target) as total_target")
            ->get();
        $targetMap = [];
        foreach ($targetRows as $r) {
            $targetMap[$r->cabang_id][$r->ym] = $r->total_target;
        }

        // 2. Budgets per cabang (total overall)
        $budgetMap = DB::table('budgets')
            ->groupBy('cabang_id')
            ->selectRaw('cabang_id, SUM(total) as total_budget')
            ->pluck('total_budget', 'cabang_id');

        // 3. ServiceTransaction (profit, omzet, profittoko) per cabang & YYYY-MM
        $servisRows = DB::table('service_transactions')
            ->whereNull('deleted_at')
            ->where('is_approve', 'Setuju')
            ->whereBetween('tgl_disetujui', [$rangeStartDate, $rangeEndDate])
            ->groupBy('cabang_id', DB::raw("DATE_FORMAT(tgl_disetujui, '%Y-%m')"))
            ->selectRaw("
                cabang_id,
                DATE_FORMAT(tgl_disetujui, '%Y-%m') as ym,
                SUM(profit) as sum_profit,
                SUM(omzet) as sum_omzet,
                SUM(profittoko) as sum_profittoko
            ")
            ->get();
        $servisMap = [];
        foreach ($servisRows as $r) {
            $servisMap[$r->cabang_id][$r->ym] = $r;
        }

        // 4. Order + OrderDetail (profit_toko & total where orders.is_approve = 'Setuju') per cabang & YYYY-MM
        $orderAggRows = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->whereNull('order_details.deleted_at')
            ->whereNull('orders.deleted_at')
            ->where('orders.is_approve', 'Setuju')
            ->whereBetween('orders.tgl_disetujui', [$rangeStartDate, $rangeEndDate])
            ->groupBy('orders.cabang_id', DB::raw("DATE_FORMAT(orders.tgl_disetujui, '%Y-%m')"))
            ->selectRaw("
                orders.cabang_id as cabang_id,
                DATE_FORMAT(orders.tgl_disetujui, '%Y-%m') as ym,
                SUM(order_details.profit_toko) as sum_profit_toko,
                SUM(order_details.total) as sum_total
            ")
            ->get();
        $orderAggMap = [];
        foreach ($orderAggRows as $r) {
            $orderAggMap[$r->cabang_id][$r->ym] = $r;
        }

        // 5. Order + OrderDetail + Product (sub_total & profit_toko per kategori) per cabang & YYYY-MM
        $orderCatRows = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->leftJoin('products', function ($join) {
                $join->on('order_details.products_id', '=', 'products.id')
                     ->whereNull('products.deleted_at');
            })
            ->whereNull('order_details.deleted_at')
            ->whereNull('orders.deleted_at')
            ->whereBetween('orders.tgl_disetujui', [$rangeStartDate, $rangeEndDate])
            ->groupBy(
                'orders.cabang_id',
                DB::raw("DATE_FORMAT(orders.tgl_disetujui, '%Y-%m')"),
                DB::raw('COALESCE(products.categories_id, 4)')
            )
            ->selectRaw("
                orders.cabang_id as cabang_id,
                DATE_FORMAT(orders.tgl_disetujui, '%Y-%m') as ym,
                COALESCE(products.categories_id, 4) as cat_id,
                SUM(order_details.sub_total) as sum_sub_total,
                SUM(order_details.profit_toko) as sum_profit_toko
            ")
            ->get();
        $orderCatMap = [];
        foreach ($orderCatRows as $r) {
            $orderCatMap[$r->cabang_id][$r->ym][(int) $r->cat_id] = $r;
        }

        // 6. Pengeluaran (Expense) per cabang & YYYY-MM
        $expenseRows = DB::table('expenses')
            ->whereNull('deleted_at')
            ->where('is_approve', 'Setuju')
            ->whereBetween('tgl_disetujui', [$rangeStartDate, $rangeEndDate])
            ->groupBy('cabang_id', DB::raw("DATE_FORMAT(tgl_disetujui, '%Y-%m')"))
            ->selectRaw("cabang_id, DATE_FORMAT(tgl_disetujui, '%Y-%m') as ym, SUM(price) as sum_price")
            ->get();
        $expenseMap = [];
        foreach ($expenseRows as $r) {
            $expenseMap[$r->cabang_id][$r->ym] = $r->sum_price;
        }

        // 7. Insiden (Incident) per cabang & YYYY-MM
        $incidentRows = DB::table('incidents')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$rangeStartTime, $rangeEndTime])
            ->groupBy('cabang_id', DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->selectRaw("cabang_id, DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(price) as sum_price")
            ->get();
        $incidentMap = [];
        foreach ($incidentRows as $r) {
            $incidentMap[$r->cabang_id][$r->ym] = $r->sum_price;
        }

        // 8. Refund per cabang & YYYY-MM
        $refundRows = DB::table('refunds')
            ->whereBetween('created_at', [$rangeStartTime, $rangeEndTime])
            ->groupBy('cabang_id', DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->selectRaw("cabang_id, DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(nominal_servis) as sum_refund")
            ->get();
        $refundMap = [];
        foreach ($refundRows as $r) {
            $refundMap[$r->cabang_id][$r->ym] = $r->sum_refund;
        }

        // ===============================================================
        // BUILD RESPONSE ARRAYS (ZERO QUERIES INSIDE LOOPS)
        // ===============================================================
        $dataAnggaran = [];
        $dataPencapaian = [];
        $dataOmset = [];
        $dataOmsetService = [];
        $dataOmsetProduk = [];
        $dataProfit = [];
        $dataProfitService = [];
        $dataProdukProduk = [];
        $dataPengeluaran = [];
        $dataInsiden = [];
        $dataRefund = [];

        foreach ($cabang as $cab) {
            $cabId = $cab->id;
            $cabName = $cab->nama_cabang;
            $totalbudgets = $budgetMap[$cabId] ?? 0;

            foreach ($listMonths as $ym) {
                // 1. Anggaran
                $dataAnggaran[$cabName][$ym] = $targetMap[$cabId][$ym] ?? 0;

                // Ambil pre-aggregated Servis & Order
                $srv = $servisMap[$cabId][$ym] ?? null;
                $ord = $orderAggMap[$cabId][$ym] ?? null;

                // 2. Pencapaian
                $servisProfit = $srv ? $srv->sum_profit : 0;
                $penjualanProfitBersih = $ord ? ($ord->sum_profit_toko + 0) : 0;
                $totalProfitUntukPencapaian = $servisProfit + $penjualanProfitBersih;
                $percent = $totalbudgets != 0 ? round(($totalProfitUntukPencapaian / $totalbudgets) * 100) : 0;
                $dataPencapaian[$cabName][$ym] = $percent;

                // 3. Omset & Omset Service
                $servisOmset = $srv ? $srv->sum_omzet : 0;
                $penjualanOmset = $ord ? ($ord->sum_total + 0) : 0;
                $dataOmset[$cabName][$ym] = [
                    'omset' => $servisOmset + $penjualanOmset,
                ];
                $dataOmsetService[$cabName][$ym] = [
                    'omset' => $servisOmset,
                ];

                // 4. Omset Produk & Profit Produk per Kategori
                $kategoriOmset = [
                    1 => ['nama' => 'Handphone', 'total_omset' => 0],
                    2 => ['nama' => 'Sparepart', 'total_omset' => 0],
                    3 => ['nama' => 'Aksesoris', 'total_omset' => 0],
                    4 => ['nama' => 'Tools', 'total_omset' => 0],
                ];
                $kategoriProfit = [
                    1 => ['nama' => 'Handphone', 'total_profit' => 0],
                    2 => ['nama' => 'Sparepart', 'total_profit' => 0],
                    3 => ['nama' => 'Aksesoris', 'total_profit' => 0],
                    4 => ['nama' => 'Tools', 'total_profit' => 0],
                ];

                if (!empty($orderCatMap[$cabId][$ym])) {
                    foreach ($orderCatMap[$cabId][$ym] as $catId => $catRow) {
                        if (!isset($kategoriOmset[$catId])) {
                            $kategoriOmset[$catId]['total_omset'] = 0;
                            $kategoriProfit[$catId]['total_profit'] = 0;
                        }
                        $kategoriOmset[$catId]['total_omset'] += ($catRow->sum_sub_total + 0);
                        $kategoriProfit[$catId]['total_profit'] += ($catRow->sum_profit_toko + 0);
                    }
                }

                $dataOmsetProduk[$cabName][$ym] = $kategoriOmset;
                $dataProdukProduk[$cabName][$ym] = $kategoriProfit;

                // 5. Profit & Profit Service
                $servisProfitToko = $srv ? $srv->sum_profittoko : 0;
                $dataProfit[$cabName][$ym] = [
                    'profit' => $servisProfitToko + $penjualanProfitBersih,
                ];
                $dataProfitService[$cabName][$ym] = [
                    'omset' => $servisProfitToko,
                ];

                // 6. Pengeluaran, Insiden, Refund
                $dataPengeluaran[$cabName][$ym] = isset($expenseMap[$cabId][$ym]) ? ($expenseMap[$cabId][$ym] + 0) : 0;
                $dataInsiden[$cabName][$ym] = isset($incidentMap[$cabId][$ym]) ? ($incidentMap[$cabId][$ym] + 0) : 0;
                $dataRefund[$cabName][$ym] = isset($refundMap[$cabId][$ym]) ? ($refundMap[$cabId][$ym] + 0) : 0;
            }
        }

        return response()->json([
            'range' => $listMonths,
            'rangeIndo' => $listMonthsIndo,
            'dataPencapaian'  => $dataPencapaian,
            'dataAnggaran'  => $dataAnggaran,
            'dataOmset'  => $dataOmset,
            'dataOmsetService'  => $dataOmsetService,
            'dataOmsetProduk'  => $dataOmsetProduk,
            'dataProfit'  => $dataProfit,
            'dataProfitService'  => $dataProfitService,
            'dataProdukProduk'  => $dataProdukProduk,
            'dataPengeluaran'  => $dataPengeluaran,
            'dataInsiden'  => $dataInsiden,
            'dataRefund'  => $dataRefund,
        ]);

    }
}
