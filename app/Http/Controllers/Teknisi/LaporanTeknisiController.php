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
        $userId = Auth::id();

        $allServisIds = servisIdMultiTeknisi($userId);

        $services = ServiceTransaction::with('serviceaction', 'user')
            ->where('is_approve', 'Setuju')
            ->whereIn('id', $allServisIds)
            ->orderByDesc('tgl_ambil')
            ->get();

        $services_count = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereIn('id', $allServisIds)
            ->count();

        // Hari ini
        $servisHariList = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereIn('id', $allServisIds)
            ->whereDate('tgl_disetujui', today())
            ->get();
        $servishari = $servisHariList->count();
        $profithari = 0;
        foreach ($servisHariList as $item) {
            $profithari += getBonusTeknisiByTransaction($item->id, $userId);
        }

        // Bulan ini
        $servisBulanList = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereIn('id', $allServisIds)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->whereYear('tgl_disetujui', $currentYear)
            ->get();
        $servisbulan = $servisBulanList->count();
        $profitbulan = 0;
        foreach ($servisBulanList as $item) {
            $profitbulan += getBonusTeknisiByTransaction($item->id, $userId);
        }

        // Tahun ini
        $servisTahunList = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereIn('id', $allServisIds)
            ->whereYear('tgl_disetujui', $currentYear)
            ->get();
        $servistahun = $servisTahunList->count();
        $profittahun = 0;
        foreach ($servisTahunList as $item) {
            $profittahun += getBonusTeknisiByTransaction($item->id, $userId);
        }

        $toko = StoreSetting::where('cabang_id', getCabangId())->first();
        return view('pages/teknisi/laporan-teknisi', compact('services', 'services_count', 'servishari', 'profithari', 'servisbulan', 'profitbulan', 'servistahun', 'profittahun', 'toko'));
    }
}
