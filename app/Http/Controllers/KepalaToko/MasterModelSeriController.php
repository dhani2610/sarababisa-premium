<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Brand;
use App\Models\ModelSerie;
use App\Models\TipeOs;
use Illuminate\Http\Request;
use App\Exports\ModelSeriExport;
use App\Imports\ModelSeriImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\ModelSerieRequest;
use Yajra\DataTables\Facades\DataTables;
class MasterModelSeriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/model-seri');
    }

    public function getData(Request $request)
    {
        // Eager load relasi brand agar query lebih efisien
        $query = ModelSerie::with('brand')->where('cabang_id',getCabangId())->latest();

        return DataTables::of($query)
            // Kolom Checkbox
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })

            // Kolom Nama Brand (Merek)
            ->addColumn('brand_name', function ($row) {
                if ($row->brand) {
                    return '<span title="Brand ID: ' . $row->brand->id . '" class="cursor-help border-b border-dotted border-gray-400">' . e($row->brand->name) . '</span>';
                }

                return '<div class="text-rose-600">Data merek telah dihapus</div>';
            })

            // Kolom Tipe OS (Manual query cek, sesuai logic lama bapak)
            ->addColumn('tipe_os', function ($row) {
                if (!empty($row->id_tipe_os)) {
                    $cek = TipeOs::find($row->id_tipe_os);
                    return $cek ? e($cek->nama) : '-';
                }
                return '-';
            })

            // Kolom Nominal Bonus (Format Rupiah/Angka)
            ->editColumn('nominal_bonus', function ($row) {
                return number_format($row->nominal_bonus);
            })

            // Kolom Aksi
            ->addColumn('aksi', function ($row) {
                $editUrl = route('master-model-seri.edit', $row->id);
                $deleteUrl = route('master-model-seri.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="flex space-x-1">
                        <a href="' . $editUrl . '">
                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                <span class="sr-only">Edit</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                            </button>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'Apakah anda yakin ingin menghapus data ini?\');">
                            ' . $csrf . $method . '
                            <button type="submit" class="text-rose-500 hover:text-rose-600 rounded-full">
                                <span class="sr-only">Delete</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                </svg>
                            </button>
                        </form>
                    </div>
                ';
            })
            // Definisikan kolom mana yang mengandung HTML agar tidak di-escape
            ->rawColumns(['checkbox', 'brand_name', 'aksi'])
            ->make(true);
    }

    // Method Bulk Delete
    public function deleteBatch(Request $request)
    {
        $selectedIds = $request->input('ids');

        if (empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 400);
        }

        // Cek Relasi (Sesuai logic lama)
        $hasRelation = ModelSerie::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService');
            })
            ->exists();

        // Uncomment jika ingin validasi relasi aktif
        // if ($hasRelation) {
        //    return response()->json(['message' => 'Data Model Seri yang memiliki riwayat transaksi tidak bisa dihapus.'], 422);
        // }

        ModelSerie::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data model seri berhasil dihapus.']);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = ModelSerie::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Data Model Seri yang memiliki riwayat transaksi tidak bisa dihapus.']);
        // }

        ModelSerie::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data model seri berhasil dihapus.']);
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
    // public function store(ModelSerieRequest $request)
    // {
    //     $data = $request->all();

    //     ModelSerie::create($data);

    //     return redirect()->route('master-model-seri.index');
    // }

    // public function store(ModelSerieRequest $request)
    // {
    //     $data = $request->validated();

    //     $existing = \App\Models\ModelSerie::withTrashed()
    //         ->where('name', $data['name'])
    //         ->first();

    //     if ($existing && $existing->trashed()) {
    //         // Kalau ada yang soft delete → restore
    //         $existing->restore();
    //         $existing->update($data);

    //         return redirect()->back()->with('success', 'Model seri berhasil dipulihkan & diperbarui.');
    //     }

    //     $data['cabang_id'] = getCabangId();
    //     // Kalau belum ada → buat baru
    //     \App\Models\ModelSerie::create($data);

    //     return redirect()->back()->with('success', 'Model seri berhasil ditambahkan.');
    // }

    public function store(ModelSerieRequest $request)
    {
        $data = $request->validated();
        $data['cabang_id'] = getCabangId();

        $namaAsli = $data['name'];
        $finalName = $namaAsli;

        // Loop Cek Duplikat
        // Menggunakan withTrashed() agar mengecek seluruh data termasuk yang sudah dihapus.
        // Jika "iPhone 11" ada di sampah, maka input baru akan menjadi "iPhone 11 (2)"
        // while (\App\Models\ModelSerie::withTrashed()->where('name', $finalName)->exists()) {
        //     $finalName = $namaAsli . '.';
        //     $counter++;
        // }

        $finalName = $request->name;

        // Cek keberadaan nama (termasuk yang sudah dihapus/withTrashed)
        // while (\App\Models\ModelSerie::withTrashed()->where('name', $finalName)->exists()) {

        //     // Jika ada, tambahkan satu titik di belakang nama yang sedang dicek
        //     $finalName = $finalName . '.';

        // }
        // Update nama di array data dengan nama yang sudah unik
        $data['name'] = $finalName;

        // Selalu Create Baru (Logic restore dihapus total)
        \App\Models\ModelSerie::create($data);

        return redirect()->back()->with('success', 'Model seri berhasil ditambahkan.');
    }


    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ModelData', $namafile);
        Excel::import(new ModelSeriImport, \public_path('/ModelData/' . $namafile));
        return redirect()->route('master-model-seri.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new ModelSeriExport, 'data-model-seri.xlsx');
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
        $item = ModelSerie::where('cabang_id',getCabangId())->with('brand')->findOrFail($id);
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $tipe = TipeOs::where('cabang_id',getCabangId())->get();

        return view('pages.kepalatoko.master.model-seri-edit', [
            'item' => $item,
            'tipe' => $tipe,
            'brands' => $brands
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

        $item = ModelSerie::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-model-seri.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = ModelSerie::findOrFail($id);

        // if (
        //     $item->relasiService()->exists()
        // ) {
        //     toast('Data Model Seri yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
        //     return redirect()->back();
        // }

        $item->delete();

        toast('Data Model Seri berhasil dihapus.', 'success');

        return redirect()->route('master-model-seri.index');
    }
}
