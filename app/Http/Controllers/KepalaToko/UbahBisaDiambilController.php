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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UbahBisaDiambilController extends Controller
{
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
                $orderDetail->total = $spareparts->harga_modal;
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
