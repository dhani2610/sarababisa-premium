<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
class CabangController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        // Data untuk header count dan modal (allowShow)
        $cabang_count = Cabang::count();

        // Cek limit cabang user
        $total_cabang_user = Auth::user()->total_cabang;
        $allowShow = $cabang_count < $total_cabang_user;

        return view('pages.kepalatoko.master.cabang', compact('cabang_count', 'allowShow'));
    }

    // Method DataTables AJAX
    public function getData(Request $request)
    {
        $query = Cabang::latest();

        return DataTables::of($query)
            // Kolom Checkbox
            ->addColumn('checkbox', function ($row) {
                // Cabang ID 1 (Pusat/Default) biasanya tidak boleh dihapus
                if ($row->id == 1) return '';
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            // Kolom Aksi
            ->addColumn('aksi', function ($row) {
                $editUrl = route('master-cabang.edit', $row->id);
                $deleteUrl = route('master-cabang.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $btnDelete = '';
                // Cabang ID 1 tidak boleh dihapus
                if ($row->id != 1) {
                    $btnDelete = '
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
                    ';
                }

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
                        ' . $btnDelete . '
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

        // Cegah hapus ID 1 jika terpilih
        if (in_array(1, $selectedIds)) {
             return response()->json(['message' => 'Cabang Utama tidak dapat dihapus.'], 400);
        }

        Cabang::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data cabang berhasil dihapus.']);
    }
    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        Cabang::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Cabang berhasil dihapus.']);
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
        $data = new Cabang();
        $data->nama_cabang = $request->nama_cabang;
        $data->save();

        $storeSetting = StoreSetting::where('cabang_id',$data->id)->first();
        if (!empty($storeSetting)) {
            $storeSetting = StoreSetting::where('cabang_id',$data->id)->first();
        }else{
            $storeSetting = new StoreSetting();
        }
        $storeSetting->owner = $request->owner;
        $storeSetting->nama_toko = $request->nama_toko;
        $storeSetting->nomor_hp_toko = $request->nomor_hp_toko;
        $storeSetting->cabang_id = $data->id;
        $storeSetting->is_tax = 0;
        $storeSetting->is_bonus = 1;
        $storeSetting->is_edit_transaksi = 1;
        $storeSetting->is_edit_produk = 1;
        $storeSetting->cabang_id = $data->id;
        $storeSetting->save();

        return redirect()->route('master-cabang.index');
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
        $item = Cabang::findOrFail($id);
        $storeSetting = StoreSetting::find($item->id);

        return view('pages.kepalatoko.master.cabang-edit', [
            'item' => $item,
            'storeSetting' => $storeSetting,
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

        $item = Cabang::findOrFail($id);
        $item->nama_cabang = $request->nama_cabang;
        $item->save();

        $storeSetting = StoreSetting::where('cabang_id',$id)->first();
        if (!empty($storeSetting)) {
            $storeSetting = StoreSetting::where('cabang_id',$id)->first();
        }else{
            $storeSetting = new StoreSetting();
        }
        $storeSetting->owner = $request->owner;
        $storeSetting->nama_toko = $request->nama_toko;
        $storeSetting->nomor_hp_toko = $request->nomor_hp_toko;
        $storeSetting->save();

        return redirect()->route('master-cabang.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Cabang::findOrFail($id);

        if ($id != 1) {
            $storeSetting = StoreSetting::where('cabang_id',$id)->first();
            $storeSetting->delete();
        }

        $item->delete();

        toast('Data Cabang berhasil dihapus.', 'success');

        return redirect()->route('master-cabang.index');
    }
}
