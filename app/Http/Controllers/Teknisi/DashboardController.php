<?php

namespace App\Http\Controllers\Teknisi;

use Carbon\Carbon;
use App\Models\StoreSetting;
use App\Models\TeknisiTarget;
use App\Models\ServiceTransaction;
use App\Models\TeknisiServis;
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
        $userId = Auth::id();

        $target = TeknisiTarget::where('users_id', $userId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('item');

        $allServisIds = servisIdMultiTeknisi($userId);

        $servisBulanList = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereIn('id', $allServisIds)
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get();

        $result = $servisBulanList->count();

        $totalbonusHardware = 0;
        $totalbonusInterface = 0;

        foreach ($servisBulanList as $item) {
            $bonus = getBonusTeknisiByTransaction($item->id, $userId);
            $relasi = TeknisiServis::where('service_transactions_id', $item->id)
                ->where('users_id', $userId)
                ->first();

            $tipe = $relasi ? $relasi->tipe : $item->tipe;
            if ($tipe === 'Hardware') {
                $totalbonusHardware += $bonus;
            } else {
                $totalbonusInterface += $bonus;
            }
        }

        $totalbonus = $totalbonusHardware + $totalbonusInterface;

        // Ambil data transaksi servis yang memiliki status "Belum cek"
        $transactions = ServiceTransaction::where('cabang_id', getCabangId())
            ->where('status_servis', 'Belum cek')
            ->get();

        // Cek apakah ada transaksi yang lebih dari 7 hari dari data dibuat
        $currentDate = Carbon::now();
        $reminderThreshold = 7; // Jumlah hari sebelum pengingat ditampilkan
        $reminders = $transactions->filter(function ($transaction) use ($currentDate, $reminderThreshold) {
            return $transaction->created_at->addDays($reminderThreshold)->isPast();
        })->count();

        if ($target != 0) {
            $reward = $totalbonus * (($result / $target) * 100) / 100;
        } else {
            $reward = 0; // Atau nilai default lainnya
        }

        $toko = StoreSetting::where('cabang_id', getCabangId())->first();

        return view('pages/teknisi/dashboard', compact(
            'totalbonus',
            'reminders',
            'totalbonusHardware',
            'totalbonusInterface',
            'target',
            'result',
            'reward',
            'toko'
        ));
    }
}
