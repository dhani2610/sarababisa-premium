<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Salary;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use App\Models\TeknisiTarget;

class GajiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/gaji/index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Salary::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data bonus berhasil dihapus.']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::find($request->users_id);

        // Servis

        $targetItemServis = TeknisiTarget::where('users_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->sum('item');

        $resultItemServis = $user->servicetransaction->count();

        $profitServis = $user->servicetransaction->sum('profit') / 100;
        $bagiHasilServis = $profitServis * $user->persen;

        if ($targetItemServis != 0) {
            $rewardSebagianServis = $bagiHasilServis * (($resultItemServis / $targetItemServis) * 100) / 100;
        }

        $rewardPenuhServis = $bagiHasilServis;

        // Penjualan

        $targetItemSale = SalesTarget::where('users_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->sum('item');

        $resultItemSale = $user->sale->sum('quantity');

        $profitSale = $user->sale->sum('profit') / 100;

        $bagiHasilSale = $profitSale * $user->persen;

        if ($targetItemSale != 0) {
            $rewardSebagianSale = $bagiHasilSale * (($resultItemSale / $targetItemSale) * 100) / 100;
        }

        $rewardPenuhSale = $bagiHasilSale;


        if ($targetItemServis != 0 || $targetItemSale != 0) {
            if ($user->role === 'Teknisi') {
                if ($resultItemServis < $targetItemServis) {
                    $bonus = $rewardSebagianServis;
                } else {
                    $bonus = $rewardPenuhServis;
                }
            } elseif ($user->role === 'Sales') {
                if ($resultItemSale < $targetItemSale) {
                    $bonus = $rewardSebagianSale;
                } else {
                    $bonus = $rewardPenuhSale;
                }
            } else {
                // $bonusadminservis = $user->adminservice->sum('profit') / 100;
                // $bonusadminsale = $user->adminsale->sum('profit') / 100;
                // $bonus = $bonusadminservis + $bonusadminsale;
                // $bonus *= $user->persen;

                $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen'; // default biar aman
                $persen = $user->persen ?? 0;
                $nominalBonus = $user->nominal_bonus_admin ?? 0;

                $totalProfitService = $user->adminservice->sum('profit');
                $totalProfitSale = $user->adminsale->sum('profit');
                $totalNotaService = $user->adminservice->count();
                $totalNotaSale = $user->adminsale->count();

                if ($tipeBonusNota === 'Persen') {
                    $bonus = (($totalProfitService + $totalProfitSale) / 100) * $persen;
                } elseif ($tipeBonusNota === 'Tetap') {
                    $totalNota = $totalNotaService + $totalNotaSale;
                    $bonus = $totalNota * $nominalBonus;
                } else {
                    $bonus = 0;
                }
            }
        } else {
            if ($user->role === 'Teknisi') {
                $bonus = $rewardPenuhServis;
            } elseif ($user->role === 'Sales') {
                $bonus = $rewardPenuhSale;
            } else {
                // $bonusadminservis = $user->adminservice->sum('profit') / 100;
                // $bonusadminsale = $user->adminsale->sum('profit') / 100;
                // $bonus = $bonusadminservis + $bonusadminsale;
                // $bonus *= $user->persen;
                $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen'; // default biar aman
                $persen = $user->persen ?? 0;
                $nominalBonus = $user->nominal_bonus_admin ?? 0;

                $totalProfitService = $user->adminservice->sum('profit');
                $totalProfitSale = $user->adminsale->sum('profit');
                $totalNotaService = $user->adminservice->count();
                $totalNotaSale = $user->adminsale->count();

                if ($tipeBonusNota === 'Persen') {
                    $bonus = (($totalProfitService + $totalProfitSale) / 100) * $persen;
                } elseif ($tipeBonusNota === 'Tetap') {
                    $totalNota = $totalNotaService + $totalNotaSale;
                    $bonus = $totalNota * $nominalBonus;
                } else {
                    $bonus = 0;
                }
            }
        }

        // return response()->json([$bonus]);

        Salary::create([
            'name' => $request->name,
            'users_id' => $request->users_id,
            'workers_id' => $request->workers_id,
            'bonus' => $bonus
        ]);

        return redirect()->route('bonus.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Salary::with('worker')->findOrFail($id);
        $workers = Worker::all();
        $users = User::whereNot('role', 'Kepala Toko')->get();

        return view('pages.kepalatoko.gaji.edit', [
            'item' => $item,
            'workers' => $workers,
            'users' => $users
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $item = Salary::findOrFail($id);

        $item->update($data);

        return redirect()->route('bonus.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Salary::findOrFail($id);

        $item->delete();

        return redirect()->route('bonus.index');
    }
}
