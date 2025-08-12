<?php

namespace App\Http\Controllers\AdminToko;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\ModelSerie;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UbahSudahDiambilController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $processes_count = ServiceTransaction::whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $bisadiambil = ServiceTransaction::with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->paginate(10);
        $jumlahbisadiambil = ServiceTransaction::with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->count();
        $customers = Customer::all();
        $users = User::all();
        $types = Type::all();
        $brands = Brand::all();
        $capacities = Capacity::all();
        $model_series = ModelSerie::all();
        $jumlah_bisa_diambil = ServiceTransaction::where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->count();
        $jumlah_semua = ServiceTransaction::all()->count();
        return view('pages/admintoko/bisa-diambil', compact(
            'processes_count',
            'customers',
            'users',
            'types',
            'brands',
            'model_series',
            'capacities',
            'jumlah_bisa_diambil',
            'jumlah_sudah_diambil',
            'jumlah_semua',
            'bisadiambil',
            'jumlahbisadiambil'
        ));
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
        //
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
        $item = ServiceTransaction::findOrFail($id);
        $service_actions = ServiceAction::all();

        return view('pages.admintoko.transaksi-servis-sudahdiambil', [
            'item' => $item,
            'service_actions' => $service_actions
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
        // dd($request->all());
        $item = ServiceTransaction::findOrFail($id);
        $profittransaksi = $request->biaya - $request->modal_sparepart - $request->diskon;
        $bagihasil = ($request->biaya - $request->modal_sparepart - $request->diskon) / 100;

        // $garansi = Carbon::now();
        $expired = [];
        $garansiList = $request->garansi ?? [];
        foreach ($garansiList as $val) {
            $expired[] = Carbon::now()->addDays($val);
        }

        if ($item->user != null) {
            $persen_teknisi = $item->user->persen;
        } else {
            $persen_teknisi = null;
        }

        if ($item->kondisi_servis === "Sudah jadi") {
            $persen_admin = Auth::user()->persen;
        } else {
            $persen_admin = null;
        }

        if ($request->cara_pembayaran === 'Tunai & Transfer') {
            $due = 0;
            if ($request->tunai != 0) {
                $transfer = $item->biaya - $request->tunai;
                $pay = $item->biaya;
                $tunai = $request->tunai;
            } else {
                $tunai = $item->biaya - $request->transfer;
                $pay = $item->biaya;
                $transfer = $request->transfer;
            }
        }

        if ($request->cara_pembayaran === 'Tunai') {
            $tunai = $item->biaya;
            $transfer = 0;
            $due = 0;
            $pay = $item->biaya;
        }

        if ($request->cara_pembayaran === 'Transfer') {
            $transfer = $item->biaya;
            $tunai = 0;
            $due = 0;
            $pay = $item->biaya;
        }

        if ($request->cara_pembayaran === 'Kredit') {
            $pay = $request->pay;
            $due = $item->biaya - $request->pay;
            if ($request->tunai) {
                $tunai = $request->pay;
                $transfer = 0;
            } elseif ($request->transfer) {
                $transfer = $request->pay;
                $tunai = 0;
            }
        }

        $waktu = Carbon::today();
        if ($request->tempo != null) {
            $tempo = $waktu->addDays(
                $request->tempo
            );
        } else {
            $tempo = null;
        }

        if ($item->kondisi_servis === 'Tidak bisa' || $item->kondisi_servis === 'Dibatalkan') {
            $pay = 0;
            $due = 0;
            $tunai = 0;
            $transfer = 0;
            $tempo = null;
        }

        // Transaction create
        $item->update([
            'qc_keluar' => $request->qc_keluar,
            'cara_pembayaran' => $request->cara_pembayaran,
            'diskon' => $request->diskon,
            'garansi' => $request->garansi[0],
            'exp_garansi' => $expired[0],
            'exp_garansi_j' => json_encode($expired),
            'status_servis' => $request->status_servis,
            'tgl_ambil' => $request->tgl_ambil,
            'pengambil' => $request->pengambil,
            'modal_sparepart' => $request->modal_sparepart,
            'biaya' => $request->biaya,
            'persen_admin' => $persen_admin,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya - $request->diskon,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - ($bagihasil *= $persen_admin + $persen_teknisi),
            'penyerah' => Auth::user()->name,
            'pay' => $pay,
            'due' => $due,
            'tempo' => $tempo,
            'tunai' => $tunai,
            'transfer' => $transfer,
        ]);

        return redirect()->route('admin-servis-sudah-diambil.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
