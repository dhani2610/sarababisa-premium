<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\StoreSetting;
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
use Illuminate\Support\Facades\Http;

class UbahSudahDiambilController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $processes_count = ServiceTransaction::where('cabang_id',getCabangId())->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $bisadiambil = ServiceTransaction::where('cabang_id',getCabangId())->with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->paginate(10);
        $jumlahbisadiambil = ServiceTransaction::where('cabang_id',getCabangId())->with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->count();
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $users = User::where('cabang_id',getCabangId())->get();
        $types = Type::where('cabang_id',getCabangId())->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $jumlah_bisa_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('cabang_id',getCabangId())->where('status_servis', 'Sudah Diambil')->count();
        $jumlah_semua = ServiceTransaction::where('cabang_id',getCabangId())->get()->count();
        return view('pages/kepalatoko/servis/bisa-diambil', compact(
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

        // 1. Decode JSON ke Array
        $qcMasuk = $item->qc_masuk ? json_decode($item->qc_masuk, true) : [];
        $qcKeluar = $item->qc_keluar ? json_decode($item->qc_keluar, true) : [];
        // dd($qcMasuk,$items->qc_masuk);
        if ($qcMasuk != null) {
            # code...
            $qcItems = array_keys($qcMasuk);
        }else{
            $qcItems = [];
        }

        if (empty($qcItems) && !empty($qcKeluar)) {
            $qcItems = array_keys($qcKeluar);
        }

        if (empty($qcItems)) {
            $qcItems = [];
        }
        // return response()->json([$qcItems,$qcMasuk,$qcKeluar]);

        return view('pages.kepalatoko.servis.transaksi-servis-sudahdiambil', [
            'item' => $item,
            'service_actions' => $service_actions,
            'qcItems' => $qcItems,
            'qcMasuk' => $qcMasuk,
            'qcKeluar' => $qcKeluar,
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
        $garansiList = $request->garansi ?? [];
        foreach ($garansiList as $val) {
            $expired[] = Carbon::now()->addDays($val);
        }

        if ($item->user != null) {
            $persen_teknisi = $item->user->persen;
        } else {
            $persen_teknisi = null;
        }


        $ppn = 0;
        $cekppn = StoreSetting::where('cabang_id',getCabangId())->first();
        if (!empty($cekppn) && $cekppn->is_tax == 1) {
            $ppn = $cekppn->ppn;
        }

        // Hitung dasar (pakai diskon kalau ada)
        if (!empty($request->diskon) && $request->diskon > 0) {
            $baseBiaya = $request->biaya - $request->diskon;
        } else {
            $baseBiaya = $request->biaya;
        }

        // Hitung total dengan PPN
        $biayaFinal = $baseBiaya;
        if ($ppn > 0) {
            $biayaFinal += ($baseBiaya * $ppn / 100);
        }

        // Default
        $tunai = 0;
        $transfer = 0;
        $due = 0;
        $pay = 0;

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
        $ppn = 0;
        $cekppn = StoreSetting::where('cabang_id',getCabangId())->first();
        if (!empty($cekppn)) {
            if ($cekppn->is_tax == 1) {
                $ppn = $cekppn->ppn;
            }else{
                $ppn = 0;
            }
        }

        
        $qc_masuk_data = $request->qc_masuk ?? [];
        $qc_keluar_data = $request->qc_keluar ?? [];

        if ($request->has('custom_item_name')) {
            foreach ($request->custom_item_name as $key => $name) {
                if (!empty($name)) {
                    $val_in = $request->custom_qc_masuk[$key] ?? '-';
                    $val_out = $request->custom_qc_keluar[$key] ?? '-';

                    $qc_masuk_data[$name] = $val_in;
                    $qc_keluar_data[$name] = $val_out;
                }
            }
        }

        $qc_masuk_final = json_encode($qc_masuk_data);
        $qc_keluar_final = json_encode($qc_keluar_data);
        // return response()->json([$request->all(),$qc_masuk_final,$qc_keluar_final]);
        // Transaction create
        $item->update([
            'qc_masuk' => $qc_masuk_data,
            'qc_keluar' => $qc_keluar_final,
            'cara_pembayaran' => $request->cara_pembayaran,
            'diskon' => $request->diskon,
            'garansi'       => !empty($request->garansi) && isset($request->garansi[0]) ? $request->garansi[0] : null,
            'exp_garansi'   => !empty($expired) && isset($expired[0]) ? $expired[0] : null,
            'exp_garansi_j' => json_encode($expired ?? []),

            'status_servis' => $request->status_servis,
            'is_approve' => Auth::user()->role == 'Kepala Toko' ? 'Setuju' : null,
            'tgl_disetujui' => $request->tgl_disetujui,
            'tgl_ambil' => $request->tgl_ambil,
            'pengambil' => $request->pengambil,
            'modal_sparepart' => $request->modal_sparepart,
            'biaya' => $request->biaya,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya - $request->diskon,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - ($bagihasil *= $persen_teknisi),
            'penyerah' => Auth::user()->name,
            'pay' => $pay,
            'due' => $due,
            'tempo' => $tempo,
            'tunai' => $tunai,
            'transfer' => $transfer,
            'ppn' => $ppn,
        ]);

         try {
            $transaksi = ServiceTransaction::findOrFail($item->id);
            // dd($transaksi);
            $teknisiName = $request->users_id
                ? User::find($request->users_id)->name
                : '-';

            $tglAmbil = date('Y-m-d H:i:s')
                ? Carbon::parse(date('Y-m-d H:i:s'))->locale('id')->translatedFormat('d F Y H:i:s')
                : '-';

            $pesan = "📦 *TRANSAKSI UPDATE*\n\n"
                . "🧾 *Status Servis:* {$transaksi->status_servis}\n"
                . "🧾 *Nomor Servis:* {$transaksi->nomor_servis}\n"
                . "🧾 *Tipe:* {$transaksi->tipe}\n"
                . "👤 *Pelanggan:* {$transaksi->nama_pelanggan}\n"
                . "📱 *Barang:* {$transaksi->nama_barang}\n"
                // . "⚙️ *Tindakan Servis:*\n{$tindakanText}\n\n"
                . "💰 *Total Modal Sparepart:* Rp " . number_format($transaksi->modal_sparepart, 0, ',', '.') . "\n"
                . "💰 *Biaya:* Rp " . number_format($transaksi->biaya, 0, ',', '.') . "\n"
                . "💸 *Diskon:* Rp " . number_format($transaksi->diskon ?? 0, 0, ',', '.') . "\n"
                . "🧾 *Total Bayar:* Rp " . number_format($transaksi->pay, 0, ',', '.') . "\n"
                . "👨‍🔧 *Teknisi:* {$teknisiName}\n"
                . "📅 *Tanggal Update:* {$tglAmbil}\n"
                . "🧍‍♂️ *Penyerah:* " . Auth::user()->name;

            $this->sendMessage($pesan);

        } catch (\Exception $e) {
            \Log::error("Gagal kirim Telegram: " . $e->getMessage());
        }

        return redirect()->route('transaksi-servis-sudah-diambil.index');
    }

    public function sendMessage($message)
    {
        $storeSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
        if ($storeSetting && $storeSetting->token_bot && $storeSetting->chat_id) {
            $botToken = $storeSetting->token_bot;
            $chatId   = $storeSetting->chat_id;

            if (!$botToken || !$chatId) {
                \Log::warning('Telegram bot token atau chat_id belum diset di pengaturan toko.');
                return;
            }

            try {
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal kirim pesan Telegram: ' . $e->getMessage());
            }
        }
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
