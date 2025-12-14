<?php

namespace App\Http\Controllers;

use App\Models\TipeOs;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class TipeOsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/tipe-os');
    }


    public function getData(Request $request)
    {
        $query = TipeOs::where('cabang_id',getCabangId())->latest();

        return DataTables::of($query)
            // Kolom Checkbox
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            // Kolom Aksi
            ->addColumn('aksi', function ($row) {
                $editUrl = route('master-tipe-os.edit', $row->id);
                $deleteUrl = route('master-tipe-os.destroy', $row->id);
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

    // Method Bulk Delete
    public function deleteBatch(Request $request)
    {
        $selectedIds = $request->input('ids');

        if (empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 400);
        }

        // Cek Relasi
      
        // Uncomment jika validasi relasi ingin diaktifkan
        // if ($hasRelation) {
        //    return response()->json(['message' => 'Data Tipe OS yang memiliki riwayat transaksi tidak bisa dihapus.'], 422);
        // }

        TipeOs::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data Tipe OS berhasil dihapus.']);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = TipeOs::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('product');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Tipe OS yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        TipeOs::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Tipe OS berhasil dihapus.']);
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
    public function store(Request $request)
    {

        // dd($request->all());
        $data = new TipeOs();
        $data->nama = $request->nama;
        $data->nominal_bonus = $request->nominal_bonus;
        $data->save();

        return redirect()->route('master-tipe-os.index');
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
        $item = TipeOs::findOrFail($id);

        return view('pages.kepalatoko.master.tipe-os-edit', [
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
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $item = TipeOs::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-tipe-os.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = TipeOs::findOrFail($id);

        $item->delete();

        toast('Data Tipe OS berhasil dihapus.', 'success');

        return redirect()->route('master-tipe-os.index');
    }
}
