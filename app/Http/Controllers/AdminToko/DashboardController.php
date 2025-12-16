<?php

namespace App\Http\Controllers\AdminToko;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Budget;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\OrderDetail;
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

        $adminbiayaservis = ServiceTransaction::where('is_admin_toko', 'Admin')
            ->where('admin_id', Auth::user()->id)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('profit');

        $adminprofitpenjualan = OrderDetail::where('is_admin_toko', 'Admin')
            ->where('admin_id', Auth::user()->id)
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            ->sum('profit');

        $adminNotaServis = ServiceTransaction::where('is_admin_toko', 'Admin')
            ->where('admin_id', Auth::user()->id)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->count();
        $adminNotaPenjualan = OrderDetail::where('is_admin_toko', 'Admin')
            ->where('admin_id', Auth::user()->id)
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            ->count();

        $user = Auth::user();

        $tipeBonusNota = $user->tipe_bonus_admin; // 'Persen' atau 'Tetap'
        $nominalBonusNota = $user->nominal_bonus_admin ?? 0;
        $persen = $user->persen ?? 0;

        if ($tipeBonusNota === 'Persen') {
            $totalbonus = (($adminbiayaservis + $adminprofitpenjualan) / 100) * $persen;
        } elseif ($tipeBonusNota === 'Tetap') {
            $totalNota = $adminNotaServis + $adminNotaPenjualan;
            $totalbonus = $totalNota * $nominalBonusNota;
        } else {
            $totalbonus = 0;
        }
        // dd($tipeBonusNota,$totalNota,$totalbonus);
        // $totalbonus = ($adminbiayaservis / 100 + $adminprofitpenjualan / 100) * Auth::user()->persen;
        // dd(($adminbiayaservis / 100 + $adminprofitpenjualan / 100),$adminbiayaservis,$adminprofitpenjualan,$totalbonus);

        $totalbudgets = Budget::where('cabang_id',getCabangId())->get()->sum('total');
        $totalbiayaservis = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('profittoko');

        $rumustotalpenjualan = Order::whereHas('detailOrders', function ($query) {
            $query->where('is_approve', 'Setuju')
                ->whereYear('tgl_disetujui', now()->year)
                ->whereMonth('tgl_disetujui', now()->month);
        })
            ->with(['detailOrders' => function ($query) {
                $query->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $totalpenjualan = $rumustotalpenjualan->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $totalprofit = $totalbiayaservis + $totalpenjualan;

        // Ambil data transaksi servis yang memiliki status "Belum cek"
        $transactions = ServiceTransaction::where('status_servis', 'Belum cek')->get();

        // Cek apakah ada transaksi yang lebih dari 7 hari dari data dibuat
        $currentDate = Carbon::now();
        $reminderThreshold = 7; // Jumlah hari sebelum pengingat ditampilkan
        $reminders = $transactions->filter(function ($transaction) use ($currentDate, $reminderThreshold) {
            return $transaction->created_at->addDays($reminderThreshold)->isPast();
        })->count();

        $stokhabis = Product::where('cabang_id',getCabangId())->where('stok', 0)->count();

        $inventories = Inventory::where('cabang_id',getCabangId())->where('masa_penggantian', '<', $currentDate)->count();

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/admintoko/dashboard', compact(
            'totalbiayaservis',
            'totalbudgets',
            'totalprofit',
            'totalpenjualan',
            'totalbonus',
            'reminders',
            'stokhabis',
            'inventories',
            'toko'
        ));
    }
}
