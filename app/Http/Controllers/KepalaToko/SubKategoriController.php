<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubKategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/sub-kategori/index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = SubCategory::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiProduct');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Sub Kategori Produk yang sudah terhubung dengan data produk tidak bisa dihapus.']);
        }

        SubCategory::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Sub Kategori Produk berhasil dihapus.']);
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
        $data = $request->all();
        $data['cabang_id'] = getCabangId();
        SubCategory::create($data);

        return redirect()->route('sub-kategori.index');
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
        $item = SubCategory::findOrFail($id);
        $categories = Category::all();

        return view('pages.kepalatoko.sub-kategori.edit', [
            'item' => $item,
            'categories' => $categories
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

        $item = SubCategory::findOrFail($id);

        $item->update($data);

        return redirect()->route('sub-kategori.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = SubCategory::findOrFail($id);

        if (
            $item->relasiProduct()->exists()
        ) {
            toast('Data Sub Kategori Produk yang sudah terhubung dengan data produk tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Sub Kategori Produk berhasil dihapus.', 'success');

        return redirect()->route('sub-kategori.index');
    }
}
