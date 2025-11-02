<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\IzinRequest;
use App\Models\Izin;
use Illuminate\Http\Request;

class MasterIzinController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.master.izin');
    }

    public function store(IzinRequest $request)
    {
        $data = $request->validated();
        $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
        Izin::create($data);

        toast('Data Izin berhasil disimpan.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function edit($id)
    {
        $item = Izin::findOrFail($id);
        return view('pages.kepalatoko.master.izin-edit', ['item' => $item]);
    }

    public function update(IzinRequest $request, $id)
    {
        $data = $request->validated();
        $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
        $item = Izin::findOrFail($id);
        $item->update($data);

        toast('Data Izin berhasil diperbarui.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function destroy($id)
    {
        $item = Izin::findOrFail($id);
        $item->delete();

        toast('Data Izin berhasil dihapus.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        Izin::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data izin berhasil dihapus.']);
    }
}
