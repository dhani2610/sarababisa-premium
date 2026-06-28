<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TeknisiTarget;
use App\Http\Controllers\Controller;

class TargetTeknisiController extends Controller
{
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $teknisi_name = User::find($request->users_id);

        // Bersihkan format ribuan jika tipe yang dipilih adalah nominal
        $nominal = null;
        $item = null;

        if (!empty($request->nominal)) {
            $nominal = preg_replace('/[^0-9]/', '', $request->nominal);
            $item = !empty($request->nominal) ? 'nominal' : 'item';
        } else {
            $item = !empty($request->nominal) ? 'nominal' : 'item';
        }

        // Bersihkan format ribuan untuk bonus pencapaian
        $bonus_nominal = null;
        if (!empty($request->bonus_nominal)) {
            $bonus_nominal = preg_replace('/[^0-9]/', '', $request->bonus_nominal);
        }

        TeknisiTarget::create([
            'users_id'     => $request->users_id,
            'teknisi_name' => $teknisi_name->name,
            'cabang_id'    => getCabangId(),
            'tipe'         => !empty($request->nominal) ? 'nominal' : 'item',
            'item'         => $item,
            'nominal'      => $nominal,
            'bonus_nominal'=> $bonus_nominal, // Insert ke DB
        ]);

        return redirect()->route('target-teknisi.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $item = TeknisiTarget::findOrFail($id);
        $teknisi = User::where('cabang_id', getCabangId())->where('role', 'Teknisi')->get();

        return view('pages.kepalatoko.target-teknisi.edit', [
            'item' => $item,
            'teknisi' => $teknisi,
        ]);
    }

    public function update(Request $request, $id)
    {
        $target = TeknisiTarget::findOrFail($id);
        $teknisi_name = User::find($request->users_id);

        $nominal = null;
        $item = null;

        if ($request->tipe == 'nominal') {
            $nominal = preg_replace('/[^0-9]/', '', $request->nominal);
        } else {
            $item = $request->item;
        }

        // Bersihkan format ribuan untuk bonus pencapaian saat update
        $bonus_nominal = null;
        if (!empty($request->bonus_nominal)) {
            $bonus_nominal = preg_replace('/[^0-9]/', '', $request->bonus_nominal);
        }

        $target->update([
            'users_id'     => $request->users_id,
            'teknisi_name' => $teknisi_name->name,
            'tipe'         => $request->tipe,
            'item'         => $item,
            'nominal'      => $nominal,
            'bonus_nominal'=> $bonus_nominal, // Update ke DB
            'created_at'   => $request->created_at,
        ]);

        return redirect()->route('target-teknisi.index');
    }

    public function destroy($id)
    {
        $item = TeknisiTarget::findOrFail($id);
        $item->delete();

        return redirect()->route('target-teknisi.index');
    }
}
