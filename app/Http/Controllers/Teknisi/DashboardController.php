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

        $target = TeknisiTarget::where('users_id', Auth::user()->id)->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)->sum('item');

        $result = ServiceTransaction::where('users_id', Auth::user()->id)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->count();
        $servis = ServiceTransaction::where('users_id', Auth::user()->id)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get();

        $bonusServisInterface = ServiceTransaction::with('serviceaction')
            ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('bonus_interface');

        // 1. Hitung Bonus Interface (Multi Teknisi)
        $teknisiServisInterface = TeknisiServis::where('users_id', Auth::user()->id)
            ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
            ->whereHas('transaction', function ($query) use ($currentYear, $currentMonth) {
                $query->where('is_approve', 'Setuju') // Pastikan status sudah disetujui
                    ->whereYear('tgl_disetujui', $currentYear)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->sum('bonus_interface');

        $teknisiServisHardware = TeknisiServis::where('users_id', Auth::user()->id)
            ->where('tipe', 'Hardware')
            ->whereHas('transaction', function ($query) use ($currentYear, $currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->whereYear('tgl_disetujui', $currentYear)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->get()
            // Jika Anda ingin menghitung bagi hasil (profit * persen / 100):
            ->sum(function ($item) {
                // Rumus: Profit Barang * Persen Teknisi / 100
                return $item->profit * ($item->persen_teknisi / 100);
            });

        // Debugging
        // dd(
        //     $teknisiServisInterface,
        //     $teknisiServisHardware,
        //     Auth::user()->id
        // );
        $bonusServisInterface = ServiceTransaction::with('serviceaction')
            ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('bonus_interface');
        $profitservis = ServiceTransaction::with('serviceaction')
            ->where('tipe', 'Hardware')
            ->where('is_approve', 'Setuju')
            ->where('users_id', Auth::user()->id)
            ->whereYear('tgl_disetujui', $currentYear)
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('profit');
        $bonusservis = ($profitservis / 100) * Auth::user()->persen;

        $totalbonusHardware = $bonusservis + $teknisiServisHardware;
        $totalbonusInterface = $bonusServisInterface + $teknisiServisInterface;
        // dd($totalbonusInterface);
        $totalbonus = $bonusservis + $bonusServisInterface;

        // Ambil data transaksi servis yang memiliki status "Belum cek"
        $transactions = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Belum cek')->get();

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

        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

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
