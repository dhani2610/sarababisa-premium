<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Worker;
use App\Models\Product;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\ModelSerie;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Carbon\Carbon;

class ServisBelumDisetujuiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/servis/belum-disetujui');
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
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $model_series = ModelSerie::all();
        $service_actions = ServiceAction::all();
        $capacities = Capacity::all();
        $users = User::where('role', 'Teknisi')->get();
        $workers = Worker::where('jabatan', 'like', '%' . 'teknisi')->get();
        $products = Product::whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();

        return view('pages.kepalatoko.servis.belum-disetujui-edit', [
            'item' => $item,
            'types' => $types,
            'customers' => $customers,
            'brands' => $brands,
            'model_series' => $model_series,
            'service_actions' => $service_actions,
            'capacities' => $capacities,
            'users' => $users,
            'workers' => $workers,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     $item = ServiceTransaction::findOrFail($id);

    //     $nama_tipe = Type::find($request->types_id);
    //     $nama_merek = Brand::find($request->brands_id);
    //     $nama_model = ModelSerie::find($request->model_series_id);
    //     $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

    //     if ($request->users_id != null) {
    //         $persen_teknisi = User::find($request->users_id)->persen;
    //     } else {
    //         $persen_teknisi = null;
    //     }

    //     if ($request->service_actions_id != null) {
    //         $tindakan_servis = ServiceAction::find($request->service_actions_id)->nama_tindakan;
    //     } elseif ($request->tindakan_servis != null) {
    //         $tindakan_servis = $request->tindakan_servis;
    //     } else {
    //         $tindakan_servis = null;
    //     }

    //     $profittransaksi = $request->biaya - $request->modal_sparepart - $request->diskon;
    //     $bagihasil = ($request->biaya - $request->modal_sparepart - $request->diskon) / 100;
    //     $nama_pelanggan = Customer::find($request->customers_id);

    //     // Transaction create
    //     $item->update([
    //         'created_at' => $request->created_at,
    //         'tgl_disetujui' => $request->tgl_disetujui,
    //         'users_id' => $request->users_id,
    //         'penerima' => $request->penerima,
    //         'customers_id' => $request->customers_id,
    //         'nama_pelanggan' => $nama_pelanggan->nama,
    //         'types_id' => $request->types_id,
    //         'brands_id' => $request->brands_id,
    //         'model_series_id' => $request->model_series_id,
    //         'nama_barang' => $nama_barang,
    //         'kerusakan' => $request->kerusakan,
    //         'qc_masuk' => $request->qc_masuk,
    //         'qc_keluar' => $request->qc_keluar,
    //         'kondisi_servis' => $request->kondisi_servis,
    //         'service_actions_id' => $request->service_actions_id,
    //         'products_id' => $request->products_id,
    //         'tindakan_servis' => $tindakan_servis,
    //         'modal_sparepart' => $request->modal_sparepart,
    //         'biaya' => $request->biaya,
    //         'uang_muka' => $request->uang_muka,
    //         'diskon' => $request->diskon,
    //         'cara_pembayaran' => $request->cara_pembayaran,
    //         'exp_garansi' => $request->exp_garansi,
    //         'tgl_ambil' => $request->tgl_ambil,
    //         'pengambil' => $request->pengambil,
    //         'persen_teknisi' => $persen_teknisi,
    //         'omzet' => $request->biaya - $request->diskon,
    //         'profit' => $profittransaksi,
    //         'profittoko' => $profittransaksi - ($bagihasil *= $persen_teknisi)
    //     ]);

    //     return redirect()->route('transaksi-servis-belum-disetujui.index');
    // }
    public function update(Request $request, $id)
    {
        $item = ServiceTransaction::findOrFail($id);

        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

        if ($request->users_id != null) {
            $persen_teknisi = User::find($request->users_id)->persen;
        } else {
            $persen_teknisi = null;
        }

        if ($request->service_actions_id != null) {
            $tindakan_servis = ServiceAction::find($request->service_actions_id)->nama_tindakan;
        } elseif ($request->tindakan_servis != null) {
            $tindakan_servis = $request->tindakan_servis;
        } else {
            $tindakan_servis = null;
        }

        $profittransaksi = $request->biaya - $request->modal_sparepart - $request->diskon;
        $bagihasil = ($request->biaya - $request->modal_sparepart - $request->diskon) / 100;
        $nama_pelanggan = Customer::find($request->customers_id);


        $ppn = 0;
        $cekppn = StoreSetting::find(1);
        if (!empty($cekppn) && $cekppn->is_tax == 1) {
            $ppn = $cekppn->ppn;
        }
         if (!empty($request->diskon) && $request->diskon > 0) {
            $baseBiaya = $request->biaya - $request->diskon;
        } else {
            $baseBiaya = $request->biaya;
        }

        $biayaFinal = $baseBiaya;
        // Default
        $tunai = 0;
        $transfer = 0;
        $due = 0;
        $pay = 0;
        if ($ppn > 0) {
            $biayaFinal += ($baseBiaya * $item->ppn / 100);
        }

        if ($request->cara_pembayaran === 'Tunai & Transfer') {
            $due = 0;
            if ($request->tunai != 0) {
                $transfer = $request->transfer;
                $pay = $request->biaya;
                $tunai = $request->tunai;
            } else {
                $tunai = $request->tunai;
                $pay = $request->biaya;
                $transfer = $request->transfer;
            }
        }
        
        // Cara pembayaran
        if ($request->cara_pembayaran === 'Tunai') {
            $tunai = $biayaFinal;
            $transfer = 0;
            $due = 0;
            $pay = $biayaFinal;
        }

        if ($request->cara_pembayaran === 'Transfer') {
            $transfer = $biayaFinal;
            $tunai = 0;
            $due = 0;
            $pay = $biayaFinal;
        }

        if ($request->cara_pembayaran === 'Kredit') {
            $pay = $request->pay;
            $due = $request->biaya - $request->pay;
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

        // Transaction create
        $item->update([
            'created_at' => $request->created_at,
            'tgl_disetujui' => $request->tgl_disetujui,
            'users_id' => $request->users_id,
            'penerima' => $request->penerima,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'nama_barang' => $nama_barang,
            'kerusakan' => $request->kerusakan,
            'qc_masuk' => $request->qc_masuk,
            'qc_keluar' => $request->qc_keluar,
            'kondisi_servis' => $request->kondisi_servis,
            'service_actions_id' => $request->service_actions_id,
            'products_id' => $request->products_id,
            'tindakan_servis' => $tindakan_servis,
            'modal_sparepart' => $request->modal_sparepart,
            'biaya_j' => $request->biaya_j,
            'modal_j' => $request->modal_j,
            'biaya' => $request->biaya,
            'uang_muka' => $request->uang_muka,
            'diskon' => $request->diskon,
            'cara_pembayaran' => $request->cara_pembayaran,
            'exp_garansi' => $request->exp_garansi,
            'tgl_ambil' => $request->tgl_ambil,
            'pengambil' => $request->pengambil,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya - $request->diskon,
            'profit' => $profittransaksi,
             'pay' => $pay,
            'due' => $due,
            'tempo' => $tempo,
            'tunai' => $tunai,
            'transfer' => $transfer,
            'ppn' => $ppn ?? 0,
            'profittoko' => $profittransaksi - ($bagihasil *= $persen_teknisi)
        ]);

        return redirect()->route('transaksi-servis-belum-disetujui.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = ServiceTransaction::findOrFail($id);

        $item->delete();

        return redirect()->route('transaksi-servis-belum-disetujui.index');
    }
}
