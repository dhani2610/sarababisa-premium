<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetail;
use App\Models\Order;

class DataPenjualanController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function getDataPenjualan()
    {
        $cabangId = getCabangId();

        $monthlyData = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->select(
                DB::raw('MONTH(order_details.created_at) as month'),
                DB::raw('YEAR(order_details.created_at) as year'),
                DB::raw('SUM(order_details.total) as total_omzet')
            )
            ->where('orders.is_approve', 'Setuju')
            ->where('orders.cabang_id', $cabangId)
            ->whereNull('order_details.deleted_at')
            ->groupBy(DB::raw('MONTH(order_details.created_at)'), DB::raw('YEAR(order_details.created_at)'))
            ->orderBy(DB::raw('YEAR(order_details.created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(order_details.created_at)'), 'asc')
            ->get();

        // Bulk aggregate profit_kotor and profit_bersih by YEAR/MONTH of orders.tgl_disetujui
        $profitByYm = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->whereNull('order_details.deleted_at')
            ->whereNull('orders.deleted_at')
            ->where('orders.cabang_id', $cabangId)
            ->whereNotNull('orders.tgl_disetujui')
            ->groupBy(DB::raw('YEAR(orders.tgl_disetujui)'), DB::raw('MONTH(orders.tgl_disetujui)'))
            ->selectRaw("
                CONCAT(YEAR(orders.tgl_disetujui), '-', LPAD(MONTH(orders.tgl_disetujui), 2, '0')) as ym,
                COALESCE(SUM(order_details.profit), 0) as profit_kotor,
                COALESCE(SUM(CASE WHEN orders.is_approve = 'Setuju' THEN order_details.profit_toko ELSE 0 END), 0) as profit_bersih
            ")
            ->get()
            ->keyBy('ym');

        $labels = [];
        $omzet = [];
        $profitkotor = [];
        $profitbersih = [];

        foreach ($monthlyData as $data) {
            $month = date('Y-m-d', mktime(0, 0, 0, $data->month, 1, $data->year));
            $labels[] = $month;

            $ymKey = sprintf('%04d-%02d', (int) $data->year, (int) $data->month);
            $pRow = $profitByYm->get($ymKey);

            $omzet[] = $data->total_omzet;
            $profitkotor[] = $pRow ? (int) $pRow->profit_kotor : 0;
            $profitbersih[] = $pRow ? (int) $pRow->profit_bersih : 0;
        }

        $data = [
            'labels' => $labels,
            'omzet' => $omzet,
            'profitkotor' => $profitkotor,
            'profitbersih' => $profitbersih,
        ];

        return response()->json($data);
    }
}
