<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
class KasbonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $debts = Debt::orderByDesc('created_at')->get();
        $debts_count = Debt::all()->count();
        return view('pages/kepalatoko/kasbon/index', compact('debts', 'debts_count'));
    }

    public function getData(Request $request)
    {
        // Urutkan: Yang belum diapprove (null) di atas, lalu berdasarkan tanggal terbaru
        $query = Debt::with('worker')
            ->where('cabang_id', getCabangId())
            ->orderByRaw('is_approve IS NULL DESC')
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y');
            })
            ->editColumn('total', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('worker_name', function ($row) {
                return $row->worker->name ?? '<span class="text-rose-600">Terhapus</span>';
            })
            ->editColumn('is_approve', function ($row) {
                if ($row->is_approve === 'Setuju') {
                    return '<span class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-blue-500 text-white text-xs">Disetujui</span>';
                } elseif ($row->is_approve === 'Ditolak') {
                    return '<span class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-rose-500 text-white text-xs">Ditolak</span>';
                } else {
                    return '<span class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-amber-500 text-white text-xs">Menunggu</span>';
                }
            })
            ->addColumn('aksi', function ($row) {
                $editUrl = route('kasbon.edit', $row->id);
                $deleteUrl = route('kasbon.destroy', $row->id);
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
            ->rawColumns(['checkbox', 'aksi', 'is_approve', 'worker_name'])
            ->make(true);
    }

    // --- BATCH ACTIONS ---

    public function deleteBatch(Request $request)
    {
        $ids = $request->input('ids');
        Debt::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Data kasbon berhasil dihapus.']);
    }

    public function approveBatch(Request $request)
    {
        $ids = $request->input('ids');
        $tanggal = $request->filled('tgl_disetujui')
            ? Carbon::parse($request->input('tgl_disetujui'))->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');
        Debt::whereIn('id', $ids)->update([
            'is_approve' => 'Setuju',
            'tgl_disetujui' => $tanggal
        ]);
        return response()->json(['message' => 'Data kasbon berhasil disetujui.']);
    }

    public function rejectBatch(Request $request)
    {
        $ids = $request->input('ids');
        Debt::whereIn('id', $ids)->update([
            'is_approve' => 'Ditolak',
            'tgl_disetujui' => Carbon::now()
        ]);
        return response()->json(['message' => 'Data kasbon berhasil ditolak.']);
    }
    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Debt::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data kasbon berhasil dihapus.']);
    }

    public function approveSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        Debt::whereIn('id', $selectedIds)->update(['is_approve' => 'Setuju', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data kasbon berhasil disetujui.']);
    }

    public function rejectSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        Debt::whereIn('id', $selectedIds)->update(['is_approve' => 'Ditolak', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data kasbon berhasil ditolak.']);
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
        $data = $request->all();
        $data['cabang_id'] = getCabangId();
        Debt::create($data);

        return redirect()->route('kasbon.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Debt::findOrFail($id);

        return view('pages.kepalatoko.kasbon.approve', [
            'item' => $item
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Debt::where('cabang_id',getCabangId())->with('worker')->findOrFail($id);
        $workers = Worker::where('cabang_id',getCabangId())->get();

        return view('pages.kepalatoko.kasbon.edit', [
            'item' => $item,
            'workers' => $workers
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
        $request->merge([
            'total' => str_replace('.', '', $request->total),
        ]);

        $data = $request->all();

        $item = Debt::findOrFail($id);

        $item->update($data);

        return redirect()->route('kasbon.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cetak(Request $request)
    {
        if (getCabangId() == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', getCabangId())->where('id', '!=', 1)->where('role', 'Kepala Toko')->orderBy('id', 'asc')->first();
        }
        if (empty($users)) {
            $users = User::find(1);
        }

        $logo = $users->profile_photo_path ?? null;
        $imagePath = $logo ? public_path('storage/' . $logo) : null;

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $workers_id = $request->workers_id;
        $selectedWorker = null;
        if ($workers_id) {
            $selectedWorker = Worker::find($workers_id);
        }

        $query = Debt::with('worker')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'asc');

        if (!empty($workers_id)) {
            $query->where('workers_id', $workers_id);
        }

        if ($request->filled('is_approve')) {
            $query->where('is_approve', $request->is_approve);
        }

        $debts = $query->get();
        $total_kasbon = $debts->sum('total');

        $pdf = Pdf::loadView('pages.kepalatoko.kasbon.cetak-laporan-kasbon', [
            'users' => $users,
            'selectedWorker' => $selectedWorker,
            'imagePath' => $imagePath,
            'debts' => $debts,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_kasbon' => $total_kasbon,
        ]);

        $workerSuffix = $selectedWorker ? ' - ' . $selectedWorker->name : '';
        $filename = 'Laporan Kasbon ' . $start_date . ' sd ' . $end_date . $workerSuffix . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy($id)
    {
        $item = Debt::findOrFail($id);

        $item->delete();

        return redirect()->route('kasbon.index');
    }
}
