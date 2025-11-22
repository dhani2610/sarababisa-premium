<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Incident;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\Worker;

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

        $jumlahhari = Incident::where('cabang_id',getCabangId())->whereDate('created_at', today())
            ->count();
        $totalbiaya = Incident::where('cabang_id',getCabangId())->whereDate('created_at', today())
            ->get()
            ->sum('biaya_toko');
        $jumlahbulan = Incident::where('cabang_id',getCabangId())->whereMonth('created_at', $currentMonth)
            ->count();
        $totalbiayabulan = Incident::where('cabang_id',getCabangId())->whereMonth('created_at', $currentMonth)
            ->get()
            ->sum('biaya_toko');
        $jumlahtahun = Incident::where('cabang_id',getCabangId())->whereYear('created_at', $currentYear)
            ->count();
        $totalbiayatahun = Incident::where('cabang_id',getCabangId())->whereYear('created_at', $currentYear)
            ->get()
            ->sum('biaya_toko');

        $users = Worker::where('cabang_id',getCabangId())->where('jabatan', 'like', '%' . 'Teknisi' . '%')->get();
        $incidents = Incident::where('cabang_id',getCabangId())->with('worker')->get();
        $incidents_count = Incident::where('cabang_id',getCabangId())->get()->count();

        return view('pages/kepalatoko/insiden', compact(
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

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Incident::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data insiden berhasil dihapus.']);
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
            'biaya_toko' => $request->price - ($request->price * $request->persen_teknisi / 100),
            'cabang_id' => getCabangId()
        ]);

        return redirect()->route('insiden.index');
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

        return view('pages.kepalatoko.insiden-edit', [
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
            'created_at' => $request->created_at,
        ]);

        return redirect()->route('insiden.index');
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

        return redirect()->route('insiden.index');
    }
}
