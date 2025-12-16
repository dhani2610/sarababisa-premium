<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Worker;

class KasbonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workers = Worker::where('cabang_id',getCabangId())->get();
        $debts = Debt::where('cabang_id',getCabangId())->get();
        return view('pages/admintoko/kasbon/index', compact('workers', 'debts'));
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
        
        $request->merge([
            'total' => (int) str_replace('.', '', $request->total),
        ]);
        $data['cabang_id'] = getCabangId();
        $data = $request->all();

        Debt::create($data);

        return redirect()->route('admin-kasbon.index');
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
        $item = Debt::where('cabang_id',getCabangId())->with('worker')->findOrFail($id);
        $workers = Worker::where('cabang_id',getCabangId())->all();

        return view('pages.admintoko.kasbon.edit', [
            'item' => $item,
            'workers' => $workers
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
        $request->merge([
            'total' => (int) str_replace('.', '', $request->total),
        ]);

        $data = $request->all();

        $item = Debt::findOrFail($id);

        $item->update($data);

        return redirect()->route('admin-kasbon.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Debt::findOrFail($id);

        $item->delete();

        return redirect()->route('admin-kasbon.index');
    }
}
