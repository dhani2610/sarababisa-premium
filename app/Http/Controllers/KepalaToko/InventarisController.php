<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\InventoryRequest;
use Yajra\DataTables\Facades\DataTables;
class InventarisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inventories = Inventory::latest()->paginate(10);
        $expenses_count = Inventory::all()->count();
        return view('pages/kepalatoko/inventaris/index', compact('inventories', 'expenses_count'));
    }


    public function getData(Request $request)
    {
        $query = Inventory::where('cabang_id', getCabangId())->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y');
            })
            ->editColumn('price', function ($row) {
                return 'Rp. ' . number_format($row->price);
            })
            ->editColumn('masa_penggantian', function ($row) {
                $dateFormatted = Carbon::parse($row->masa_penggantian)->format('d/m/Y');
                // Logic pewarnaan: Jika lewat hari ini = Merah, Jika belum = Biru
                if ($row->masa_penggantian < Carbon::now()) {
                    return '<div class="font-medium text-red-600">' . $dateFormatted . '</div>';
                } else {
                    return '<div class="font-medium text-blue-600">' . $dateFormatted . '</div>';
                }
            })
            ->addColumn('aksi', function ($row) {
                $editUrl = route('inventaris.edit', $row->id);
                $deleteUrl = route('inventaris.destroy', $row->id);
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
                        <div x-data="{ modalOpen: false }">
                             <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true">
                                <span class="sr-only">Delete</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                </svg>
                            </button>
                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
                            <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                                    <div class="p-5 flex space-x-4">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100"><svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" /></svg></div>
                                        <div>
                                            <div class="mb-2"><div class="text-lg font-semibold text-slate-800">Hapus Data?</div></div>
                                            <div class="text-sm mb-10"><div class="space-y-2"><p>Data yang dihapus tidak dapat dikembalikan.</p></div></div>
                                            <div class="flex flex-wrap justify-end space-x-2">
                                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                                <form action="' . $deleteUrl . '" method="POST">
                                                    ' . $csrf . $method . '
                                                    <button type="submit" class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'aksi', 'masa_penggantian'])
            ->make(true);
    }

    public function printSelected(Request $request)
    {
        $users = User::find(1);
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $selectedIds  = $request->input('selectedIds');
        $inventories = Inventory::whereIn('id', $selectedIds)->get();

        $pdf = PDF::loadView('pages.kepalatoko.cetak-label-inventaris', [
            'inventories' => $inventories,
            'imagePath' => $imagePath,
            'users' => $users
        ]);

        $filename = 'Cetak Label Inventaris.pdf';
        return $pdf->stream($filename);
    }
    public function deleteBatch(Request $request)
    {
        $selectedIds  = $request->input('ids');
        Inventory::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data inventaris berhasil dihapus.']);
    }
    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Inventory::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data inventaris berhasil dihapus.']);
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

        $request->merge([
            'price' => str_replace('.', '', $request->price),
        ]);

        $masa_penggantian = Carbon::now();
        $expired = $masa_penggantian->addDays(
            $request->masa_penggantian
        );

        // Transaction create
        Inventory::create([
            'name' => $request->name,
            'code' => $request->code,
            'price' => $request->price,
            'supplier' => $request->supplier,
            'masa_penggantian' => $expired,
            'created_at' => $request->created_at,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('inventaris.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Inventory::findOrFail($id);

        return view('pages.kepalatoko.inventaris.approve', [
            'item' => $item
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Inventory::findOrFail($id);

        return view('pages.kepalatoko.inventaris.edit', [
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->merge([
            'price' => str_replace('.', '', $request->price),
        ]);

        $item = Inventory::findOrFail($id);
        // Transaction update
        $item->update([
            'name' => $request->name,
            'code' => $request->code,
            'price' => $request->price,
            'supplier' => $request->supplier,
            'created_at' => $request->created_at,
            'masa_penggantian' => $request->masa_penggantian,
        ]);

        return redirect()->route('inventaris.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */

    // public function printSelected(Request $request)
    // {
    //     // Mengambil logo dan nama toko
    //     $users = User::find(1);

    //     $logo = $users->profile_photo_path;
    //     $imagePath = public_path('storage/' . $logo);

    //     $selectedIds  = $request->input('selectedIds');
    //     $inventories = Inventory::whereIn('id', $selectedIds)->get();

    //     $pdf = PDF::loadView('pages.kepalatoko.cetak-label-inventaris', [
    //         'inventories' => $inventories,
    //         'imagePath' => $imagePath,
    //         'users' => $users
    //     ]);

    //     $filename = 'Cetak Label Inventaris' . '.pdf';

    //     return $pdf->stream($filename);
    // }

    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);

        $item->delete();

        return redirect()->route('inventaris.index');
    }
}
