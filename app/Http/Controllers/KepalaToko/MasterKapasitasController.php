<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\CapacityRequest;
use App\Models\Capacity;
use Illuminate\Http\Request;

class MasterKapasitasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $capacities = Capacity::where('cabang_id',getCabangId())->paginate(10);
        $capacities_count = Capacity::where('cabang_id',getCabangId())->get()->count();
        return view('pages/kepalatoko/master/kapasitas', compact('capacities', 'capacities_count'));
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Capacity::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Kapasitas yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        Capacity::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data kapasitas berhasil dihapus.']);
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
    // public function store(CapacityRequest $request)
    // {
    //     $data = $request->all();
    //     $data['cabang_id'] = getCabangId();
    //     Capacity::create($data);

    //     return redirect()->route('master-kapasitas.index');
    // }

    public function store(CapacityRequest $request)
    {
        $data = $request->all();
        $cabangId = getCabangId();

        $namaAsli = $data['name'];
        $finalName = $namaAsli;
        $counter = 2;

        // Loop Cek Duplikat
        // HANYA cek data aktif (tidak pakai withTrashed), sesuai request "gausah ngecek ke sampah"
        while (Capacity::where('name', $finalName)->exists()) {
            $finalName = $namaAsli . ' (' . $counter . ')';
            $counter++;
        }

        // Simpan dengan nama yang sudah unik
        $data['name'] = $finalName;
        $data['cabang_id'] = $cabangId;

        Capacity::create($data);

        return redirect()->route('master-kapasitas.index');
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
        $item = Capacity::findOrFail($id);

        return view('pages.kepalatoko.master.kapasitas-edit', [
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CapacityRequest $request, $id)
    {
        $data = $request->all();

        $item = Capacity::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-kapasitas.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Capacity::findOrFail($id);

        if (
            $item->relasiService()->exists()
        ) {
            toast('Data Kapasitas yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Kapasitas berhasil dihapus.', 'success');

        return redirect()->route('master-kapasitas.index');
    }
}
