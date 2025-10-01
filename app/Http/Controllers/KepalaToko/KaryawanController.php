<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\User;
use App\Models\Budget;
use App\Models\Salary;
use App\Models\Worker;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\WorkerRequest;
use App\Models\Incident;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workers = Worker::with('worker_users', 'user');
        $debts = Worker::with('debt')->get();
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

        $namaBulanFile = Carbon::now()->translatedFormat('F Y');

        $items = Worker::findOrFail($id);
        $salaries = Salary::where('workers_id', $id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->get();
        $bonus = Salary::where('workers_id', $id)->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)
            ->sum('bonus');
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

        $namaKaryawan = $items->name;

        $pdf = PDF::loadView('pages.kepalatoko.karyawan.cetak', [
            'tanggal' => $tanggal,
            'periode' => $periode,
            'users' => $users,
            'items' => $items,
            'salaries' => $salaries,
            'bonus' => $bonus,
            'debts' => $debts,
            'incidents' => $incidents,
            'totalkasbon' => $totalkasbon,
            'totalinsiden' => $totalinsiden
        ]);

        $filename = 'Slip Gaji ' . $namaKaryawan . ' ' . '(' . $namaBulanFile . ')' . '.pdf';

        return $pdf->setPaper('a4', 'portrait')->setOption(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isRemoteEnabled', true])->stream($filename);
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
