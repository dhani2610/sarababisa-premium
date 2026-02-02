<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Order;
use App\Models\Budget;
use App\Models\Target;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TargetBulanSebelumnyaController extends Controller
{
    public function store(Request $request)
    {
        // dd('msk');
        $target = Budget::all()->sum('total');

        $lastMonth = now()->subMonth(); // Mendapatkan tanggal bulan sebelumnya

        $year = $lastMonth->year;
        $month = $lastMonth->month;

        $date = $lastMonth->endOfMonth(); // Set the value of $date to the last date of the previous month

        $bulanprofitbersihservis = ServiceTransaction::where('cabang_id',getCabangId())->whereYear('tgl_disetujui', $year)
            ->whereMonth('tgl_disetujui', $month)
            ->where('is_approve', 'Setuju')
            ->get()
            ->sum('profit');

        $profitpenjualan = Order::where('cabang_id',getCabangId())->whereHas('detailOrders', function ($query) use ($year, $month) {
            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('is_approve', 'Setuju');
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $bulanprofitbersihpenjualan = $profitpenjualan->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $bulantotalprofitbersih = ($bulanprofitbersihservis + $bulanprofitbersihpenjualan);

        $persen = round(($bulantotalprofitbersih / $target) * 100);

        Target::create([
            'cabang_id' => getCabangId(),
            'target' => $target,
            'persen' => $persen,
            'nilai' => $bulantotalprofitbersih,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        return redirect()->route('target.index');
    }
}
