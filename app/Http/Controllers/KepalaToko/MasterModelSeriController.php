<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Brand;
use App\Models\ModelSerie;
use App\Models\TipeOs;
use Illuminate\Http\Request;
use App\Exports\ModelSeriExport;
use App\Imports\ModelSeriImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\ModelSerieRequest;

class MasterModelSeriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/model-seri');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = ModelSerie::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Data Model Seri yang memiliki riwayat transaksi tidak bisa dihapus.']);
        // }

        ModelSerie::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data model seri berhasil dihapus.']);
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
    // public function store(ModelSerieRequest $request)
    // {
    //     $data = $request->all();

    //     ModelSerie::create($data);

    //     return redirect()->route('master-model-seri.index');
    // }

    // public function store(ModelSerieRequest $request)
    // {
    //     $data = $request->validated();

    //     $existing = \App\Models\ModelSerie::withTrashed()
    //         ->where('name', $data['name'])
    //         ->first();

    //     if ($existing && $existing->trashed()) {
    //         // Kalau ada yang soft delete → restore
    //         $existing->restore();
    //         $existing->update($data);

    //         return redirect()->back()->with('success', 'Model seri berhasil dipulihkan & diperbarui.');
    //     }

    //     $data['cabang_id'] = getCabangId();
    //     // Kalau belum ada → buat baru
    //     \App\Models\ModelSerie::create($data);

    //     return redirect()->back()->with('success', 'Model seri berhasil ditambahkan.');
    // }

    public function store(ModelSerieRequest $request)
    {
        $data = $request->validated();
        $data['cabang_id'] = getCabangId();

        $namaAsli = $data['name'];
        $finalName = $namaAsli;

        // Loop Cek Duplikat
        // Menggunakan withTrashed() agar mengecek seluruh data termasuk yang sudah dihapus.
        // Jika "iPhone 11" ada di sampah, maka input baru akan menjadi "iPhone 11 (2)"
        // while (\App\Models\ModelSerie::withTrashed()->where('name', $finalName)->exists()) {
        //     $finalName = $namaAsli . '.';
        //     $counter++;
        // }

        $finalName = $request->name; 

        // Cek keberadaan nama (termasuk yang sudah dihapus/withTrashed)
        while (\App\Models\ModelSerie::withTrashed()->where('name', $finalName)->exists()) {
            
            // Jika ada, tambahkan satu titik di belakang nama yang sedang dicek
            $finalName = $finalName . '.';
            
        }
        // Update nama di array data dengan nama yang sudah unik
        $data['name'] = $finalName;

        // Selalu Create Baru (Logic restore dihapus total)
        \App\Models\ModelSerie::create($data);

        return redirect()->back()->with('success', 'Model seri berhasil ditambahkan.');
    }


    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ModelData', $namafile);
        Excel::import(new ModelSeriImport, \public_path('/ModelData/' . $namafile));
        return redirect()->route('master-model-seri.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new ModelSeriExport, 'data-model-seri.xlsx');
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
        $item = ModelSerie::with('brand')->findOrFail($id);
        $brands = Brand::all();
        $tipe = TipeOs::get();

        return view('pages.kepalatoko.master.model-seri-edit', [
            'item' => $item,
            'tipe' => $tipe,
            'brands' => $brands
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

        $item = ModelSerie::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-model-seri.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = ModelSerie::findOrFail($id);

        // if (
        //     $item->relasiService()->exists()
        // ) {
        //     toast('Data Model Seri yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
        //     return redirect()->back();
        // }

        $item->delete();

        toast('Data Model Seri berhasil dihapus.', 'success');

        return redirect()->route('master-model-seri.index');
    }
}
