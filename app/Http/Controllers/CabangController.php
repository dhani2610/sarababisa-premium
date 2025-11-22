<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class CabangController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/cabang');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        Cabang::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Cabang berhasil dihapus.']);
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
        $data = new Cabang();
        $data->nama_cabang = $request->nama_cabang;
        $data->save();

        $storeSetting = StoreSetting::where('cabang_id',$data->id)->first();
        if (!empty($storeSetting)) {
            $storeSetting = StoreSetting::where('cabang_id',$data->id)->first();
        }else{
            $storeSetting = new StoreSetting();
        }
        $storeSetting->owner = $request->owner;
        $storeSetting->nama_toko = $request->nama_toko;
        $storeSetting->deskripsi_toko = $request->deskripsi_toko;
        $storeSetting->nomor_hp_toko = $request->nomor_hp_toko;
        $storeSetting->bank = $request->bank;
        $storeSetting->rekening = $request->rekening;
        $storeSetting->pemilik_rekening = $request->pemilik_rekening;
        $storeSetting->cabang_id = $data->id;
        $storeSetting->is_tax = 0;
        $storeSetting->is_bonus = 1;
        $storeSetting->is_edit_transaksi = 1;
        $storeSetting->is_edit_produk = 1;
        $storeSetting->cabang_id = $data->id;
        $storeSetting->save();

        return redirect()->route('master-cabang.index');
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
        $item = Cabang::findOrFail($id);
        $storeSetting = StoreSetting::find($item->id);

        return view('pages.kepalatoko.master.cabang-edit', [
            'item' => $item,
            'storeSetting' => $storeSetting,
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

        $item = Cabang::findOrFail($id);
        $item->nama_cabang = $request->nama_cabang;
        $item->save();

        $storeSetting = StoreSetting::where('cabang_id',$id)->first();
        if (!empty($storeSetting)) {
            $storeSetting = StoreSetting::where('cabang_id',$id)->first();
        }else{
            $storeSetting = new StoreSetting();
        }
        $storeSetting->owner = $request->owner;
        $storeSetting->nama_toko = $request->nama_toko;
        $storeSetting->alamat_toko = $request->alamat_toko;
        $storeSetting->deskripsi_toko = $request->deskripsi_toko;
        $storeSetting->nomor_hp_toko = $request->nomor_hp_toko;
        $storeSetting->bank = $request->bank;
        $storeSetting->rekening = $request->rekening;
        $storeSetting->pemilik_rekening = $request->pemilik_rekening;
        $storeSetting->save();

        return redirect()->route('master-cabang.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Cabang::findOrFail($id);

        if ($id != 1) {
            $storeSetting = StoreSetting::where('cabang_id',$id)->first();
            $storeSetting->delete();
        }

        $item->delete();

        toast('Data Cabang berhasil dihapus.', 'success');

        return redirect()->route('master-cabang.index');
    }
}
