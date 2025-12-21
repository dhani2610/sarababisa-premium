<?php

namespace App\Http\Controllers\Teknisi;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Models\TeknisiServis;
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
        // $column = auth()->user()->bagian_teknisi == 'Teknisi Interface' ? 'bonus_interface' : 'profit';

        $profithariInterface = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('tipe', 'Interface')
            ->where('users_id', Auth::user()->id)
            ->whereDate('tgl_disetujui', today())
            ->get()
            ->sum('bonus_interface');
        $profithari = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('tipe', 'Hardware')
            ->where('users_id', Auth::user()->id)
            ->whereDate('tgl_disetujui', today())
            ->get()
            ->sum('profit');

        $bonusTeknisiServisInterface = TeknisiServis::where('users_id', Auth::user()->id)
            ->where('tipe', 'Interface')
            ->whereHas('transaction', function ($query) use ($currentYear, $currentMonth) {
                $query->where('is_approve', 'Setuju') // Pastikan status sudah disetujui
                ->whereDate('tgl_disetujui', today());
            })
            ->sum('bonus_interface');

        $bonusTeknisiServisHardware = TeknisiServis::where('users_id', Auth::user()->id)
            ->where('tipe', 'Hardware')
            ->whereHas('transaction', function ($query) use ($currentYear, $currentMonth) {
                $query->where('is_approve', 'Setuju')
                ->whereDate('tgl_disetujui', today());
            })
            ->get()
            // Jika Anda ingin menghitung bagi hasil (profit * persen / 100):
            ->sum(function ($item) {
                // Rumus: Profit Barang * Persen Teknisi / 100
                return $item->profit;
            });

        $profithari = $profithari + $profithariInterface + $bonusTeknisiServisInterface + $bonusTeknisiServisHardware;
        // dd($bonusTeknisiServisHardware,$profithari);

        $servisbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->count();

        $profitbulanInterface = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('tipe', 'Interface')
            ->where('users_id', Auth::user()->id)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('bonus_interface');
        $profitbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('tipe', 'Hardware')
            ->where('users_id', Auth::user()->id)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('profit');
        $profitbulan = $profitbulan + $profitbulanInterface;

        $servistahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->count();

        $profittahunInterface = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('tipe', 'Interface')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->get()
            ->sum('bonus_interface');
        $profittahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->where('tipe', 'Hardware')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->get()
            ->sum('profit');
        $profittahun = $profittahun + $profittahunInterface;

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        return view('pages/teknisi/laporan-teknisi', compact('services', 'services_count', 'servishari', 'profithari', 'servisbulan', 'profitbulan', 'servistahun', 'profittahun', 'toko'));
    }
}
