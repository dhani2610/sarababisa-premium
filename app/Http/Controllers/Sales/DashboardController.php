<?php

namespace App\Http\Controllers\Sales;

use App\Models\Order;
use App\Models\Budget;
use App\Models\OrderDetail;
use App\Models\SalesTarget;
use App\Models\StoreSetting;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    /**
     * Displays the dashboard screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $target = SalesTarget::where('users_id', Auth::user()->id)->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)->sum('item');

        $result = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            ->sum('quantity');

        $profitpenjualan = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            ->sum('profit');

        $bonuspenjualan = ($profitpenjualan / 100) * Auth::user()->persen;

        $totalbonus = $bonuspenjualan;

        if ($target != 0) {
            $reward = $totalbonus * (($result / $target) * 100) / 100;
        } else {
            $reward = 0; // Atau nilai default lainnya
        }

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/sales/dashboard', compact(
            'totalbonus',
            'target',
            'result',
            'reward',
            'toko'
        ));
    }
}
