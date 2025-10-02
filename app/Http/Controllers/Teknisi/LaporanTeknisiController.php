<?php

namespace App\Http\Controllers\Teknisi;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LaporanTeknisiController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $services = ServiceTransaction::with('serviceaction', 'user')->where('is_approve', 'Setuju')->where('users_id', Auth::user()->id)->orderByDesc('tgl_ambil')->get();
        $services_count = ServiceTransaction::with('serviceaction')->where('is_approve', 'Setuju')->count();
        $servishari = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereDate('tgl_disetujui', today())
            ->count();
        $column = auth()->user()->bagian_teknisi == 'Teknisi Interface' ? 'bonus_interface' : 'profit';

        $profithari = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereDate('tgl_disetujui', today())
            ->get()
            ->sum($column);
        $servisbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->count();
        $profitbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum($column);
        $servistahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->count();
        $profittahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->get()
            ->sum($column);

        $toko = StoreSetting::find(1);
        return view('pages/teknisi/laporan-teknisi', compact('services', 'services_count', 'servishari', 'profithari', 'servisbulan', 'profitbulan', 'servistahun', 'profittahun', 'toko'));
    }
}
