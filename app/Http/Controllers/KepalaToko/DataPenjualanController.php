<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataPenjualanController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function getDataPenjualan()
    {
        $monthlyData = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->select(DB::raw('MONTH(order_details.created_at) as month'), DB::raw('YEAR(order_details.created_at) as year'), DB::raw('SUM(order_details.total) as total_omzet'), DB::raw('SUM(order_details.profit_toko) as total_profit_bersih'), DB::raw('SUM(order_details.profit) as total_profit_kotor'))
            ->where('orders.is_approve', 'Setuju')
            ->where('orders.cabang_id',getCabangId())
            ->whereNull('order_details.deleted_at') // Menambahkan kondisi where untuk memfilter data yang deleted_at-nya NULL
            ->groupBy(DB::raw('MONTH(order_details.created_at)'), DB::raw('YEAR(order_details.created_at)'))
            ->orderBy(DB::raw('YEAR(order_details.created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(order_details.created_at)'), 'asc')
            ->get();

        $labels = [];
        $omzet = [];
        $profitkotor = [];
        $profitbersih = [];

        foreach ($monthlyData as $data) {
            $month = date('Y-m-d', mktime(0, 0, 0, $data->month, 1, $data->year));
            $labels[] = $month;
            $omzet[] = $data->total_omzet;
            $profitkotor[] = $data->total_profit_kotor;
            $profitbersih[] = $data->total_profit_bersih;
        }

        $data = [
            'labels' => $labels,
            'omzet' => $omzet,
            'profitkotor' => $profitkotor,
            'profitbersih' => $profitbersih,
        ];

        return response()->json($data); // Mengembalikan data dalam format JSON
    }
}
