<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // Pastikan ini diimport
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        // === LOGIC DATA TABLES ===
        if ($request->ajax()) {
            $query = Shift::with('worker')->where('cabang_id', getCabangId());

            if (Auth::user()->role !== 'Kepala Toko' && Auth::user()->workers_id) {
                $query->where('worker_id', Auth::user()->workers_id);
            }

            $query->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('worker_name', function ($row) {
                    return $row->worker->name ?? '-';
                })
                // Format Uang
                ->editColumn('nominal_gaji', fn($row) => 'Rp ' . number_format($row->nominal_gaji, 0, ',', '.'))
                ->editColumn('potongan_terlambat', fn($row) => 'Rp ' . number_format($row->potongan_terlambat, 0, ',', '.'))
                ->editColumn('potongan_tidak_masuk', fn($row) => 'Rp ' . number_format($row->potongan_tidak_masuk, 0, ',', '.'))
                ->editColumn('potongan_izin', fn($row) => 'Rp ' . number_format($row->potongan_izin, 0, ',', '.'))
                ->editColumn('potongan_cuti', fn($row) => 'Rp ' . number_format($row->potongan_cuti, 0, ',', '.'))
                ->editColumn('potongan_sakit', fn($row) => 'Rp ' . number_format($row->potongan_sakit, 0, ',', '.'))

                ->addColumn('action', function ($row) {
                    // Persiapkan data untuk Modal Edit (Sama persis dengan logic sebelumnya)
                    $data = [
                        'id' => $row->id,
                        'worker_id' => $row->worker_id,
                        'nama_shift' => $row->nama_shift,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        // Kita kirim format number string agar terbaca oleh input .sapator
                        'nominal_gaji' => number_format($row->nominal_gaji, 0, ',', '.'),
                        'potongan_terlambat' => number_format($row->potongan_terlambat, 0, ',', '.'),
                        'potongan_tidak_masuk' => number_format($row->potongan_tidak_masuk, 0, ',', '.'),
                        'potongan_sakit' => number_format($row->potongan_sakit, 0, ',', '.'),
                        'potongan_izin' => number_format($row->potongan_izin, 0, ',', '.'),
                        'potongan_cuti' => number_format($row->potongan_cuti, 0, ',', '.'),
                    ];

                    // Encode ke JSON agar bisa dipassing ke JS
                    $jsonData = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');

                    $editBtn = '<button type="button" class="text-indigo-500 mr-2" onclick="openEditModal('.$jsonData.')">Edit</button>';

                    $deleteUrl = route('shift.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    $deleteBtn = '
                        <form action="'.$deleteUrl.'" method="post" class="inline-block" onsubmit="return confirm(\'Yakin ingin hapus?\')">
                            '.$csrf.'
                            '.$method.'
                            <button class="text-rose-500">Hapus</button>
                        </form>
                    ';

                    return '<div class="flex justify-center">'.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        // Return view biasa untuk load halaman pertama
        $workers = Worker::where('cabang_id', getCabangId())->get();
        return view('pages.kepalatoko.master.shift', compact('workers'));
    }

    // Method store, update, destroy, deleteSelected TIDAK BERUBAH (tetap sama seperti kode asli Anda)
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
            'nominal_gaji' => 'nullable|integer',
            'potongan_terlambat' => 'required|integer',
            'potongan_tidak_masuk' => 'required|integer',
            'potongan_izin' => 'required|integer',
            'potongan_cuti' => 'required|integer',
            'potongan_sakit' => 'required|integer',
            'worker_id' => 'required|integer',
        ]);

        $validated['cabang_id'] = getCabangId();
        $data = Shift::create($validated);

        $user = User::where('workers_id', $data->worker_id)->first();
        if ($user) {
            $user->shift_id = $data->id;
            $user->save();
        }

        toast('Shift berhasil ditambahkan.', 'success');
        return redirect()->route('shift.index');
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
            'nominal_gaji' => 'nullable|integer',
            'potongan_terlambat' => 'required|integer',
            'potongan_tidak_masuk' => 'required|integer',
            'potongan_izin' => 'required|integer',
            'potongan_cuti' => 'required|integer',
            'potongan_sakit' => 'required|integer',
            'worker_id' => 'required|integer',
        ]);

        $item = Shift::findOrFail($id);
        $validated['cabang_id'] = getCabangId();
        $item->update($validated);

        $user = User::where('workers_id', $item->worker_id)->first();
        if ($user) {
            $user->shift_id = $item->id;
            $user->save();
        }

        // $worker = Worker::find($item->worker_id);
        // if (!empty($worker)) {
        //     $worker->gaji = $item->nominal_gaji;
        //     $worker->save();
        // }


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
