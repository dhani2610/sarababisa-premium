<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\StoreSetting;
use Carbon\Carbon;
use App\Models\Term;
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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SudahDiambilController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/servis/sudah-diambil');
    }

    public function approveSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        ServiceTransaction::whereIn('id', $selectedIds)->update(['is_approve' => 'Setuju', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data transaksi servis berhasil disetujui.']);
    }

    public function rejectSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        ServiceTransaction::whereIn('id', $selectedIds)->update(['is_approve' => 'Ditolak', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data transaksi servis berhasil ditolak.']);
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
        $nomor_servis = '' . mt_rand(date('Ymd00'), date('Ymd99'));
        $nama_pelanggan = Customer::find($request->customers_id);
        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

        // Transaction create
        ServiceTransaction::create([
            'nomor_servis' => $nomor_servis,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'nama_barang' => $nama_barang,
            'imei' => $request->imei,
            'warna' => $request->warna,
            'capacities_id' => $request->capacities_id,
            'kelengkapan' => $request->kelengkapan,
            'kerusakan' => $request->kerusakan,
            'qc_masuk' => $request->qc_masuk,
            'estimasi_pengerjaan' => $request->estimasi_pengerjaan,
            'estimasi_biaya' => $request->estimasi_biaya,
            'uang_muka' => $request->uang_muka,
            'status_servis' => $request->status_servis,
            'penerima' => $request->penerima
        ]);

        return redirect()->route('transaksi-servis.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = ServiceTransaction::with('user', 'serviceaction', 'product')->findOrFail($id);

        return view('pages.kepalatoko.servis.kembali-bisa-diambil', [
            'item' => $item,
        ]);
    }

    public function pengambilantermal($id)
    {
        $items = ServiceTransaction::with('customer')->findOrFail($id);
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.cetak-termal-pengambilan', [
        // return View('pages.kepalatoko.servis.cetak-termal-pengambilan', [
            'users' => $users,
            'items' => $items,
            'imagePath' => $imagePath,
        ]);

        $filename = 'Nota Pengambilan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
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
        $penerima = User::all();
        $users = User::where('role', 'Teknisi')->get();
        $workers = Worker::where('jabatan', 'like', '%' . 'teknisi')->get();
        $products = Product::whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();
        $sales = User::where('role', 'Sales')->get();

        return view('pages.kepalatoko.servis.sudah-diambil-edit', [
            'item' => $item,
            'types' => $types,
            'customers' => $customers,
            'brands' => $brands,
            'model_series' => $model_series,
            'service_actions' => $service_actions,
            'capacities' => $capacities,
            'users' => $users,
            'workers' => $workers,
            'products' => $products,
            'sales' => $sales,
            'penerima' => $penerima
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

    //     $nama_pelanggan = Customer::find($request->customers_id);

    //     $nama_tipe = Type::find($request->types_id);
    //     $nama_merek = Brand::find($request->brands_id);
    //     $nama_model = ModelSerie::find($request->model_series_id);
    //     $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

    //     if ($request->users_id != null) {
    //         $persen_teknisi = User::find($request->users_id)->persen;
    //     } else {
    //         $persen_teknisi = null;
    //     }

    //     // --- BLOK LOGIKA YANG DIPERBAIKI ---
    //     $tindakan_servis = []; // 1. Inisialisasi sebagai array kosong

    //     // Pastikan request memiliki inputnya untuk menghindari error
    //     if ($request->has('service_actions_id')) {
    //         // 2. Lakukan loop pada semua tindakan yang dikirim
    //         foreach ($request->service_actions_id as $key => $servis_id) {
    //             $tindakan = null; // Reset untuk setiap iterasi

    //             // 3. Cek apakah tindakan dipilih dari dropdown
    //             if (!empty($servis_id)) {
    //                 $action = ServiceAction::find($servis_id);
    //                 if ($action) {
    //                     $tindakan = $action->nama_tindakan;
    //                 }
    //             }
    //             // 4. Jika tidak, cek apakah diisi manual
    //             elseif (!empty($request->tindakan_servis[$key])) {
    //                 $tindakan = $request->tindakan_servis[$key];
    //             }

    //             // 5. Tambahkan ke array jika ada tindakan yang valid
    //             if ($tindakan !== null) {
    //                 array_push($tindakan_servis, $tindakan);
    //             }
    //         }
    //     }
    //     // --- AKHIR BLOK LOGIKA YANG DIPERBAIKI ---

    //     $garansi = Carbon::now();
    //     if ($request->garansi != null) {
    //         $expired = $garansi->addDays(
    //             $request->garansi
    //         );
    //     } else {
    //         $expired = null;
    //     }

    //     $modalSparepart = array_sum($request->modal_sparepart);
    //     $biaya = $request->biaya ?? 0;
    //     $profittransaksi = $biaya - $modalSparepart - $request->diskon;
    //     $bagihasil = ($biaya - $modalSparepart - $request->diskon) / 100;


    //     $ppn = 0;
    //     $cekppn = StoreSetting::find(1);
    //     if (!empty($cekppn) && $cekppn->is_tax == 1) {
    //         $ppn = $cekppn->ppn;
    //     }

    //     // Hitung dasar (pakai diskon kalau ada)
    //     if (!empty($request->diskon) && $request->diskon > 0) {
    //         $baseBiaya = $request->biaya - $request->diskon;
    //     } else {
    //         $baseBiaya = $request->biaya;
    //     }

    //     // Hitung total dengan PPN
    //     $biayaFinal = $baseBiaya;
    //     if ($ppn > 0) {
    //         $biayaFinal += ($baseBiaya * $ppn / 100);
    //     }

    //     // Default
    //     $tunai = 0;
    //     $transfer = 0;
    //     $due = 0;
    //     $pay = 0;

    //     if ($request->cara_pembayaran === 'Tunai & Transfer') {
    //         $due = 0;
    //         if ($request->tunai != 0) {
    //             $transfer = $request->transfer;
    //             $pay = $request->biaya;
    //             $tunai = $request->tunai;
    //         } else {
    //             $tunai = $request->tunai;
    //             $pay = $request->biaya;
    //             $transfer = $request->transfer;
    //         }
    //     }
        
    //     // Cara pembayaran
    //     if ($request->cara_pembayaran === 'Tunai') {
    //         $tunai = $biayaFinal;
    //         $transfer = 0;
    //         $due = 0;
    //         $pay = $biayaFinal;
    //     }

    //     if ($request->cara_pembayaran === 'Transfer') {
    //         $transfer = $biayaFinal;
    //         $tunai = 0;
    //         $due = 0;
    //         $pay = $biayaFinal;
    //     }

    //     if ($request->cara_pembayaran === 'Kredit') {
    //         $pay = $request->pay;
    //         $due = $request->biaya - $request->pay;
    //         if ($request->tunai) {
    //             $tunai = $request->pay;
    //             $transfer = 0;
    //         } elseif ($request->transfer) {
    //             $transfer = $request->pay;
    //             $tunai = 0;
    //         }
    //     }

    //     $waktu = Carbon::today();
    //     if ($request->tempo != null) {
    //         $tempo = $waktu->addDays(
    //             $request->tempo
    //         );
    //     } else {
    //         $tempo = null;
    //     }

    //     $expired = [];
    //     if (count($request->garansi) > 0) {
    //         foreach ($request->garansi as $val) {
    //             array_push($expired, Carbon::now()->addDays(
    //                 $val
    //             ));
    //         }
    //     } else {
    //         $expired = null;
    //     }

    //     if ($request->kondisi_servis == 'Dibatalkan') {
    //         $finalModal = $request->total_modal_sparepart;
    //     }else{
    //         $finalModal = $modalSparepart;
    //     }
    //     // dd($profittransaksi,$request->all()); 
    //     $ppn = 0;
    //     $cekppn = StoreSetting::find(1);
    //     if (!empty($cekppn)) {
    //         if ($cekppn->is_tax == 1) {
    //             $ppn = $cekppn->ppn;
    //         }else{
    //             $ppn = 0;
    //         }
    //     }
    //     // Transaction create
    //     $item->update([
    //         'created_at' => $request->created_at,
    //         'customers_id' => $request->customers_id,
    //         'nama_pelanggan' => $nama_pelanggan->nama,
    //         'types_id' => $request->types_id,
    //         'brands_id' => $request->brands_id,
    //         'model_series_id' => $request->model_series_id,
    //         'nama_barang' => $nama_barang,
    //         'kerusakan' => $request->kerusakan,
    //         'imei' => $request->imei,
    //         'warna' => $request->warna,
    //         'capacities_id' => $request->capacities_id,
    //         'kelengkapan' => $request->kelengkapan,
    //         'qc_masuk' => $request->qc_masuk,
    //         'penerima' => $request->penerima,
    //         'users_id' => $request->users_id,
    //         'kondisi_servis' => $request->kondisi_servis,
    //         'products_id' => $request->products_id[0],
    //         'tindakan_servis' => count($tindakan_servis) > 0 ? json_encode($tindakan_servis) : null,
    //         'modal_sparepart' => $finalModal,
    //         'biaya' => $request->biaya,
    //         'catatan' => $request->catatan,
    //         'persen_teknisi' => $persen_teknisi,
    //         'omzet' => $request->biaya,
    //         'profit' => $profittransaksi,
    //         'profittoko' => $profittransaksi - ($bagihasil * $persen_teknisi),
    //         'qc_keluar' => $request->qc_keluar,
    //         'cara_pembayaran' => $request->cara_pembayaran,
    //         'diskon' => $request->diskon,
    //         'garansi' => $request->garansi[0],
    //         'exp_garansi' => $expired[0],
    //         'exp_garansi_j' => json_encode($expired),
    //         'tgl_ambil' => $request->tgl_ambil,
    //         'pengambil' => $nama_pelanggan->nama,
    //         'penyerah' => Auth::user()->name,
    //         'pay' => $pay,
    //         'due' => $due,
    //         'tempo' => $tempo,
    //         'tunai' => $tunai,
    //         'transfer' => $transfer,
    //         'ppn' => $ppn ?? 0,
    //         'service_actions' => json_encode($request->service_actions_id),
    //         'products' => json_encode($request->products_id),
    //         'biaya_j' => json_encode($request->biaya_servis),
    //         'modal_j' => json_encode($request->modal_sparepart)
    //     ]);

    //     return redirect()->route('transaksi-servis-sudah-diambil.index');
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

        $garansiList = $request->garansi ?? [];
        foreach ($garansiList as $val) {
            $expired[] = Carbon::now()->addDays($val);
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
            'garansi'       => !empty($request->garansi) && isset($request->garansi[0]) ? $request->garansi[0] : null,
            'exp_garansi'   => !empty($expired) && isset($expired[0]) ? $expired[0] : null,
            'exp_garansi_j' => json_encode($expired ?? []),
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

        return redirect()->route('transaksi-servis-sudah-diambil.index');
    }

    public function cetakinkjet($id)
    {
        $items = ServiceTransaction::with('customer')->findOrFail($id);
        $users = User::find(1);
        $terms = Term::find(2);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.notapengambilan-cetak-inkjet', [
        // return View('pages.kepalatoko.servis.notapengambilan-cetak-inkjet', [
            'users' => $users,
            'items' => $items,
            'terms' => $terms,
            'imagePath' => $imagePath,
        ]);

        $filename = 'Nota Pengambilan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    public function back(Request $request, $id)
    {
        $item = ServiceTransaction::findOrFail($id);

        $profittransaksi = $request->biaya - $request->modal_sparepart;
        $bagihasil = ($request->biaya - $request->modal_sparepart) / 100;

        // Transaction update
        $item->update([
            'qc_keluar' => null,
            'cara_pembayaran' => null,
            'status_servis' => 'Bisa Diambil',
            'kondisi_servis' => $request->kondisi_servis,
            'diskon' => null,
            'garansi' => null,
            'exp_garansi' => null,
            'is_approve' => null,
            'pengambil' => null,
            'penyerah' => null,
            'tgl_ambil' => null,
            'tgl_disetujui' => null,
            'omzet' => $request->biaya,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - $bagihasil * ($request->persen_teknisi + $request->persen_admin),
        ]);

        return redirect()->route('transaksi-servis-sudah-diambil.index');
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

        return redirect()->route('transaksi-servis-sudah-diambil.index');
    }
}
