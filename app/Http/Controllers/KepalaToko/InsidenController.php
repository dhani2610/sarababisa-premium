<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Incident;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\Worker;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
class InsidenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $jumlahhari = Incident::where('cabang_id',getCabangId())->whereDate('created_at', today())
            ->count();
        $totalbiaya = Incident::where('cabang_id',getCabangId())->whereDate('created_at', today())
            ->get()
            ->sum('biaya_toko');
        $jumlahbulan = Incident::where('cabang_id',getCabangId())->whereMonth('created_at', $currentMonth)
            ->count();
        $totalbiayabulan = Incident::where('cabang_id',getCabangId())->whereMonth('created_at', $currentMonth)
            ->get()
            ->sum('biaya_toko');
        $jumlahtahun = Incident::where('cabang_id',getCabangId())->whereYear('created_at', $currentYear)
            ->count();
        $totalbiayatahun = Incident::where('cabang_id',getCabangId())->whereYear('created_at', $currentYear)
            ->get()
            ->sum('biaya_toko');

        $users = Worker::where('cabang_id',getCabangId())->where('jabatan', 'like', '%' . 'Teknisi' . '%')->get();
        $incidents = Incident::where('cabang_id',getCabangId())->with('worker')->get();
        $incidents_count = Incident::where('cabang_id',getCabangId())->get()->count();

        return view('pages/kepalatoko/insiden', compact(
            'users',
            'incidents',
            'incidents_count',
            'jumlahhari',
            'jumlahbulan',
            'jumlahtahun',
            'totalbiaya',
            'totalbiayabulan',
            'totalbiayatahun'
        ));
    }

    public function getData(Request $request)
    {
        $query = Incident::with('worker')->where('cabang_id', getCabangId())->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y');
            })
            ->editColumn('price', function ($row) {
                return 'Rp ' . number_format($row->price, 0, ',', '.');
            })
            ->editColumn('biaya_teknisi', function ($row) {
                return 'Rp ' . number_format($row->biaya_teknisi, 0, ',', '.');
            })
            ->editColumn('biaya_toko', function ($row) {
                return 'Rp ' . number_format($row->biaya_toko, 0, ',', '.');
            })
            ->addColumn('worker_name', function ($row) {
                return $row->worker->name ?? '-';
            })
            ->addColumn('aksi', function ($row) {
                $editUrl = route('insiden.edit', $row->id);
                $deleteUrl = route('insiden.destroy', $row->id);
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

    public function deleteBatch(Request $request)
    {
        $selectedIds  = $request->input('ids');
        Incident::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data insiden berhasil dihapus.']);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Incident::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data insiden berhasil dihapus.']);
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
        $price = (float) str_replace('.', '', $request->price);
        $workerId = $request->workers_id;
        $createdAt = $request->created_at ? Carbon::parse($request->created_at) : now();

        $worker = Worker::find($workerId);
        $user = $worker ? ($worker->user_account ?? \App\Models\User::where('workers_id', $worker->id)->first()) : null;

        $limitToko = $user->nominal_insiden_toko ?? ($worker->nominal_insiden_toko ?? 0);
        $persenKaryawan = $user->persentase_potongan_insiden ?? ($worker->persentase_potongan_insiden ?? 0);

        // Hitung total insiden sebelumnya di bulan & tahun yang sama untuk teknisi ini
        $prevTotal = Incident::where('workers_id', $workerId)
            ->whereMonth('created_at', $createdAt->month)
            ->whereYear('created_at', $createdAt->year)
            ->sum('price');

        // Sisa kuota limit toko yang masih tersedia di bulan ini
        $sisaKuotaToko = max(0, $limitToko - $prevTotal);

        // Porsi insiden yang bisa ditanggung penuh oleh toko dari sisa kuota
        $ditanggungPenuhToko = min($price, $sisaKuotaToko);

        // Kelebihan insiden yang melebihi limit toko
        $melebihiLimit = max(0, $price - $ditanggungPenuhToko);

        // Beban biaya untuk teknisi berdasarkan persentase potongan di akun
        $biayaTeknisi = round($melebihiLimit * ($persenKaryawan / 100));

        // Sisa biaya ditanggung oleh toko
        $biayaToko = $price - $biayaTeknisi;

        // Persentase beban teknisi riil
        $persenTeknisi = $price > 0 ? round(($biayaTeknisi / $price) * 100, 2) : 0;

        // Transaction create
        Incident::create([
            'name' => $request->name,
            'price' => $price,
            'workers_id' => $workerId,
            'persen_teknisi' => $persenTeknisi,
            'biaya_teknisi' => $biayaTeknisi,
            'biaya_toko' => $biayaToko,
            'created_at' => $createdAt,
            'cabang_id' => getCabangId()
        ]);

        return redirect()->route('insiden.index');
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
        $item = Incident::findOrFail($id);
        $users = Worker::where('cabang_id',getCabangId())->where('jabatan', 'like', '%' . 'Teknisi' . '%')->get();

        return view('pages.kepalatoko.insiden-edit', [
            'item' => $item,
            'users' => $users
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
        $item = Incident::findOrFail($id);
        $price = (float) str_replace('.', '', $request->price);
        $workerId = $request->workers_id;
        $createdAt = $request->created_at ? Carbon::parse($request->created_at) : Carbon::parse($item->created_at);

        $worker = Worker::find($workerId);
        $user = $worker ? ($worker->user_account ?? \App\Models\User::where('workers_id', $worker->id)->first()) : null;

        $limitToko = $user->nominal_insiden_toko ?? ($worker->nominal_insiden_toko ?? 0);
        $persenKaryawan = $user->persentase_potongan_insiden ?? ($worker->persentase_potongan_insiden ?? 0);

        // Hitung total insiden sebelumnya di bulan & tahun yang sama untuk teknisi ini (kecuali record ini)
        $prevTotal = Incident::where('workers_id', $workerId)
            ->where('id', '!=', $id)
            ->whereMonth('created_at', $createdAt->month)
            ->whereYear('created_at', $createdAt->year)
            ->sum('price');

        // Sisa kuota limit toko yang masih tersedia di bulan ini
        $sisaKuotaToko = max(0, $limitToko - $prevTotal);

        // Porsi insiden yang bisa ditanggung penuh oleh toko dari sisa kuota
        $ditanggungPenuhToko = min($price, $sisaKuotaToko);

        // Kelebihan insiden yang melebihi limit toko
        $melebihiLimit = max(0, $price - $ditanggungPenuhToko);

        // Beban biaya untuk teknisi berdasarkan persentase potongan di akun
        $biayaTeknisi = round($melebihiLimit * ($persenKaryawan / 100));

        // Sisa biaya ditanggung oleh toko
        $biayaToko = $price - $biayaTeknisi;

        // Persentase beban teknisi riil
        $persenTeknisi = $price > 0 ? round(($biayaTeknisi / $price) * 100, 2) : 0;

        $item->update([
            'name' => $request->name,
            'price' => $price,
            'workers_id' => $workerId,
            'persen_teknisi' => $persenTeknisi,
            'biaya_teknisi' => $biayaTeknisi,
            'biaya_toko' => $biayaToko,
            'created_at' => $createdAt,
        ]);

        return redirect()->route('insiden.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Incident::findOrFail($id);

        $item->delete();

        return redirect()->route('insiden.index');
    }
}
