<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Brand;
use App\Exports\MerekExport;
use App\Imports\BrandImport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\BrandRequest;

class MasterMerekController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/merek');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        // Validasi apakah data memiliki relasi dengan Service atau ModelSerie
        $hasRelation = Brand::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService')
                    ->orWhereHas('relasiModelSerie');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Data Merek yang memiliki riwayat transaksi/model seri tidak bisa dihapus.']);
        // }

        Brand::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data merek berhasil dihapus.']);
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
    // public function store(BrandRequest $request)
    // {
    //     $data = $request->validated();

    //     $existing = Brand::withTrashed()
    //         ->where('name', $data['name'])
    //         ->first();

    //     if ($existing && $existing->trashed()) {
    //         // Kalau ada merek dengan nama sama tapi soft delete → restore
    //         $existing->restore();
    //         $existing->update($data);

    //         return redirect()->route('master-merek.index')
    //             ->with('success', 'Merek berhasil dipulihkan & diperbarui.');
    //     }
    //     $data['cabang_id'] = getCabangId();

    //     // Kalau belum ada → buat baru
    //     Brand::create($data);

    //     return redirect()->route('master-merek.index')
    //         ->with('success', 'Merek berhasil ditambahkan.');
    // }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        $data['cabang_id'] = getCabangId();

        $namaAsli = $data['name'];
        $finalName = $namaAsli;
        $counter = 2;

        // Loop Cek Duplikat
        // Kita gunakan withTrashed() agar pengecekan mencakup data yang sudah dihapus juga.
        // Jadi kalau ada "Samsung" (Soft Deleted), input baru akan jadi "Samsung (2)"
        while (Brand::withTrashed()->where('name', $finalName)->exists()) {
            $finalName = $namaAsli . ' (' . $counter . ')';
            $counter++;
        }

        // Set nama final yang sudah unik
        $data['name'] = $finalName;

        // Langsung Create Baru (Logic restore dihapus)
        Brand::create($data);

        return redirect()->route('master-merek.index')
            ->with('success', 'Merek berhasil ditambahkan.');
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('BrandData', $namafile);
        Excel::import(new BrandImport, \public_path('/BrandData/' . $namafile));
        return redirect()->route('master-merek.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new MerekExport, 'data-merek.xlsx');
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
        $item = Brand::findOrFail($id);

        return view('pages.kepalatoko.master.merek-edit', [
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
    public function update(BrandRequest $request, $id)
    {
        $data = $request->all();

        $item = Brand::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-merek.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Brand::findOrFail($id);

        // if (
        //     $item->relasiService()->exists() || $item->relasiModelSerie()->exists()
        // ) {
        //     toast('Data Merek yang memiliki riwayat transaksi/model seri tidak bisa dihapus.', 'error');
        //     return redirect()->back();
        // }

        $item->delete();

        toast('Data Merek berhasil dihapus.', 'success');

        return redirect()->route('master-merek.index');
    }
}
