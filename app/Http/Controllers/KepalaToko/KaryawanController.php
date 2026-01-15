<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\User;
use App\Models\Budget;
use App\Models\Salary;
use App\Models\Refund;
use App\Models\ServiceTransaction;
use App\Models\TeknisiServis;
use App\Models\Izin;
use App\Models\Worker;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\WorkerRequest;
use App\Models\Attendance;
use App\Models\Incident;
use App\Models\Overtime;
use App\Models\Shift;
use Yajra\DataTables\Facades\DataTables;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workers = Worker::where('cabang_id',getCabangId())->with('worker_users', 'user');
        $debts = Worker::where('cabang_id',getCabangId())->with('debt')->get();
        return view('pages/kepalatoko/karyawan/index', compact('workers', 'debts'));
    }


    public function getData(Request $request)
    {
        // 1. Ambil Filter dari Request
        $filterYear = $request->input('filter_year', date('Y')); // Default tahun sekarang
        $filterMonth = $request->input('filter_month'); // Bisa null (semua bulan) atau 1-12

        // 2. Ambil semua worker aktif di cabang ini
        if (auth()->user()->role == 'Kepala Toko') {
            $workers = Worker::with(['user', 'debt', 'incident'])
                ->where('cabang_id', getCabangId())
                // ->where('id',12)
                ->get();
        }else{
            $workers = Worker::with(['user', 'debt', 'incident'])
                ->where('cabang_id', getCabangId())
                ->where('id',auth()->user()->workers_id)
                ->get();
        }

        $data = collect();

        // 3. Tentukan range bulan yang akan di-loop
        // Jika filter bulan dipilih, loop 1 bulan itu saja. Jika tidak, loop 1-12.
        $months = $filterMonth ? [$filterMonth] : range(1, 12);

        foreach ($workers as $worker) {
            foreach ($months as $m) {
                // Buat objek Carbon untuk periode ini
                $dateObj = Carbon::createFromDate($filterYear, $m, 1);

                // Skip jika bulan belum terjadi (opsional, jika tidak ingin menampilkan masa depan)
                // if ($dateObj->isFuture()) continue;

                $startDate = $dateObj->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $dateObj->copy()->endOfMonth()->format('Y-m-d');
                $periodeLabel = $dateObj->format('F Y'); // Contoh: Januari 2025
                $periodeValue = $dateObj->format('Y-m'); // Untuk value input: 2025-01

                // 4. Hitung Bonus
                $bonus = 0;
                $user = User::where('workers_id', $worker->id)->first();

                if ($user) {
                    $bonus = $this->calculateBonus($user->id, $startDate, $endDate);
                }
                // dd($worker,$user, $startDate, $endDate,$bonus);


                // 5. Hitung Data Lain (Kasbon & Insiden per bulan tersebut)
                // Note: Kita filter collection debt/incident yang sudah di-load agar hemat query
                $kasbonBulanIni = $worker->debt
                    ->where('is_approve', 'Setuju')
                    ->filter(function ($d) use ($filterYear, $m) {
                        return Carbon::parse($d->tgl_disetujui)->year == $filterYear &&
                            Carbon::parse($d->tgl_disetujui)->month == $m;
                    })->sum('total');

                $insidenBulanIni = $worker->incident
                    ->filter(function ($i) use ($filterYear, $m) {
                        return Carbon::parse($i->created_at)->year == $filterYear &&
                            Carbon::parse($i->created_at)->month == $m;
                    })->sum('biaya_teknisi');

                // 6. Siapkan Data Row
                $row = new \stdClass();
                $row->id = $worker->id;
                $row->name = $worker->name;
                $row->jabatan = $worker->jabatan;
                $row->periode_label = $periodeLabel; // Kolom baru untuk Tampilan
                $row->periode_value = $periodeValue; // Data hidden untuk Modal
                $row->bonus = $bonus;

                // Logic Gaji Pokok (Sama seperti sebelumnya)
                $gajiPokok = 0;
                if ($user) {
                    $shift = \App\Models\Shift::where('worker_id',$worker->id)->first();
                    // dd($shift);
                    $gajiPokok = $shift->nominal_gaji ?? 0;
                }
                $row->gaji = $gajiPokok;

                $row->absen = $worker->absen;
                $row->bpjs = $worker->bpjs;
                $row->kasbon_total = $kasbonBulanIni;
                $row->insiden_total = $insidenBulanIni;
                $row->hp = $user->nomor_hp ?? '';

                // Masukkan ke collection utama
                $data->push($row);
            }
        }

        // Return ke DataTables
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->addColumn('periode', function ($row) {
                return '<span class="font-bold text-indigo-600">' . $row->periode_label . '</span>';
            })
            ->editColumn('gaji', fn($row) => 'Rp ' . number_format($row->gaji, 0, ',', '.'))
            ->editColumn('bonus', fn($row) => 'Rp ' . number_format($row->bonus, 0, ',', '.')) // Kolom Bonus Baru
            ->editColumn('absen', fn($row) => 'Rp ' . number_format($row->absen, 0, ',', '.'))
            ->editColumn('bpjs', fn($row) => 'Rp ' . number_format($row->bpjs, 0, ',', '.'))
            ->editColumn('kasbon_total', fn($row) => 'Rp ' . number_format($row->kasbon_total, 0, ',', '.'))
            ->editColumn('insiden_total', fn($row) => 'Rp ' . number_format($row->insiden_total, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                $editUrl = route('karyawan.edit', $row->id);
                $deleteUrl = route('karyawan.destroy', $row->id);
                // Tombol Cetak kita modifikasi onclick-nya untuk kirim periode
                if (auth()->user()->role == 'Kepala Toko') {
                    $hide = '';
                } else {
                    $hide = 'style="display:none;"';
                }
                return '
                    <div class="flex space-x-1 items-center">
                        <a href="' . $editUrl . '" '.$hide.' class=" text-slate-400 hover:text-slate-500" title="Edit">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32"><path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" /></svg>
                        </a>

                        <button type="button" class="text-slate-400 hover:text-slate-500"
                            onclick="openPrintModal(\'' . $row->id . '\', \'' . addslashes($row->name) . '\', \'' . $row->hp . '\', \'' . $row->periode_value . '\')"
                            title="Cetak Slip Gaji Bulan Ini">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-sky-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                <rect x="7" y="13" width="10" height="8" rx="2" />
                            </svg>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'aksi', 'periode'])
            ->make(true);
    }
    // public function getData(Request $request)
    // {
    //     // Eager load relasi untuk menghitung sum dan mencegah N+1 query
    //     $query = Worker::with(['user', 'debt', 'incident'])
    //         ->where('cabang_id', getCabangId())
    //         ->latest();
    //     // dd($query);

    //     return DataTables::of($query)
    //         ->addIndexColumn()
    //         ->addColumn('checkbox', function ($row) {
    //             return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
    //         })
    //         ->editColumn('gaji', function ($row) {

    //             $user = User::where('workers_id',$row->id)->first();
    //             if(empty($user->shift_id)){
    //                 return 'Rp ' . number_format(0, 0, ',', '.');
    //             }
    //             $shift = Shift::where('id',$user->shift_id)->first();

    //             return 'Rp ' . number_format($shift->nominal_gaji ?? 0, 0, ',', '.');
    //         })
    //         ->editColumn('absen', function ($row) {
    //             return 'Rp ' . number_format($row->absen, 0, ',', '.');
    //         })
    //         ->editColumn('bpjs', function ($row) {
    //             return 'Rp ' . number_format($row->bpjs, 0, ',', '.');
    //         })
    //         ->addColumn('kasbon_total', function ($row) {
    //             // Menghitung total kasbon yang disetujui (logic sesuai controller lama)
    //             $total = $row->debt->where('is_approve', 'Setuju')->sum('total'); // Atau sum total semua tergantung kebutuhan view lama
    //             // Di view lama: $item->debt->sum('total') (ambil semua relasi debt yg di load)
    //             return 'Rp ' . number_format($row->debt->sum('total'), 0, ',', '.');
    //         })
    //         ->addColumn('insiden_total', function ($row) {
    //             return 'Rp ' . number_format($row->incident->sum('biaya_teknisi'), 0, ',', '.');
    //         })
    //         ->addColumn('aksi', function ($row) {
    //             $editUrl = route('karyawan.edit', $row->id);
    //             $deleteUrl = route('karyawan.destroy', $row->id);
    //             $csrf = csrf_field();
    //             $method = method_field('DELETE');

    //             // Ambil No HP dari relasi User
    //             // $hp = $row->user->nomor_hp ?? '';

    //             $hp = \App\Models\User::where('workers_id',$row->id)->first()->nomor_hp ?? '';


    //             return '
    //                 <div class="flex space-x-1">
    //                     <a href="' . $editUrl . '">
    //                         <button class="text-slate-400 hover:text-slate-500 rounded-full" title="Edit">
    //                             <span class="sr-only">Edit</span>
    //                             <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
    //                                 <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
    //                             </svg>
    //                         </button>
    //                     </a>

    //                     <div x-data="{ modalOpen: false }">
    //                         <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true" title="Hapus">
    //                             <span class="sr-only">Delete</span>
    //                             <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
    //                                 <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
    //                                 <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
    //                             </svg>
    //                         </button>
    //                         <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
    //                         <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
    //                             <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
    //                                 <div class="p-5 flex space-x-4">
    //                                     <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100"><svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" /></svg></div>
    //                                     <div>
    //                                         <div class="mb-2"><div class="text-lg font-semibold text-slate-800">Hapus Data?</div></div>
    //                                         <div class="text-sm mb-10"><div class="space-y-2"><p>Data yang dihapus tidak dapat dikembalikan.</p></div></div>
    //                                         <div class="flex flex-wrap justify-end space-x-2">
    //                                             <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
    //                                             <form action="' . $deleteUrl . '" method="POST">
    //                                                 ' . $csrf . $method . '
    //                                                 <button type="submit" class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
    //                                             </form>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                     </div>

    //                     <button type="button" class="text-slate-400 hover:text-slate-500 rounded-full"
    //                         onclick="openPrintModal(\'' . $row->id . '\', \'' . addslashes($row->name) . '\', \'' . $hp . '\')"
    //                         title="Cetak / WA">
    //                         <span class="sr-only">Cetak</span>
    //                         <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer w-6 h-6 mt-1" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
    //                             <path stroke="none" d="M0 0h24v24H0z" fill="none" />
    //                             <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
    //                             <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
    //                             <rect x="7" y="13" width="10" height="8" rx="2" />
    //                         </svg>
    //                     </button>
    //                 </div>
    //             ';
    //         })
    //         ->rawColumns(['checkbox', 'aksi'])
    //         ->make(true);
    // }

    public function deleteBatch(Request $request)
    {
        $selectedIds  = $request->input('ids');

        $hasRelation = Worker::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiDebt')
                    ->orWhereHas('relasiSalary');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Gagal! Beberapa data Karyawan memiliki riwayat bonus/kasbon.'], 422);
        // }

        Worker::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Karyawan berhasil dihapus.']);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Worker::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiDebt')
                    ->orWhereHas('relasiSalary');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Data Karyawan yang memiliki riwayat bonus/kasbon tidak bisa dihapus.']);
        // }

        Worker::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Karyawan berhasil dihapus.']);
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
        Worker::create($data);

        return redirect()->route('karyawan.index');
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

    public function cetak(Request $request, $id)
    {
        $tanggal = $request->penanggalan;
        $periode = $request->input('periode');
        $date = Carbon::createFromFormat('Y-m', $periode);

        $start_date = $date->copy()->startOfMonth()->format('Y-m-d');
        $end_date   = $date->copy()->endOfMonth()->format('Y-m-d');
        // dd($start_date, $end_date);

        $namaBulanFile = Carbon::now()->translatedFormat('F Y');

        $items = Worker::findOrFail($id);
        // dd($items);
        $salaries = Salary::where('workers_id', $id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->get();
        $bonusOld = Salary::where('workers_id', $id)->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)
            ->sum('bonus');

        $user = User::where('workers_id', $id)->first();
        $bonus = 0;
        if($user) {
            $bonus = $this->calculateBonus($user->id, $start_date, $end_date);
        }
        // dd($items,$user,$bonus,$start_date, $end_date);
        // dd($bonus,$bonusOld);
        $users = User::find(1);
        $debts = Debt::where('workers_id', $id)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $date->year)
            ->whereMonth('tgl_disetujui', $date->month)
            ->get();
        $incidents = Incident::where('workers_id', $id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->get();
        $totalkasbon = Debt::where('workers_id', $id)
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $date->year)
            ->whereMonth('tgl_disetujui', $date->month)
            ->sum('total');
        $totalinsiden = Incident::where('workers_id', $id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->sum('biaya_teknisi');

        $potonganServis = Refund::where('teknisi_id', $user->id)->whereYear('created_at', $date->year)
        ->whereMonth('created_at', $date->month)
        ->get();

        $totalPotonganServis = $potonganServis;
        $namaKaryawan = $items->name;

        $izin = Izin::where('status',1)->where('user_id',$user->id)->whereYear('tanggal', $date->year)
        ->whereMonth('tanggal', $date->month)->get()->sum('nominal_potongan');
        // dd($izin);
        $overtime = Overtime::where('id_user',$user->id)->whereYear('tanggal', $date->year)
        ->whereMonth('tanggal', $date->month)->get()->sum('nominal_overtime');
        $potongan_telat = Attendance::where('user_id',$user->id)->whereYear('tanggal', $date->year)
        ->whereMonth('tanggal', $date->month)->where('telat',1)->get()->sum('nominal_potongan');

        $shift = Shift::where('worker_id',$id)->first();

        $pdf = PDF::loadView('pages.kepalatoko.karyawan.cetak', [
        // return View('pages.kepalatoko.karyawan.cetak', [
            'overtime' => $overtime,
            'izin' => $izin,
            'tanggal' => $tanggal,
            'periode' => $periode,
            'users' => $users,
            'items' => $items,
            'salaries' => $salaries,
            'bonus' => $bonus,
            'debts' => $debts,
            'incidents' => $incidents,
            'shift' => $shift,
            'totalkasbon' => $totalkasbon,
            'totalPotonganServis' => $totalPotonganServis,
            'totalinsiden' => $totalinsiden,
            'potongan_telat' => $potongan_telat,
        ]);

        $filename = 'Slip Gaji ' . $namaKaryawan . ' ' . '(' . $namaBulanFile . ')' . '.pdf';

        return $pdf->setPaper('a4', 'portrait')->setOption(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isRemoteEnabled', true])->stream($filename);
    }

    // Tambahkan parameter $customDate (opsional)
    public function calculateBonus($id, $start_date, $end_date)
    {
        $user = User::find($id);
        $bonus = 0;



        if ($user->role === 'Teknisi') {
            $total_bonus_interface_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Interface')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('bonus_interface');

            $total_profit_hardware_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Hardware')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('profit');

            $bonus_hardware_main = ($total_profit_hardware_main / 100) * $user->persen;

            $total_bonus_interface_detail = TeknisiServis::where('users_id', $user->id)
                ->where('tipe', 'Interface')
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->sum('bonus_interface');

            $total_bonus_hardware_detail = TeknisiServis::where('users_id', $user->id)
                ->where('tipe', 'Hardware')
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->get()
                ->sum(function ($item) {
                    return $item->profit * ($item->persen_teknisi / 100);
                });

            $bonus = $total_bonus_interface_main + $bonus_hardware_main + $total_bonus_interface_detail + $total_bonus_hardware_detail;

        } elseif ($user->role === 'Sales') {
            $bonus = $user->sale->sum('profit') / 100;
            $bonus *= $user->persen;

        } else {
            $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen'; // default biar aman
            $persen = $user->persen ?? 0;
            $nominalBonus = $user->nominal_bonus_admin ?? 0;

            $totalProfitService = $user->adminservice->sum('profit');
            $totalProfitSale = $user->adminsale->sum('profit');
            $totalNotaService = $user->adminservice->count();
            $totalNotaSale = $user->adminsale->count();

            if ($tipeBonusNota === 'Persen') {
                $bonus = (($totalProfitService + $totalProfitSale) / 100) * $persen;
            } elseif ($tipeBonusNota === 'Tetap') {
                $totalNota = $totalNotaService + $totalNotaSale;
                $bonus = $totalNota * $nominalBonus;
            } else {
                $bonus = 0;
            }
        }

        return $bonus;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Worker::findOrFail($id);
        $budgets = Budget::where('cabang_id',getCabangId())->get();

        return view('pages.kepalatoko.karyawan.edit', [
            'item' => $item,
            'budgets' => $budgets
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
            'absen' => str_replace('.', '', $request->absen),
            'bpjs' => str_replace('.', '', $request->bpjs),
        ]);
        $item = Worker::findOrFail($id);

        $data = $request->all();

        $item->update($data);

        return redirect()->route('karyawan.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Worker::findOrFail($id);

        $item->delete();

        toast('Data Karyawan berhasil dihapus.', 'success');

        return redirect()->route('karyawan.index');
    }
}
