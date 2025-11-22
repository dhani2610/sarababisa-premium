<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataTargetController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function getDataTarget()
    {
        $monthlyData = DB::table('targets')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('SUM(target) as target'), DB::raw('SUM(nilai) as nilai'))
            ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('YEAR(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->where('cabang_id',getCabangId())
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        $labels = [];
        $target = [];
        $nilai = [];

        foreach ($monthlyData as $data) {
            $month = date('Y-m-d', mktime(0, 0, 0, $data->month, 1, $data->year));
            $labels[] = $month;
            $target[] = $data->target;
            $nilai[] = $data->nilai;
        }

        $data = [
            'labels' => $labels,
            'target' => $target,
            'nilai' => $nilai,
        ];

        return response()->json($data); // Mengembalikan data dalam format JSON
    }
}
