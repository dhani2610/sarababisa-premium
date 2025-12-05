<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\TypeRequest;
use App\Models\Type;
use Illuminate\Http\Request;

class MasterJenisBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/jenis-barang');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Type::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data jenis barang yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        Type::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data jenis barang berhasil dihapus.']);
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
    // public function store(TypeRequest $request)
    // {
    //     $data = $request->all();
    //     $data['cabang_id'] = getCabangId();
    //     Type::create($data);

    //     return redirect()->route('master-jenis-barang.index');
    // }

    public function store(TypeRequest $request)
    {
        // 1. Ambil Nama Asli dan ID Cabang
        $cabangId = getCabangId();
        $namaAsli = $request->name;

        $finalName = $namaAsli;
        $counter = 2;

        // 2. Loop Cek Duplikat (Logic Create Baru)
        // Mengecek apakah nama 'finalName' sudah ada di database.
        // Loop akan terus berjalan sampai menemukan nama yang belum dipakai.
        while (Type::where('name', $finalName)->exists()) {
            $finalName = $namaAsli . ' (' . $counter . ')';
            $counter++;
        }

        // 3. Siapkan Data untuk disimpan
        $data = $request->all();
        $data['name'] = $finalName; // Timpa nama dengan yang sudah unik/aman
        $data['cabang_id'] = $cabangId;

        // 4. Create Data
        Type::create($data);

        return redirect()->route('master-jenis-barang.index');
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
        $item = Type::findOrFail($id);

        return view('pages.kepalatoko.master.jenis-barang-edit', [
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
    public function update(TypeRequest $request, $id)
    {
        $data = $request->all();

        $item = Type::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-jenis-barang.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Type::findOrFail($id);

        if (
            $item->relasiService()->exists()
        ) {
            toast('Data Jenis Barang yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Jenis Barang berhasil dihapus.', 'success');

        return redirect()->route('master-jenis-barang.index');
    }
}
