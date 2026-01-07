<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Http\Controllers\Controller;
use App\Imports\ServiceActionImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TindakanServisExport;
use App\Http\Requests\KepalaToko\ServiceActionRequest;
use Yajra\DataTables\Facades\DataTables;

class TindakanServisController extends Controller
{
    public function index()
    {
        $actions = ServiceAction::paginate(10);
        $actions_count = ServiceAction::all()->count();
        return view('pages/kepalatoko/servis/tindakan-servis', compact('actions', 'actions_count'));
    }

    public function getData(Request $request)
    {
        $query = ServiceAction::where('cabang_id', getCabangId())->latest();

        return DataTables::of($query)
            ->addIndexColumn()

            // Checkbox
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('modal_sparepart', fn ($r) => 'Rp. '.number_format($r->modal_sparepart))
            ->editColumn('harga_toko', fn ($r) => 'Rp. '.number_format($r->harga_toko))
            ->editColumn('harga_pelanggan', fn ($r) => 'Rp. '.number_format($r->harga_pelanggan))

            // Aksi (DISAMAKAN PERSIS)
            ->addColumn('aksi', function ($row) {
                $edit = route('tindakan-servis.edit', $row->id);
                $delete = route('tindakan-servis.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                <div class="space-x-1 flex">
                    <a href="'.$edit.'">
                        <button class="text-slate-400 hover:text-slate-500 rounded-full">
                            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4z"/>
                            </svg>
                        </button>
                    </a>

                    <div x-data="{ modalOpen: false }">
                        <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true" aria-controls="danger-modal">
                            <span class="sr-only">Delete</span>
                            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                            </svg>
                        </button>
                        <!-- Modal backdrop -->
                        <div
                            class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                            x-show="modalOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-out duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            aria-hidden="true"
                            x-cloak
                        ></div>
                        <!-- Modal dialog -->
                        <div
                            id="danger-modal"
                            class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                            role="dialog"
                            aria-modal="true"
                            x-show="modalOpen"
                            x-transition:enter="transition ease-in-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in-out duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-4"
                            x-cloak
                        >
                            <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                <div class="p-5 flex space-x-4">
                                    <!-- Icon -->
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                        <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                        </svg>
                                    </div>
                                    <!-- Content -->
                                    <div>
                                        <!-- Modal header -->
                                        <div class="mb-2">
                                            <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                                        </div>
                                        <!-- Modal content -->
                                        <div class="text-sm mb-10">
                                            <div class="space-y-2">
                                                <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                            </div>
                                        </div>
                                        <!-- Modal footer -->
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                            <form action="'.$delete.'" method="post">
                                                '.$csrf.$method.'
                                                <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
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

            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
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
