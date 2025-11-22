<?php

namespace App\Http\Controllers\Sales;

use App\Models\OrderDetail;
use App\Models\StoreSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;

        $penjualanhari = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) {
                $query->where('is_approve', 'Setuju')
                    ->whereDate('tgl_disetujui', today());
            })
            ->get()
            ->sum('quantity');
        $bonushari = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) {
                $query->where('is_approve', 'Setuju')
                    ->whereDate('tgl_disetujui', today());
            })
            ->get()
            ->sum('profit');
        $penjualanbulan = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            ->sum('quantity');
        $bonusbulan = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            ->sum('profit');
        $penjualantahun = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year);
            })
            ->get()
            ->sum('quantity');
        $bonustahun = OrderDetail::where('users_id', Auth::user()->id)
            ->whereHas('order', function ($query) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', now()->year);
            })
            ->get()
            ->sum('profit');

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/sales/laporan-penjualan', compact('penjualanhari', 'bonushari', 'penjualanbulan', 'bonusbulan', 'penjualantahun', 'bonustahun', 'toko'));
    }
}
