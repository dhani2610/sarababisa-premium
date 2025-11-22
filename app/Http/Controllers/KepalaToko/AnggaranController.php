<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Budget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AnggaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $budgets = Budget::where('cabang_id',getCabangId())->get();
        $budgets_count = Budget::where('cabang_id',getCabangId())->get()->count();
        $total_budgets = Budget::where('cabang_id',getCabangId())->get()->sum('total');
        $targetharian = $total_budgets / 30;

        return view('pages/kepalatoko/anggaran', compact('budgets', 'budgets_count', 'total_budgets', 'targetharian'));
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Budget::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data anggaran berhasil dihapus.']);
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
        Budget::create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $request->quantity * $request->price,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('anggaran.index');
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
        $item = Budget::findOrFail($id);

        return view('pages.kepalatoko.anggaran-edit', [
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
        $item = Budget::findOrFail($id);
        $item->update([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $request->quantity * $request->price
        ]);

        return redirect()->route('anggaran.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Budget::findOrFail($id);

        $item->delete();

        return redirect()->route('anggaran.index');
    }
}
