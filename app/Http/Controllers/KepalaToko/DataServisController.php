<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceTransaction;

class DataServisController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function getDataServis()
    {
        $monthlyData = DB::table('service_transactions')
            ->select(DB::raw('MONTH(tgl_disetujui) as month'), DB::raw('YEAR(tgl_disetujui) as year'), DB::raw('SUM(omzet) as total_omzet'), DB::raw('SUM(profittoko) as total_profit_bersih'), DB::raw('SUM(profit) as total_profit_kotor'))
            ->where('is_approve', 'Setuju')
            ->where('cabang_id',getCabangId())
            ->whereNull('deleted_at') // Menambahkan kondisi where untuk memfilter data yang deleted_at-nya NULL
            ->groupBy(DB::raw('MONTH(tgl_disetujui)'), DB::raw('YEAR(tgl_disetujui)'))
            ->orderBy(DB::raw('YEAR(tgl_disetujui)'), 'asc')
            ->orderBy(DB::raw('MONTH(tgl_disetujui)'), 'asc')
            ->get();

        $labels = [];
        $omzet = [];
        $profitkotor = [];
        $profitbersih = [];

        foreach ($monthlyData as $data) {
            $month = date('Y-m-d', mktime(0, 0, 0, $data->month, 1, $data->year));
            $labels[] = $month;

            $bulanprofitbersihservis = ServiceTransaction::where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', $data->year)
            ->whereMonth('tgl_disetujui', $data->month)
            ->where('is_approve', 'Setuju')
            ->sum('profit');

             $bulanprofitkotorservis = ServiceTransaction::where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', $data->year)
            ->whereMonth('tgl_disetujui', $data->month)
            ->where('is_approve', 'Setuju')
            ->sum('profit');

            $omzetservis = ServiceTransaction::where('cabang_id', getCabangId())
            ->where('status_servis', 'Sudah Diambil')
            ->whereYear('tgl_disetujui', $data->year)
            ->whereMonth('tgl_disetujui', $data->month)
            ->sum('omzet');


            $omzet[] = $omzetservis;
            $profitkotor[] = $bulanprofitkotorservis;
            $profitbersih[] = $bulanprofitbersihservis;
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
