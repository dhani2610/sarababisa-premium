<?php

namespace App\Http\Controllers;

use App\Models\RincianInvest;
use Illuminate\Http\Request;

class RincianInvestController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.rincian-invest.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe' => 'required|integer|in:1,2,3',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'upload_bukti_tf' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $path = $request->file('upload_bukti_tf')->store('invest_bukti', 'public');

        RincianInvest::create([
            'tipe' => $request->tipe,
            'tanggal' => $request->tanggal,
            'nominal' => $request->nominal,
            'upload_bukti_tf' => $path,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('rincian-invest.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $invest = RincianInvest::findOrFail($id);

        $request->validate([
            'tipe' => 'required|integer|in:1,2,3',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'upload_bukti_tf' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $path = $invest->upload_bukti_tf;

        if ($request->hasFile('upload_bukti_tf')) {
            if (file_exists(storage_path('app/public/' . $invest->upload_bukti_tf))) {
                unlink(storage_path('app/public/' . $invest->upload_bukti_tf));
            }
            $path = $request->file('upload_bukti_tf')->store('invest_bukti', 'public');
        }

        $invest->update([
            'tipe' => $request->tipe,
            'tanggal' => $request->tanggal,
            'nominal' => $request->nominal,
            'upload_bukti_tf' => $path,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('rincian-invest.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $invest = RincianInvest::findOrFail($id);
        if ($invest->upload_bukti_tf && file_exists(storage_path('app/public/' . $invest->upload_bukti_tf))) {
            unlink(storage_path('app/public/' . $invest->upload_bukti_tf));
        }
        $invest->delete();

        return redirect()->route('rincian-invest.index')->with('success', 'Data berhasil dihapus');
    }
    public function bulkDelete(Request $request)
    {
        RincianInvest::whereIn('id', $request->selectedIds)->delete();
        return response()->json(['success' => true]);
    }
}
