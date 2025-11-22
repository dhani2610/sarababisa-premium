<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\IzinRequest;
use App\Models\Izin;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class MasterIzinController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.master.izin');
    }

    public function store(IzinRequest $request)
    {
        dd('MASUK KE STORE', $request->all());

        try {
            $data = $request->validated();

            if ($request->hasFile('dokumen')) {
                $file = $request->file('dokumen');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/izin'), $filename);
                $data['dokumen'] = 'uploads/izin/' . $filename;
            }

            // Ambil setting potongan dari store_settings (id = 1)
            $store = StoreSetting::where('cabang_id',getCabangId())->first();

            // Jika nominal_potongan tidak diisi manual, ambil dari store_settings sesuai tipe
            if (empty($data['nominal_potongan'])) {
                switch ($data['tipe']) {
                    case 'izin':
                        $data['nominal_potongan'] = $store->nominal_potongan_izin ?? 0;
                        break;
                    case 'sakit':
                        $data['nominal_potongan'] = $store->nominal_potongan_sakit ?? 0;
                        break;
                    case 'alfa':
                        $data['nominal_potongan'] = $store->nominal_potongan_alfa ?? 0;
                        break;
                    default:
                        $data['nominal_potongan'] = 0;
                        break;
                }
            } else {
                // Jika user isi manual, pastikan format angka bersih dari titik
                $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
            }
            dd($data);

            Izin::create($data);

            toast('Data Izin berhasil disimpan.', 'success');
            return redirect()->route('master-izin.index');
        } catch (\Throwable $e) {
        // Tampilkan semua informasi error
            dd([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data_yang_dikirim' => $request->all(),
                'validated_data' => $data ?? null,
            ]);

            toast('Data Izin gagal disimpan.', 'error');
            return redirect()->route('master-izin.index');
        }
    }

    public function update(IzinRequest $request, $id)
    {
        $data = $request->validated();
        $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
        $item = Izin::findOrFail($id);
        if ($request->hasFile('dokumen')) {
            if ($item->dokumen && file_exists(public_path($item->dokumen))) {
                unlink(public_path($item->dokumen));
            }
            $file = $request->file('dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/izin'), $filename);
            $data['dokumen'] = 'uploads/izin/' . $filename;
        }

        //      // Ambil setting potongan dari store_settings (id = 1)
        // $store = StoreSetting::where('cabang_id',getCabangId())->first();

        // // Jika nominal_potongan tidak diisi manual, ambil dari store_settings sesuai tipe
        // if (empty($data['nominal_potongan'])) {
        //     switch ($data['tipe']) {
        //         case 'izin':
        //             $data['nominal_potongan'] = $store->nominal_potongan_izin ?? 0;
        //             break;
        //         case 'sakit':
        //             $data['nominal_potongan'] = $store->nominal_potongan_sakit ?? 0;
        //             break;
        //         case 'alfa':
        //             $data['nominal_potongan'] = $store->nominal_potongan_alfa ?? 0;
        //             break;
        //         default:
        //             $data['nominal_potongan'] = 0;
        //             break;
        //     }
        // } else {
        //     // Jika user isi manual, pastikan format angka bersih dari titik
        //     $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
        // }

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
