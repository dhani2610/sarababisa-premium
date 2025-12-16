<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\User;
use App\Models\Worker;
use App\Models\Incident;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;

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
        // Transaction create
        Incident::create([
            'name' => $request->name,
            'price' => $request->price,
            'workers_id' => $request->workers_id,
            'persen_teknisi' => $request->persen_teknisi,
            'biaya_teknisi' => $request->price * $request->persen_teknisi / 100,
            'biaya_toko' => $request->price - ($request->price * $request->persen_teknisi / 100)
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
        $users = Worker::where('jabatan', 'like', '%' . 'Teknisi' . '%')->get();

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
        $item->update([
            'name' => $request->name,
            'price' => $request->price,
            'workers_id' => $request->workers_id,
            'persen_teknisi' => $request->persen_teknisi,
            'biaya_teknisi' => $request->price * $request->persen_teknisi / 100,
            'biaya_toko' => $request->price - ($request->price * $request->persen_teknisi / 100),
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
