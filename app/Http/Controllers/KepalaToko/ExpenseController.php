<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expenses = Expense::latest()->paginate(10);
        $expenses_count = Expense::all()->count();
        $users = User::all();
        return view('pages/kepalatoko/pengeluaran/index', compact('expenses', 'expenses_count', 'users'));
    }

    public function getData(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);
        $query = Expense::with('user')
            ->where('cabang_id', getCabangId())
            ->orderByRaw('is_approve IS NULL DESC') // Pending di atas
            // ->orderBy('created_at', 'desc')
            ->orderByDesc('updated_at')
            ->latest()
            ->skip($offset)
            ->take($limit);

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y');
            })
            ->editColumn('tipe', function ($row) {
                if($row->tipe == 0){
                    return 'Operasional';
                } else if($row->tipe == 1){
                    return 'Servis';
                } else if($row->tipe == 2){
                    return 'Penjualan';
                } 
            })
            ->addColumn('user_name', function ($row) {
                return $row->user->name ?? '<span class="text-rose-600">Akun Terhapus</span>';
            })
            ->editColumn('price', function ($row) {
                return 'Rp ' . number_format($row->price, 0, ',', '.');
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
                $editUrl = route('pengeluaran.edit', $row->id);
                $deleteUrl = route('pengeluaran.destroy', $row->id);
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
            ->rawColumns(['checkbox', 'aksi', 'is_approve', 'user_name'])
            ->make(true);
    }

    // --- BATCH ACTIONS ---

    public function deleteBatch(Request $request)
    {
        $ids = $request->input('ids');
        Expense::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Data pengeluaran berhasil dihapus.']);
    }

    public function tipeBatch(Request $request)
    {
        $ids = $request->input('ids');
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        Expense::whereIn('id', $ids)->update(['tipe' => $request->tipe]);
        return response()->json(['message' => 'Data pengeluaran berhasil diubah.']);
    }
    public function approveBatch(Request $request)
    {
        $ids = $request->input('ids');
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        Expense::whereIn('id', $ids)->update(['is_approve' => 'Setuju', 'tgl_disetujui' => $tanggal]);
        return response()->json(['message' => 'Data pengeluaran berhasil disetujui.']);
    }

    public function rejectBatch(Request $request)
    {
        $ids = $request->input('ids');
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        Expense::whereIn('id', $ids)->update(['is_approve' => 'Ditolak', 'tgl_disetujui' => $tanggal]);
        return response()->json(['message' => 'Data pengeluaran berhasil ditolak.']);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Expense::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data pengeluaran berhasil dihapus.']);
    }

    public function approveSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        Expense::whereIn('id', $selectedIds)->update(['is_approve' => 'Setuju', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data pengeluaran berhasil disetujui.']);
    }

    public function rejectSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        Expense::whereIn('id', $selectedIds)->update(['is_approve' => 'Ditolak', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data pengeluaran berhasil ditolak.']);
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
        if (Auth::user()->role == 'Kepala Toko') {
            $is_approve = 'Setuju';
            $tanggal_approve = date('Y-m-d');
        }else{
            $is_approve = null;
            $tanggal_approve = null;
        }
        // Transaction create
        Expense::create([
            'name' => $request->name,
            'price' => $request->price,
            'users_id' => $request->users_id,
            'tipe' => $request->tipe,
            'is_approve' => $is_approve,
            'tgl_disetujui' => $tanggal_approve,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('pengeluaran.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Expense::findOrFail($id);

        return view('pages.kepalatoko.pengeluaran.approve', [
            'item' => $item
        ]);
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        if (getCabangId() == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini. lalu setting kop di pengaturan toko melalui akun kepala toko', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if ($request->tipe) {
            $expenses = Expense::with('user')->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->where('tipe', $request->tipe)
                ->where('cabang_id', getCabangId())
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            // Mengambil data pengeluaran
            $expenses = Expense::with('user')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'asc')
            ->get();
        }
        // Menghitung total pengeluaran
        $total_pengeluaran = $expenses->sum('price');

        if ($request->tipe == 0) {
            $tipe = 'Operasional';
        } else if ($request->tipe == 1) {
            $tipe = 'Servis';
        } else if ($request->tipe == 2) {
            $tipe = 'Penjualan';
        } else {
            $tipe = 'Semua';
        }

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-pengeluaran', [
            'tipe' => $tipe,
            'users' => $users,
            'imagePath' => $imagePath,
            'expenses' => $expenses,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_pengeluaran' => $total_pengeluaran
        ]);

        $filename = 'Laporan Pengeluaran' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . ' '.$tipe.'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Expense::findOrFail($id);
        $users = User::all();

        return view('pages.kepalatoko.pengeluaran.edit', [
            'item' => $item,
            'users' => $users
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $request->merge([
            'price' => str_replace('.', '', $request->price),
        ]);
        $item = Expense::findOrFail($id);
        // Transaction update
        $item->update([
            'name' => $request->name,
            'price' => $request->price,
            'tipe' => $request->tipe,
            'users_id' => $request->users_id,
            'created_at' => $request->created_at,
            'tgl_disetujui' => $request->tgl_disetujui,
        ]);

        return redirect()->route('pengeluaran.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Expense::findOrFail($id);

        $item->delete();

        return redirect()->route('pengeluaran.index');
    }
}
