<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $shifts = Shift::orderBy('created_at', 'desc')->paginate($perPage);

        return view('pages.kepalatoko.master.shift', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nominal_gaji' => str_replace('.', '', $request->nominal_gaji),
            'potongan_terlambat' => str_replace('.', '', $request->potongan_terlambat),
            'potongan_tidak_masuk' => str_replace('.', '', $request->potongan_tidak_masuk),
            'potongan_izin' => str_replace('.', '', $request->potongan_izin),
            'potongan_cuti' => str_replace('.', '', $request->potongan_cuti),
            'potongan_sakit' => str_replace('.', '', $request->potongan_sakit),
        ]);
        $validated = $request->validate([
            'nama_shift' => 'required|string|max:100',
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'nominal_gaji' => 'required|integer',
            'potongan_terlambat' => 'required|integer',
            'potongan_tidak_masuk' => 'required|integer',
            'potongan_izin' => 'required|integer',
            'potongan_cuti' => 'required|integer',
            'potongan_sakit' => 'required|integer',
        ]);

        Shift::create($validated);

        toast('Shift berhasil ditambahkan.', 'success');
        return redirect()->route('shift.index');
    }

    public function edit($id)
    {
        $item = Shift::findOrFail($id);
        return view('pages.kepalatoko.master.shift-edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'nominal_gaji' => str_replace('.', '', $request->nominal_gaji),
            'potongan_terlambat' => str_replace('.', '', $request->potongan_terlambat),
            'potongan_tidak_masuk' => str_replace('.', '', $request->potongan_tidak_masuk),
            'potongan_izin' => str_replace('.', '', $request->potongan_izin),
            'potongan_cuti' => str_replace('.', '', $request->potongan_cuti),
            'potongan_sakit' => str_replace('.', '', $request->potongan_sakit),
        ]);
        $validated = $request->validate([
            'nama_shift' => 'required|string|max:100',
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'nominal_gaji' => 'required|integer',
            'potongan_terlambat' => 'required|integer',
            'potongan_tidak_masuk' => 'required|integer',
            'potongan_izin' => 'required|integer',
            'potongan_cuti' => 'required|integer',
            'potongan_sakit' => 'required|integer',
        ]);

        $item = Shift::findOrFail($id);
        $item->update($validated);

        toast('Shift berhasil diupdate.', 'success');
        return redirect()->route('shift.index');
    }

    public function destroy($id)
    {
        Shift::findOrFail($id)->delete();

        toast('Shift berhasil dihapus.', 'success');
        return redirect()->route('shift.index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        if (!is_array($selectedIds) || empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 422);
        }

        Shift::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data shift berhasil dihapus.']);
    }
}
