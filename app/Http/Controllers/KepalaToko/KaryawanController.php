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

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Worker::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiDebt')
                    ->orWhereHas('relasiSalary');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Karyawan yang memiliki riwayat bonus/kasbon tidak bisa dihapus.']);
        }

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

        $user = User::where('workers_id',$id)->first();
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

        $shift = Shift::where('id',$user->shift_id)->first();

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
            $bonus = $user->prevsale->sum('profit') / 100;
            $bonus *= $user->persen;

        } else {
            $bonusadminservis = $user->prevadminservice->sum('profit') / 100;
            $bonusadminsale = $user->prevadminsale->sum('profit') / 100;
            $bonus = ($bonusadminservis + $bonusadminsale) * $user->persen;
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
        $budgets = Budget::all();

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
