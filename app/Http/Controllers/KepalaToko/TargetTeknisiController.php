<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TeknisiTarget;
use App\Http\Controllers\Controller;

class TargetTeknisiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/target-teknisi/index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        TeknisiTarget::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data target teknisi berhasil dihapus.']);
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
        $teknisi_name = User::find($request->users_id);

        TeknisiTarget::create([
            'users_id' => $request->users_id,
            'item' => $request->item,
            'teknisi_name' => $teknisi_name->name,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('target-teknisi.index');
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
        $item = TeknisiTarget::findOrFail($id);
        $teknisi = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();

        return view('pages.kepalatoko.target-teknisi.edit', [
            'item' => $item,
            'teknisi' => $teknisi,
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
        $item = TeknisiTarget::findOrFail($id);

        $teknisi_name = User::find($request->users_id);

        $item->update([
            'users_id' => $request->users_id,
            'item' => $request->item,
            'teknisi_name' => $teknisi_name->name,
            'created_at' => $request->created_at,
        ]);

        return redirect()->route('target-teknisi.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = TeknisiTarget::findOrFail($id);

        $item->delete();

        return redirect()->route('target-teknisi.index');
    }
}
