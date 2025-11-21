<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataTargetPersenController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function getDataTargetPersen()
    {
        $monthlyData = DB::table('targets')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('SUM(persen) as persen'))
            ->where('cabang_id',getCabangId())
            ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('YEAR(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        $labels = [];
        $persen = [];

        foreach ($monthlyData as $data) {
            $month = date('Y-m-d', mktime(0, 0, 0, $data->month, 1, $data->year));
            $labels[] = $month;
            $persen[] = $data->persen;
        }

        $data = [
            'labels' => $labels,
            'persen' => $persen,
        ];

        return response()->json($data); // Mengembalikan data dalam format JSON
    }
}
