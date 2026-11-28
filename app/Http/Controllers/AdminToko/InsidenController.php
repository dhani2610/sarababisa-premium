<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\User;
use App\Models\Worker;
use App\Models\Incident;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class InsidenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $jumlahhari = Incident::whereDate('created_at', today())
        ->where('cabang_id',getCabangId())
            ->count();
        $totalbiaya = Incident::whereDate('created_at', today())
        ->where('cabang_id',getCabangId())
            ->get()
            ->sum('biaya_toko');
        $jumlahbulan = Incident::whereMonth('created_at', $currentMonth)
        ->where('cabang_id',getCabangId())
            ->count();
        $totalbiayabulan = Incident::whereMonth('created_at', $currentMonth)
        ->where('cabang_id',getCabangId())
            ->get()
            ->sum('biaya_toko');
        $jumlahtahun = Incident::whereYear('created_at', $currentYear)
        ->where('cabang_id',getCabangId())
            ->count();
        $totalbiayatahun = Incident::whereYear('created_at', $currentYear)
            ->get()
            ->where('cabang_id',getCabangId())
            ->sum('biaya_toko');

        $users = Worker::where('cabang_id',getCabangId())->where('jabatan', 'like', '%' . 'Teknisi' . '%')->get();
        $incidents = Incident::where('cabang_id',getCabangId())->with('worker')->get();
        $incidents_count = Incident::where('cabang_id',getCabangId())->get()->count();

        return view('pages/admintoko/insiden', compact(
            'users',
            'incidents',
            'incidents_count',
            'jumlahhari',
            'jumlahbulan',
            'jumlahtahun',
            'totalbiaya',
            'totalbiayabulan',
            'totalbiayatahun'
        ));
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
        $price = (float) str_replace('.', '', $request->price);
        $workerId = $request->workers_id;
        $createdAt = $request->created_at ? Carbon::parse($request->created_at) : now();

        $worker = Worker::find($workerId);
        $user = $worker ? ($worker->user_account ?? \App\Models\User::where('workers_id', $worker->id)->first()) : null;

        $limitToko = $user->nominal_insiden_toko ?? ($worker->nominal_insiden_toko ?? 0);
        $persenKaryawan = $user->persentase_potongan_insiden ?? ($worker->persentase_potongan_insiden ?? 0);

        $prevTotal = Incident::where('workers_id', $workerId)
            ->whereMonth('created_at', $createdAt->month)
            ->whereYear('created_at', $createdAt->year)
            ->sum('price');

        $sisaKuotaToko = max(0, $limitToko - $prevTotal);
        $ditanggungPenuhToko = min($price, $sisaKuotaToko);
        $melebihiLimit = max(0, $price - $ditanggungPenuhToko);
        $biayaTeknisi = round($melebihiLimit * ($persenKaryawan / 100));
        $biayaToko = $price - $biayaTeknisi;
        $persenTeknisi = $price > 0 ? round(($biayaTeknisi / $price) * 100, 2) : 0;

        // Transaction create
        Incident::create([
            'name' => $request->name,
            'price' => $price,
            'cabang_id' => getCabangId(),
            'workers_id' => $workerId,
            'persen_teknisi' => $persenTeknisi,
            'biaya_teknisi' => $biayaTeknisi,
            'biaya_toko' => $biayaToko,
            'created_at' => $createdAt,
        ]);

        return redirect()->route('admin-insiden.index');
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
        $item = Incident::findOrFail($id);
        $users = Worker::where('cabang_id',getCabangId())->where('jabatan', 'like', '%' . 'Teknisi' . '%')->get();

        return view('pages.admintoko.insiden-edit', [
            'item' => $item,
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
        $item = Incident::findOrFail($id);
        $price = (float) str_replace('.', '', $request->price);
        $workerId = $request->workers_id;
        $createdAt = $request->created_at ? Carbon::parse($request->created_at) : Carbon::parse($item->created_at);

        $worker = Worker::find($workerId);
        $user = $worker ? ($worker->user_account ?? \App\Models\User::where('workers_id', $worker->id)->first()) : null;

        $limitToko = $user->nominal_insiden_toko ?? ($worker->nominal_insiden_toko ?? 0);
        $persenKaryawan = $user->persentase_potongan_insiden ?? ($worker->persentase_potongan_insiden ?? 0);

        $prevTotal = Incident::where('workers_id', $workerId)
            ->where('id', '!=', $id)
            ->whereMonth('created_at', $createdAt->month)
            ->whereYear('created_at', $createdAt->year)
            ->sum('price');

        $sisaKuotaToko = max(0, $limitToko - $prevTotal);
        $ditanggungPenuhToko = min($price, $sisaKuotaToko);
        $melebihiLimit = max(0, $price - $ditanggungPenuhToko);
        $biayaTeknisi = round($melebihiLimit * ($persenKaryawan / 100));
        $biayaToko = $price - $biayaTeknisi;
        $persenTeknisi = $price > 0 ? round(($biayaTeknisi / $price) * 100, 2) : 0;

        $item->update([
            'name' => $request->name,
            'price' => $price,
            'workers_id' => $workerId,
            'persen_teknisi' => $persenTeknisi,
            'biaya_teknisi' => $biayaTeknisi,
            'biaya_toko' => $biayaToko,
            'created_at' => $createdAt,
        ]);

        return redirect()->route('admin-insiden.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Incident::findOrFail($id);

        $item->delete();

        return redirect()->route('admin-insiden.index');
    }
}
