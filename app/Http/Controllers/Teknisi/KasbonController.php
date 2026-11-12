<?php

namespace App\Http\Controllers\Teknisi;

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
        return view('pages/teknisi/kasbon/index');
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
        if (empty($data['workers_id'])) {
            $data['workers_id'] = auth()->user()?->worker?->id;
        }
        if (empty($data['workers_id'])) {
            return redirect()->route('teknisi-kasbon.index')
                ->with('error', 'Akun Anda belum terhubung ke Data Karyawan. Hubungi Kepala Toko untuk mengatur Relasi Data Karyawan pada menu Akun.');
        }

        Debt::create($data);

        return redirect()->route('teknisi-kasbon.index');
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
        $item = Debt::findOrFail($id);

        return view('pages.teknisi.kasbon.edit', [
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

        $item = Debt::findOrFail($id);

        $item->update($data);

        return redirect()->route('teknisi-kasbon.index');
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

        return redirect()->route('teknisi-kasbon.index');
    }
}
