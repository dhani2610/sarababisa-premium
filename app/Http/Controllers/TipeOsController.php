<?php

namespace App\Http\Controllers;

use App\Models\TipeOs;
use Illuminate\Http\Request;

class TipeOsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/tipe-os');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = TipeOs::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('product');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Tipe OS yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        TipeOs::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Tipe OS berhasil dihapus.']);
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

        // dd($request->all());
        $data = new TipeOs();
        $data->nama = $request->nama;
        $data->nominal_bonus = $request->nominal_bonus;
        $data->save();

        return redirect()->route('master-tipe-os.index');
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
        $item = TipeOs::findOrFail($id);

        return view('pages.kepalatoko.master.tipe-os-edit', [
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
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $item = TipeOs::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-tipe-os.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = TipeOs::findOrFail($id);

        $item->delete();

        toast('Data Tipe OS berhasil dihapus.', 'success');

        return redirect()->route('master-tipe-os.index');
    }
}
