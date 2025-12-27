<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Salary;
use App\Models\ServiceTransaction;
use App\Models\TeknisiServis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BonusBulanSebelumnyaController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $user = User::find($request->users_id);
    //     if ($user->role === 'Teknisi') {
    //         $hasil = $user->prevservicetransaction->sum('profit') / 100;
    //         $hasil *= $user->persen;
    //         $bonus = $hasil;
    //     } elseif ($user->role === 'Sales') {
    //         $bonus = $user->prevsale->sum('profit') / 100;
    //         $bonus *= $user->persen;
    //     } else {
    //         $bonusadminservis = $user->prevadminservice->sum('profit') / 100;
    //         $bonusadminsale = $user->prevadminsale->sum('profit') / 100;
    //         $bonus = $bonusadminservis + $bonusadminsale;
    //         $bonus *= $user->persen;
    //     }

    //     return response()->json([$bonus]);


    //     Salary::create([
    //         'name' => $request->name,
    //         'users_id' => $request->users_id,
    //         'workers_id' => $request->workers_id,
    //         'bonus' => $bonus
    //     ]);

    //     return redirect()->route('bonus.index');
    // }

    public function store(Request $request)
    {
        $user = User::find($request->users_id);
        $bonus = 0;

        $lastMonth = now()->subMonth();
        $start_date = $lastMonth->startOfMonth()->format('Y-m-d');
        $end_date   = $lastMonth->endOfMonth()->format('Y-m-d');

        if ($user->role === 'Teknisi') {

            $total_bonus_interface_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Interface')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('bonus_interface');

            $total_profit_hardware_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Hardware')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('profit');
            
            $bonus_hardware_main = ($total_profit_hardware_main / 100) * $user->persen;


            $total_bonus_interface_detail = TeknisiServis::where('users_id', $user->id)
                ->where('tipe', 'Interface')
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil') 
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->sum('bonus_interface');

            $total_bonus_hardware_detail = TeknisiServis::where('users_id', $user->id)
                ->where('tipe', 'Hardware')
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->get()
                ->sum(function ($item) {
                    return $item->profit * ($item->persen_teknisi / 100);
                });

            $bonus = $total_bonus_interface_main + $bonus_hardware_main + $total_bonus_interface_detail + $total_bonus_hardware_detail;

        } elseif ($user->role === 'Sales') {
            $bonus = $user->prevsale->sum('profit') / 100;
            $bonus *= $user->persen;
        } else {
            $bonusadminservis = $user->prevadminservice->sum('profit') / 100;
            $bonusadminsale = $user->prevadminsale->sum('profit') / 100;
            $bonus = ($bonusadminservis + $bonusadminsale) * $user->persen;
        }

        Salary::create([
            'name' => $request->name,
            'users_id' => $request->users_id,
            'workers_id' => $request->workers_id,
            'bonus' => $bonus,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('bonus.index');
    }
}
