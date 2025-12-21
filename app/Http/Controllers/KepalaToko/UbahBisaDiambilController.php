<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\ModelSerie;
use App\Models\TeknisiServis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
class UbahBisaDiambilController extends Controller
{
    public function multiTeknisi($id)
    {
        $item = ServiceTransaction::findOrFail($id);
        $teknisiServis = TeknisiServis::where('service_transactions_id', $id)->get();
        // return response()->json($teknisiServis);
        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $sales = User::where('cabang_id',getCabangId())->where('role', 'Sales')->get();
        $service_actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();

        return view('pages.kepalatoko.servis.transaksi-servis-multi-teknisi', [
            'item' => $item,
            'users' => $users,
            'sales' => $sales,
            'service_actions' => $service_actions,
            'teknisiServis' => $teknisiServis,
            'products' => $products
        ]);
    }
    public function edit($id)
    {
        $item = ServiceTransaction::findOrFail($id);
        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $sales = User::where('cabang_id',getCabangId())->where('role', 'Sales')->get();
        $service_actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();

        return view('pages.kepalatoko.servis.transaksi-servis-bisadiambil', [
            'item' => $item,
            'users' => $users,
            'sales' => $sales,
            'service_actions' => $service_actions,
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
    public function update(Request $request, $id)
    {
        $item = ServiceTransaction::findOrFail($id);

        if ($request->users_id != null) {
            $persen_teknisi = User::find($request->users_id)->persen;
        } else {
            $persen_teknisi = null;
        }

        // --- BLOK LOGIKA YANG DIPERBAIKI ---
        $tindakan_servis = []; // 1. Inisialisasi sebagai array kosong

        // Pastikan request memiliki inputnya untuk menghindari error
        if ($request->has('service_actions_id')) {
            // 2. Lakukan loop pada semua tindakan yang dikirim
            foreach ($request->service_actions_id as $key => $servis_id) {
                $tindakan = null; // Reset untuk setiap iterasi

                // 3. Cek apakah tindakan dipilih dari dropdown
                if (!empty($servis_id)) {
                    $action = ServiceAction::find($servis_id);
                    if ($action) {
                        $tindakan = $action->nama_tindakan;
                    }
                }
                // 4. Jika tidak, cek apakah diisi manual
                elseif (!empty($request->tindakan_servis[$key])) {
                    $tindakan = $request->tindakan_servis[$key];
                }

                // 5. Tambahkan ke array jika ada tindakan yang valid
                if ($tindakan !== null) {
                    array_push($tindakan_servis, $tindakan);
                }
            }
        }
        // --- AKHIR BLOK LOGIKA YANG DIPERBAIKI ---

        $modalSparepart = array_sum($request->modal_sparepart);
        $biaya = $request->biaya ?? 0;
        $profittransaksi = $biaya - $modalSparepart;
        $bagihasil = ($biaya - $modalSparepart) / 100;

        $bonus_interface = 0;
        if ($request->kondisi_servis == 'Dibatalkan') {
            $finalModal = $request->total_modal_sparepart;
        }else{
            $finalModal = $modalSparepart;
            $cekTeknisi = User::find($request->users_id);
            if ($cekTeknisi->bagian_teknisi == 'Teknisi Interface') {
                if ($request->tipe == 'Interface') {
                    $nama_model = ModelSerie::find($item->model_series_id);
                    if (!empty($nama_model)) {
                        $bonus_interface = $nama_model->nominal_bonus;
                    }else{
                    $bonus_interface = 0;
                    }
                }else{
                    $bonus_interface = 0;
                }
            }else{
                $bonus_interface = 0;
            }
        }
        // Transaction create
        $item->update([
            'users_id' => $request->users_id,
            'status_servis' => $request->status_servis,
            'tgl_selesai' => $request->tgl_selesai,
            'kondisi_servis' => $request->kondisi_servis,
            'bonus_interface' => $bonus_interface,
            'tipe' => $request->tipe,
            // 'service_actions_id' => $request->service_actions_id,
            'products_id' => $request->products_id[0] ?? null,
            'tindakan_servis' => count($tindakan_servis) > 0 ? json_encode($tindakan_servis) : null,
            'modal_sparepart' => $finalModal,
            'biaya' => $biaya,
            'catatan' => $request->catatan,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - ($bagihasil * $persen_teknisi),
            'service_actions' => json_encode($request->service_actions_id),
            'products' => json_encode($request->products_id),
            'biaya_j' => json_encode($request->biaya_servis),
            'modal_j' => json_encode($request->modal_sparepart)
        ]);

        if (count($request->products_id) > 0) {

            foreach ($request->products_id as $key => $product) {
                if (!$product) continue;

                $spareparts = Product::find($product);
                $spareparts->stok -= 1;
                $spareparts->save();

                // Menambahkan data transaksi produk sparepart
                $nama_pelanggan = Customer::find($item->customers_id)->nama;
                $order = new Order();
                $order->customers_id = $item->customers_id;
                $order->users_id = $request->sales_id[$key];
                $order->order_date = Carbon::today()->locale('id')->translatedFormat('d F Y');
                $order->total_products = 1;
                // $order->sub_total = $spareparts->harga_jual;
                $order->sub_total = $spareparts->harga_modal;
                $order->invoice_no = '' . mt_rand(date('Ymd00'), date('Ymd99'));
                $order->nama_pelanggan = $nama_pelanggan;
                $order->payment_method = "Tunai";
                // $order->pay = $spareparts->harga_jual;
                $order->pay = $spareparts->harga_modal;
                $order->due = 0;
                $order->save();

                // Menambahkan data detail transaksi produk sparepart
                $garansi = Carbon::now();
                if (
                    $spareparts->garansi != null
                ) {
                    $expired = $garansi->addDays(
                        $spareparts->garansi
                    );
                } else {
                    $expired = null;
                }
                if ($request->sales_id[$key] != 1) {
                    $persen_sales = User::find($request->sales_id[$key])->persen;
                } else {
                    $persen_sales = null;
                }
                $orderDetail = new OrderDetail();
                $orderDetail->orders_id = $order->id;
                $orderDetail->users_id = $request->sales_id[$key];
                $orderDetail->products_id = $request->products_id[$key];
                $orderDetail->product_name = $spareparts->product_name;
                $orderDetail->quantity = 1;
                // $orderDetail->price = $spareparts->harga_jual;
                // $orderDetail->total = $spareparts->harga_jual;
                // $orderDetail->sub_total = $spareparts->harga_jual;
                $orderDetail->price = $spareparts->harga_modal;
                $orderDetail->total = 0;
                $orderDetail->sub_total = $spareparts->harga_modal;
                $orderDetail->modal = $spareparts->harga_modal;
                // $orderDetail->profit = $spareparts->harga_jual - $spareparts->harga_modal;
                // $orderDetail->persen_sales = $persen_sales;
                // $orderDetail->profit_toko = ($spareparts->harga_jual - $spareparts->harga_modal) - ($spareparts->harga_jual - $spareparts->harga_modal) / 100 * $persen_sales;
                $orderDetail->profit = 0;
                $orderDetail->persen_sales = 0;
                $orderDetail->profit_toko = 0;
                $orderDetail->garansi = $expired;
                $orderDetail->product_discount_amount = 0;
                $orderDetail->save();
            }
        }

        try {
            $transaksi = ServiceTransaction::findOrFail($item->id);
            // dd($transaksi);
            $tindakanText = count($tindakan_servis) > 0
                ? "• " . implode("\n• ", $tindakan_servis)
                : "-";

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

        return redirect()->route('transaksi-servis.index');
    }

    public function multiTeknisiProses(Request $request, $id)
    {
        $itemOrigin = ServiceTransaction::findOrFail($id);
        // dd($request->all());
        DB::beginTransaction();

        try {
            if ($request->has('teknisi') && is_array($request->teknisi)) {

                // --- VARIABEL PENAMPUNG GRAND TOTAL (UNTUK TABEL INDUK) ---
                $grandTotalBiaya = 0;
                $grandTotalModal = 0;
                $mainTechnicianId = null; // Penampung ID Teknisi Utama

                // Variabel untuk menyimpan detail Teknisi Utama (Legacy support jika tabel induk butuh JSON detail)
                $mainTechDetails = [
                    'tindakan' => null,
                    'actions_id' => null,
                    'products_id' => null,
                    'biaya_j' => null,
                    'modal_j' => null
                ];

                $teknisiServisDelete = TeknisiServis::where('service_transactions_id', $itemOrigin->id)->get();
                foreach ($teknisiServisDelete as $key => $valueDel) {
                    $valueDel->delete();
                }

                foreach ($request->teknisi as $index => $techData) {
                    // --- 1. PERSIAPAN DATA ---
                    $userId = $techData['user_id'] ?? null;
                    $tipeTeknisi = $techData['tipe'] ?? null;

                    // Set Teknisi Utama (Index 0)
                    if ($index === 0) {
                        $mainTechnicianId = $userId;
                    }

                    // Ambil Persen
                    $persen_teknisi = 0;
                    if ($userId) {
                        $userObj = User::find($userId);
                        $persen_teknisi = $userObj->persen ?? 0;
                    }

                    // Reset variable per teknisi
                    $list_tindakan_text = [];
                    $arr_service_actions_id = [];
                    $arr_products_id = [];
                    $arr_biaya_servis = [];
                    $arr_modal_sparepart = [];

                    $subTotalBiaya = 0;
                    $subTotalModal = 0;

                    // --- 2. LOOP TINDAKAN ---
                    if (isset($techData['tindakan']) && is_array($techData['tindakan'])) {
                        foreach ($techData['tindakan'] as $action) {

                            $act_id = $action['service_actions_id'] ?? null;
                            $manual_act = $action['tindakan_servis'] ?? null;
                            $prod_id = $action['products_id'] ?? null;
                            $sales_id = $action['sales_id'] ?? 1;

                            $biaya = filter_var($action['biaya_servis'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                            $modal = filter_var($action['modal_sparepart'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

                            // Ambil Nama Tindakan
                            $nama_tindakan = null;
                            if (!empty($act_id)) {
                                $actDb = ServiceAction::find($act_id);
                                if ($actDb) $nama_tindakan = $actDb->nama_tindakan;
                            } elseif (!empty($manual_act)) {
                                $nama_tindakan = $manual_act;
                            }
                            if ($nama_tindakan) $list_tindakan_text[] = $nama_tindakan;

                            // Push Array
                            $arr_service_actions_id[] = $act_id;
                            $arr_products_id[] = $prod_id;
                            $arr_biaya_servis[] = $biaya;
                            $arr_modal_sparepart[] = $modal;

                            // Kalkulasi SubTotal per Teknisi
                            $subTotalBiaya += (int)$biaya;
                            $subTotalModal += (int)$modal;

                            // --- 3. STOK & ORDER ---
                            if (!empty($prod_id)) {
                                $sparepart = Product::find($prod_id);
                                if ($sparepart) {
                                    $sparepart->decrement('stok', 1);
                                    $this->createSparepartOrder($itemOrigin->customers_id, $sales_id, $sparepart);
                                }
                            }
                        }
                    }

                    // --- 4. HITUNG PROFIT PER TEKNISI ---
                    $profitTransaksi = $subTotalBiaya - $subTotalModal;
                    $nilaiBagiHasil = ($profitTransaksi) / 100;
                    $profitToko = $profitTransaksi - ($nilaiBagiHasil * $persen_teknisi);

                    // Bonus Interface
                    $bonus_interface = 0;
                    if ($request->kondisi_servis !== 'Dibatalkan' && $userId) {
                        $cekTeknisi = User::find($userId);
                        if ($cekTeknisi && $cekTeknisi->bagian_teknisi == 'Teknisi Interface' && $tipeTeknisi == 'Interface') {
                            $nama_model = ModelSerie::find($itemOrigin->model_series_id);
                            $bonus_interface = $nama_model->nominal_bonus ?? 0;
                        }
                    }
                

                    // --- 5. PERBAIKAN: SELALU SIMPAN KE TABEL TEKNISI SERVIS (ANAK) ---
                    // Baik index 0 maupun index > 0, semua masuk sini biar data lengkap
                    TeknisiServis::create([
                        'service_transactions_id' => $itemOrigin->id,
                        'users_id' => $userId,
                        'tipe' => $tipeTeknisi,
                        'modal_sparepart' => $subTotalModal,
                        'biaya' => $subTotalBiaya,
                        'profit' => $profitTransaksi,
                        'profittoko' => $profitToko,
                        'persen_teknisi' => $persen_teknisi,
                        'bonus_interface' => $bonus_interface,

                        // Detail JSON
                        'tindakan_servis' => count($list_tindakan_text) > 0 ? json_encode($list_tindakan_text) : null,
                        'service_actions' => json_encode($arr_service_actions_id),
                        'products' => json_encode($arr_products_id),
                        'biaya_j' => json_encode($arr_biaya_servis),
                        'modal_j' => json_encode($arr_modal_sparepart)
                    ]);

                    // --- 6. AKUMULASI GRAND TOTAL ---
                    $grandTotalBiaya += $subTotalBiaya;
                    $grandTotalModal += $subTotalModal;

                    // Jika ini Teknisi Utama, simpan detailnya untuk update tabel Induk (Legacy)
                    if ($index === 0) {
                        $mainTechDetails = [
                            'tindakan' => count($list_tindakan_text) > 0 ? json_encode($list_tindakan_text) : null,
                            'actions_id' => json_encode($arr_service_actions_id),
                            'products_id' => $arr_products_id[0] ?? null,
                            'all_products' => json_encode($arr_products_id),
                            'biaya_j' => json_encode($arr_biaya_servis),
                            'modal_j' => json_encode($arr_modal_sparepart)
                        ];
                    }

                } // End Foreach

                // --- 7. UPDATE TABEL INDUK DENGAN GRAND TOTAL ---
                // Profit & Omzet Induk harus akumulasi dari semua teknisi
                $grandProfit = $grandTotalBiaya - $grandTotalModal;
                // Note: Profit toko di induk adalah sisa setelah dikurangi bagi hasil semua teknisi
                // Untuk simplifikasi di header, kita bisa simpan Total Profit kotor atau hitung ulang

                $itemOrigin->update([
                    'users_id' => $mainTechnicianId, // Penanggung Jawab Utama
                    'tgl_selesai' => $request->tgl_selesai,
                    'kondisi_servis' => $request->kondisi_servis ?? 'Selesai',
                    'catatan' => $request->catatan,
                    'biaya' => $grandTotalBiaya,
                    'modal_sparepart' => $grandTotalModal,
                    'omzet' => $grandTotalBiaya,
                    'profit' => $grandProfit,
                    'tindakan_servis' => $mainTechDetails['tindakan'],
                    'service_actions' => $mainTechDetails['actions_id'],
                    'products_id' => $mainTechDetails['products_id'],
                    'products' => $mainTechDetails['all_products'],
                    'biaya_j' => $mainTechDetails['biaya_j'],
                    'modal_j' => $mainTechDetails['modal_j']
                ]);

            }

            DB::commit();
            toast('Data servis multi-teknisi berhasil disimpan.', 'success');

            return redirect()->back()->with('success', 'Data servis multi-teknisi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Error Multi Teknisi: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // --- HELPER: CREATE SPAREPART ORDER ---
    private function createSparepartOrder($customerId, $salesId, $sparepart)
    {
        $nama_pelanggan = Customer::find($customerId)->nama ?? 'Umum';

        $order = new Order();
        $order->customers_id = $customerId;
        $order->users_id = $salesId;
        $order->order_date = Carbon::today()->locale('id')->translatedFormat('d F Y');
        $order->total_products = 1;
        $order->sub_total = $sparepart->harga_modal;
        $order->invoice_no = '' . mt_rand(date('Ymd00'), date('Ymd99'));
        $order->nama_pelanggan = $nama_pelanggan;
        $order->payment_method = "Tunai";
        $order->pay = $sparepart->harga_modal;
        $order->due = 0;
        $order->save();

        // Detail Order
        $garansi = Carbon::now();
        $expired = ($sparepart->garansi != null) ? $garansi->addDays($sparepart->garansi) : null;

        $orderDetail = new OrderDetail();
        $orderDetail->orders_id = $order->id;
        $orderDetail->users_id = $salesId;
        $orderDetail->products_id = $sparepart->id;
        $orderDetail->product_name = $sparepart->product_name;
        $orderDetail->quantity = 1;
        $orderDetail->price = $sparepart->harga_modal;
        $orderDetail->total = 0;
        $orderDetail->sub_total = $sparepart->harga_modal;
        $orderDetail->modal = $sparepart->harga_modal;
        $orderDetail->profit = 0;
        $orderDetail->persen_sales = 0;
        $orderDetail->profit_toko = 0;
        $orderDetail->garansi = $expired;
        $orderDetail->product_discount_amount = 0;
        $orderDetail->save();
    }

    // --- HELPER: TELEGRAM ---
    private function sendTelegramNotification($transaksi, $adminName)
    {
        try {
            $pesan = "📦 *TRANSAKSI UPDATE (MULTI)*\n\n"
                . "🧾 *No Servis:* {$transaksi->nomor_servis}\n"
                . "🧾 *Status:* {$transaksi->status_servis}\n"
                . "💰 *Total Biaya Utama:* Rp " . number_format($transaksi->biaya, 0, ',', '.') . "\n"
                . "ℹ️ *Info:* Terdapat update data multi-teknisi.\n"
                . "🧍‍♂️ *Admin:* " . $adminName;

            $this->sendMessage($pesan);
        } catch (\Exception $e) {
            \Log::error("Telegram Error: " . $e->getMessage());
        }
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
}
