<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\TypeRequest;
use App\Models\Type;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterJenisBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/jenis-barang');
    }

    public function getData(Request $request)
    {
        $query = Type::where('cabang_id',getCabangId())->latest();

        return DataTables::of($query)
            // Kolom Checkbox
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            // Kolom Aksi
            ->addColumn('aksi', function ($row) {
                $editUrl = route('master-jenis-barang.edit', $row->id);
                $deleteUrl = route('master-jenis-barang.destroy', $row->id);
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
            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Type::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Data jenis barang yang memiliki riwayat transaksi tidak bisa dihapus.']);
        // }

        Type::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data jenis barang berhasil dihapus.']);
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
    // public function store(TypeRequest $request)
    // {
    //     $data = $request->all();
    //     $data['cabang_id'] = getCabangId();
    //     Type::create($data);

    //     return redirect()->route('master-jenis-barang.index');
    // }

    public function store(TypeRequest $request)
    {
        // 1. Ambil Nama Asli dan ID Cabang
        $cabangId = getCabangId();
        $namaAsli = $request->name;

        $finalName = $namaAsli;
        $counter = 2;

        // 2. Loop Cek Duplikat (Logic Create Baru)
        // Mengecek apakah nama 'finalName' sudah ada di database.
        // Loop akan terus berjalan sampai menemukan nama yang belum dipakai.
        // while (Type::where('name', $finalName)->exists()) {
        //     $finalName = $namaAsli . '.';
        //     $counter++;
        // }

        // 3. Siapkan Data untuk disimpan
        $data = $request->all();
        $data['name'] = $finalName; // Timpa nama dengan yang sudah unik/aman
        $data['cabang_id'] = $cabangId;

        // 4. Create Data
        Type::create($data);

        return redirect()->route('master-jenis-barang.index');
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
        $item = Type::findOrFail($id);

        return view('pages.kepalatoko.master.jenis-barang-edit', [
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
    public function update(TypeRequest $request, $id)
    {
        $data = $request->all();

        $item = Type::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-jenis-barang.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Type::findOrFail($id);

        // if (
        //     $item->relasiService()->exists()
        // ) {
        //     toast('Data Jenis Barang yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
        //     return redirect()->back();
        // }

        $item->delete();

        toast('Data Jenis Barang berhasil dihapus.', 'success');

        return redirect()->route('master-jenis-barang.index');
    }
}
