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

        // Tentukan range tanggal bulan lalu (sesuai logika "prev" di model Anda)
        $lastMonth = now()->subMonth();
        $start_date = $lastMonth->startOfMonth()->format('Y-m-d');
        $end_date   = $lastMonth->endOfMonth()->format('Y-m-d');

        if ($user->role === 'Teknisi') {

            // --- 1. SUMBER: ServiceTransaction (Transaksi Utama) ---
            
            // A. Hitung Bonus Interface (Nominal langsung)
            $total_bonus_interface_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Interface')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('bonus_interface');

            // B. Hitung Profit Hardware (Profit * Persen User)
            $total_profit_hardware_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Hardware')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('profit');
            
            // Kalkulasi bagian hardware dari transaksi utama
            $bonus_hardware_main = ($total_profit_hardware_main / 100) * $user->persen;


            // --- 2. SUMBER: TeknisiServis (Tabel Detail/Pengerjaan Tim) ---

            // C. Hitung Bonus Interface (Nominal langsung dari tabel detail)
            $total_bonus_interface_detail = TeknisiServis::where('users_id', $user->id)
                ->where('tipe', 'Interface')
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil') // Pastikan status diambil juga dicek
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->sum('bonus_interface');

            // D. Hitung Profit Hardware (Profit per item * Persen per item)
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
                    // Rumus: Profit Barang * Persen Teknisi (spesifik per row) / 100
                    return $item->profit * ($item->persen_teknisi / 100);
                });

            // --- TOTAL AKHIR ---
            $bonus = $total_bonus_interface_main + $bonus_hardware_main + $total_bonus_interface_detail + $total_bonus_hardware_detail;

        } elseif ($user->role === 'Sales') {
            // ... (Logika Sales biarkan tetap atau sesuaikan jika perlu) ...
            $bonus = $user->prevsale->sum('profit') / 100;
            $bonus *= $user->persen;
        } else {
            // ... (Logika Admin) ...
            $bonusadminservis = $user->prevadminservice->sum('profit') / 100;
            $bonusadminsale = $user->prevadminsale->sum('profit') / 100;
            $bonus = ($bonusadminservis + $bonusadminsale) * $user->persen;
        }

        // Debugging untuk memastikan hasil sesuai (Hapus baris ini jika sudah fix)
        // return response()->json(['bonus_calculated' => $bonus]);

        Salary::create([
            'name' => $request->name,
            'users_id' => $request->users_id,
            'workers_id' => $request->workers_id,
            'bonus' => $bonus
        ]);

        return redirect()->route('bonus.index');
    }
}
