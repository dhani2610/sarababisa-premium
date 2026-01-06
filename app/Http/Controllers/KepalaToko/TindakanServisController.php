<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Http\Controllers\Controller;
use App\Imports\ServiceActionImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TindakanServisExport;
use App\Http\Requests\KepalaToko\ServiceActionRequest;

class TindakanServisController extends Controller
{
    public function index()
    {
        $actions = ServiceAction::paginate(10);
        $actions_count = ServiceAction::all()->count();
        return view('pages/kepalatoko/servis/tindakan-servis', compact('actions', 'actions_count'));
    }

    public function importChunk(Request $request)
    {
        try {
            $rows = $request->input('rows', []);
            $cabangId = getCabangId();

            $insertedCount = 0;
            $updatedCount  = 0;
            $skippedCount  = 0;

            foreach ($rows as $row) {

                // ===============================
                // Validasi baris kosong
                // ===============================
                if (empty($row['Nama Tindakan'])) {
                    $skippedCount++;
                    continue;
                }

                $namaTindakan = trim($row['Nama Tindakan']);

                // ===============================
                // Cek Service Action di cabang ini
                // ===============================
                $existing = ServiceAction::where('cabang_id', $cabangId)
                    ->where('nama_tindakan', $namaTindakan)
                    ->first();

                $data = [
                    'modal_sparepart' => $row['Modal Sparepart'] ?? 0,
                    'harga_toko'      => $row['Harga Pelanggan Toko'] ?? 0,
                    'harga_pelanggan' => $row['Harga Pelanggan Biasa'] ?? 0,
                    'garansi'         => $row['Garansi'] ?? 0,
                    'updated_at'      => now(),
                ];

                // ===============================
                // UPDATE jika sudah ada
                // ===============================
                if ($existing) {
                    $existing->update($data);
                    $updatedCount++;
                    continue;
                }

                // ===============================
                // CREATE jika belum ada
                // ===============================
                $data['nama_tindakan'] = $namaTindakan;
                $data['cabang_id']     = $cabangId;
                $data['created_at']   = now();

                ServiceAction::create($data);
                $insertedCount++;
            }

            return response()->json([
                'status'   => 'success',
                'inserted'=> $insertedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'msg'    => $th->getMessage(),
            ], 500);
        }
    }


    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = ServiceAction::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('servicetransaction');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Tindakan Servis yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        ServiceAction::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Tindakan Servis berhasil dihapus.']);
    }

    public function store(ServiceActionRequest $request)
    {

        $request->merge([
            'modal_sparepart' => str_replace('.', '', $request->modal_sparepart),
            'harga_toko' => str_replace('.', '', $request->harga_toko),
            'harga_pelanggan' => str_replace('.', '', $request->harga_pelanggan),
        ]);
        $data = $request->all();

        $data['cabang_id'] = getCabangId();
        ServiceAction::create($data);

        return redirect()->route('tindakan-servis.index');
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ServiceActionData', $namafile);
        Excel::import(new ServiceActionImport, \public_path('/ServiceActionData/' . $namafile));
        return redirect()->route('tindakan-servis.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new TindakanServisExport, 'tindakan-servis.xlsx');
    }

    public function edit($id)
    {
        $item = ServiceAction::findOrFail($id);

        return view('pages.kepalatoko.servis.tindakan-servis-edit', [
            'item' => $item
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'modal_sparepart' => str_replace('.', '', $request->modal_sparepart),
            'harga_toko' => str_replace('.', '', $request->harga_toko),
            'harga_pelanggan' => str_replace('.', '', $request->harga_pelanggan),
        ]);
        $data = $request->all();

        $item = ServiceAction::findOrFail($id);

        $item->update($data);

        return redirect()->route('tindakan-servis.index');
    }

    public function destroy($id)
    {
        $item = ServiceAction::findOrFail($id);

        if (
            $item->servicetransaction()->exists()
        ) {
            toast('Data Tindakan Servis yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Tindakan Servis berhasil dihapus.', 'success');

        return redirect()->route('tindakan-servis.index');
    }
}
