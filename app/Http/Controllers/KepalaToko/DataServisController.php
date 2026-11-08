<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceTransaction;
use App\Models\Expense;

class DataServisController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function getDataServis()
    {
        $cabangId = getCabangId();

        $monthlyData = DB::table('service_transactions')
            ->select(
                DB::raw('MONTH(tgl_disetujui) as month'),
                DB::raw('YEAR(tgl_disetujui) as year'),
                DB::raw('SUM(profit) as total_profit_kotor')
            )
            ->where('is_approve', 'Setuju')
            ->where('cabang_id', $cabangId)
            ->whereNull('deleted_at')
            ->groupBy(DB::raw('MONTH(tgl_disetujui)'), DB::raw('YEAR(tgl_disetujui)'))
            ->orderBy(DB::raw('YEAR(tgl_disetujui)'), 'asc')
            ->orderBy(DB::raw('MONTH(tgl_disetujui)'), 'asc')
            ->get();

        // Bulk aggregate omzet where status_servis = 'Sudah Diambil' by year/month of tgl_disetujui
        $omzetByYm = DB::table('service_transactions')
            ->whereNull('deleted_at')
            ->where('cabang_id', $cabangId)
            ->where('status_servis', 'Sudah Diambil')
            ->whereNotNull('tgl_disetujui')
            ->groupBy(DB::raw('YEAR(tgl_disetujui)'), DB::raw('MONTH(tgl_disetujui)'))
            ->selectRaw("CONCAT(YEAR(tgl_disetujui), '-', LPAD(MONTH(tgl_disetujui), 2, '0')) as ym, SUM(omzet) as total_omzet")
            ->pluck('total_omzet', 'ym');

        // Bulk aggregate expense tipe=1 by year/month of created_at
        $expenseByYm = DB::table('expenses')
            ->whereNull('deleted_at')
            ->where('cabang_id', $cabangId)
            ->where('tipe', 1)
            ->whereNotNull('created_at')
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->selectRaw("CONCAT(YEAR(created_at), '-', LPAD(MONTH(created_at), 2, '0')) as ym, SUM(price) as total_price")
            ->pluck('total_price', 'ym');

        $labels = [];
        $omzet = [];
        $profitkotor = [];
        $profitbersih = [];

        foreach ($monthlyData as $data) {
            $month = date('Y-m-d', mktime(0, 0, 0, $data->month, 1, $data->year));
            $labels[] = $month;

            $ymKey = sprintf('%04d-%02d', (int) $data->year, (int) $data->month);
            $bulanprofitkotorservis = $data->total_profit_kotor ?? 0;
            $operasionalServis = $expenseByYm[$ymKey] ?? 0;
            $omzetservis = $omzetByYm[$ymKey] ?? 0;

            $omzet[] = $omzetservis;
            $profitkotor[] = $bulanprofitkotorservis;
            $profitbersih[] = $bulanprofitkotorservis - $operasionalServis;
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
