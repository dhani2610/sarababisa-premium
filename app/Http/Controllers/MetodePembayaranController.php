<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.master.metode-pembayaran.index');
    }

    public function getData()
    {
        $query = MetodePembayaran::where('cabang_id',getCabangId())->latest();

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->addColumn('foto', function ($row) {
                if ($row->foto) {
                    $url = asset('storage/metode-pembayaran/' . $row->foto);
                    return '<a href="'.$url.'"><img src="' . $url . '" class="h-50 w-auto object-contain rounded" /> </>';
                }
                return '<span class="text-slate-400 text-xs">No Image</span>';
            })
            ->addColumn('is_payment_gateway', function ($row) {
                return $row->is_payment_gateway == 1
                    ? '<span class="bg-emerald-100 text-emerald-600 px-2 py-1 rounded-full text-xs font-bold">Gateway</span>'
                    : '<span class="bg-slate-100 text-slate-500 px-2 py-1 rounded-full text-xs">Manual</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '
                    <div class="flex space-x-2">
                        <button onclick="editData(' . $row->id . ')" class="text-slate-400 hover:text-indigo-500 rounded-full">
                            <span class="sr-only">Edit</span>
                             <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32"><path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" /></svg>
                        </button>
                        <button onclick="deleteData(' . $row->id . ')" class="text-rose-500 hover:text-rose-600 rounded-full">
                            <span class="sr-only">Delete</span>
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32"><path d="M13 15h2v6h-2zM17 15h2v6h-2z" /><path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" /></svg>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'foto', 'is_payment_gateway', 'aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $messages = [
            'nama.required' => 'Nama metode pembayaran wajib diisi ya.',
            'nama.string'   => 'Nama metode pembayaran harus berupa teks.',
            'nama.max'      => 'Nama metode pembayaran maksimal 255 karakter.',

            'foto.required' => 'Jangan lupa upload logo atau icon pembayarannya.',
            'foto.image'    => 'File yang diupload harus berupa gambar.',
            'foto.mimes'    => 'Format logo harus berupa: jpeg, png, jpg, atau svg.',
            'foto.max'      => 'Ukuran logo terlalu besar, maksimal 2MB ya.',
        ];

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'foto' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'is_payment_gateway' => 'nullable'
        ], $messages);

        // 3. Cek Validasi
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imageName = null;
        if ($request->hasFile('foto')) {
            $imageName = time() . '.' . $request->foto->extension();
            $request->foto->storeAs('public/metode-pembayaran', $imageName);
        }

        MetodePembayaran::create([
            'nama' => $request->nama,
            'foto' => $imageName,
            'is_payment_gateway' => $request->has('is_payment_gateway') ? 1 : 0,
            'cabang_id' => getCabangId(),
        ]);

        return response()->json(['message' => 'Metode Pembayaran berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = MetodePembayaran::findOrFail($id);
        // Tambahkan URL foto untuk preview di Dropify
        $data->foto_url = $data->foto ? asset('storage/metode-pembayaran/' . $data->foto) : null;
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $item = MetodePembayaran::findOrFail($id);

        $messages = [
            'nama.required' => 'Nama metode pembayaran wajib diisi ya.',
            'nama.string'   => 'Nama metode pembayaran harus berupa teks.',
            'nama.max'      => 'Nama metode pembayaran maksimal 255 karakter.',

            'foto.required' => 'Jangan lupa upload logo atau icon pembayarannya.',
            'foto.image'    => 'File yang diupload harus berupa gambar.',
            'foto.mimes'    => 'Format logo harus berupa: jpeg, png, jpg, atau svg.',
            'foto.max'      => 'Ukuran logo terlalu besar, maksimal 2MB ya.',
        ];

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'foto' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'is_payment_gateway' => 'nullable'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'nama' => $request->nama,
            'is_payment_gateway' => $request->has('is_payment_gateway') ? 1 : 0,
        ];

        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($item->foto && Storage::exists('public/metode-pembayaran/' . $item->foto)) {
                Storage::delete('public/metode-pembayaran/' . $item->foto);
            }

            $imageName = time() . '.' . $request->foto->extension();
            $request->foto->storeAs('public/metode-pembayaran', $imageName);
            $data['foto'] = $imageName;
        }

        $item->update($data);

        return response()->json(['message' => 'Data berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $item = MetodePembayaran::findOrFail($id);
        if ($item->foto && Storage::exists('public/metode-pembayaran/' . $item->foto)) {
            Storage::delete('public/metode-pembayaran/' . $item->foto);
        }
        $item->delete();
        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    public function deleteBatch(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) return response()->json(['message' => 'Tidak ada data dipilih'], 400);

        $items = MetodePembayaran::whereIn('id', $ids)->get();

        foreach($items as $item) {
            if ($item->foto && Storage::exists('public/metode-pembayaran/' . $item->foto)) {
                Storage::delete('public/metode-pembayaran/' . $item->foto);
            }
            $item->delete();
        }

        return response()->json(['message' => 'Data terpilih berhasil dihapus.']);
    }
}
