<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\StoreSetting;
use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ModelSerie;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Models\TeknisiServis;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\TipeOs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransaksiServisLangsungController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $request->merge([
            'total_modal_sparepart' => str_replace('.', '', $request->total_modal_sparepart),
            'tunai' => str_replace('.', '', $request->tunai),
            'transfer' => str_replace('.', '', $request->transfer),
        ]);


        if (empty($request->customers_id)) {
            $insertCustomer = insertManualPelanggan($request->customers_manual,$request->customers_tlp_manual,$request->customers_kategori_manual,$request->customers_alamat_manual);
            if ($insertCustomer) {
                $request->customers_id = $insertCustomer;
            }
        }
        if (empty($request->types_id)) {
            $insertType = insertManualKategori($request->types_manual);
            if ($insertType) {
                $request->types_id = $insertType;
            }
        }
        if (empty($request->brands_id)) {
            $insertBrand = insertManualBrand($request->brands_manual);
            if ($insertBrand) {
                $request->brands_id = $insertBrand;
            }
        }
        if (empty($request->model_series_id)) {
            $insertModelSeri = insertManualModelSerie($request->model_series_manual,$request->brands_id);
            if ($insertModelSeri) {
                $request->model_series_id = $insertModelSeri;
            }
        }

        $nomor_servis = '' . mt_rand(date('Ymd00'), date('Ymd99')).rand(10,90);
        $nama_pelanggan = Customer::find($request->customers_id);

        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

        DB::beginTransaction();

        try {

            if ($request->has('teknisi') && is_array($request->teknisi)) {

                $grandTotalBiaya = 0;
                $grandTotalModal = 0;
                $mainTechnicianId = null; // Penampung ID Teknisi Utama

                $mainTechDetails = [
                    'tindakan' => null,
                    'actions_id' => null,
                    'products_id' => null,
                    'biaya_j' => null,
                    'modal_j' => null
                ];


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
                    $list_garansi = [];
                    $arr_service_actions_id = [];
                    $arr_products_id = [];
                    $arr_biaya_servis = [];
                    $arr_modal_sparepart = [];

                    $subTotalBiaya = 0;
                    $subTotalModal = 0;

                    if ($request->kondisi_servis !== 'Dibatalkan' && $userId) {
                        // --- 2. LOOP TINDAKAN ---
                        if (isset($techData['tindakan']) && is_array($techData['tindakan'])) {
                            foreach ($techData['tindakan'] as $action) {


                                $garansi_data = $action['garansi'] ?? 0;

                                $garansi = Carbon::now();
                                if ($garansi_data != null) {
                                    $garansi_servis = $garansi->addDays(
                                        $garansi_data
                                    );
                                } else {
                                    $garansi_servis = null;
                                }

                                $list_garansi[] = $garansi_servis;

                                $act_id = $action['service_actions_id'] ?? null;
                                $manual_act = $action['tindakan_servis'] ?? null;
                                $prod_id = $action['products_id'] ?? null;
                                $sales_id = $action['sales_id'] ?? 1;
                                $biaya = filter_var($action['biaya_servis'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                                $modal = filter_var($action['modal_sparepart'] ?? 0, FILTER_SANITIZE_NUMBER_INT);


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
                            $nama_model = ModelSerie::find($request->model_series_id);
                            $bonus_interface = $nama_model->nominal_bonus ?? 0;
                        }
                    }

                    // dd($request->all(),$request->kondisi_servis,$userId);


                    // --- 6. AKUMULASI GRAND TOTAL ---
                    $grandTotalBiaya += $subTotalBiaya;
                    $grandTotalModal += $subTotalModal;


                    // Ambil Nama Tindakan
                    // Jika ini Teknisi Utama, simpan detailnya untuk update tabel Induk (Legacy)
                    if ($index === 0) {
                        $mainTechDetails = [
                            'tindakan' => count($list_tindakan_text) > 0 ? json_encode($list_tindakan_text) : null,
                            'actions_id' => json_encode($arr_service_actions_id),
                            'products_id' => $arr_products_id[0] ?? null,
                            'all_products' => json_encode($arr_products_id),
                            'biaya_j' => json_encode($arr_biaya_servis),
                            'modal_j' => json_encode($arr_modal_sparepart),
                            'garansi' => !empty($garansi_data) ? $garansi_data[0] ?? null : null,
                            'exp_garansi' => !empty($list_garansi) ? $list_garansi[0] ?? null : null,
                            'exp_garansi_j' => !empty($list_garansi) ? json_encode($list_garansi) : null,
                            'bagian_teknisi' => $cekTeknisi->bagian_teknisi,
                            'tipe_teknisi' => $tipeTeknisi,
                        ];
                    }

                } // End Foreach

                // dd($request->all(),$mainTechDetails);


                $grandProfit = $grandTotalBiaya - $grandTotalModal;
                // $garansi = Carbon::now();
                // if ($request->garansi != null) {
                //     $expired = $garansi->addDays(
                //         $request->garansi
                //     );
                // } else {
                //     $expired = null;
                // }

                $modalSparepart = $request->total_modal_sparepart;
                $biaya = $request->biaya ?? 0;
                $profittransaksi = $biaya - $modalSparepart - $request->diskon;
                $bagihasil = ($biaya - $modalSparepart - $request->diskon) / 100;


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


                // dd($request->all(),$expired);

                if ($request->kondisi_servis == 'Dibatalkan') {
                    $finalModal = $request->total_modal_sparepart;
                }else{
                    $finalModal = $modalSparepart;
                }
                // dd($profittransaksi,$request->all());
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
                $transaksi = ServiceTransaction::create([
                    'nomor_servis' => $nomor_servis,
                    'customers_id' => $request->customers_id,
                    'nama_pelanggan' => $nama_pelanggan->nama,
                    'types_id' => $request->types_id,
                    'brands_id' => $request->brands_id,
                    'model_series_id' => $request->model_series_id,
                    'bonus_interface' => $bonus_interface,
                    'tipe' => $mainTechDetails['tipe_teknisi'],
                    'nama_barang' => $nama_barang,
                    'kerusakan' => $request->kerusakan,
                    'imei' => $request->imei,
                    'warna' => $request->warna,
                    'capacities_id' => $request->capacities_id,
                    'kelengkapan' => $request->kelengkapan,
                    'qc_masuk' => $qc_masuk_final,
                    'status_servis' => "Sudah Diambil",
                    'penerima' => $request->penerima,
                    'users_id' => $mainTechnicianId, // Penanggung Jawab Utama
                    'kondisi_servis' => $request->kondisi_servis,
                    // 'service_actions_id' => $request->service_actions_id,
                    'tindakan_servis' => $mainTechDetails['tindakan'],
                    'modal_sparepart' => $grandTotalModal,
                    // 'tipe' => $request->tipe,
                    'biaya' => $grandTotalBiaya,
                    'catatan' => $request->catatan,
                    'persen_teknisi' => $persen_teknisi,
                    'omzet' => $grandTotalBiaya,
                    'profit' => $grandProfit,
                    'profittoko' => $profittransaksi - ($bagihasil * $persen_teknisi),
                    'qc_keluar' => $qc_keluar_final,
                    'cara_pembayaran' => $request->cara_pembayaran,
                    'diskon' => $request->diskon,
                    // 'garansi' => !empty($request->garansi) ? $request->garansi[0] : null,
                    'garansi' => $mainTechDetails['garansi'],
                    'exp_garansi' => $mainTechDetails['exp_garansi'],
                    'exp_garansi_j' => json_encode($mainTechDetails['exp_garansi_j']),
                    'is_admin_toko' => Auth::user()->role == 'Admin Toko' ? 'Admin' : null,
                    'is_approve' => Auth::user()->role == 'Kepala Toko' ? 'Setuju' : null,
                    'tgl_disetujui' => $request->tgl_disetujui,
                    'tgl_ambil' => $request->tgl_ambil,
                    'pengambil' => $nama_pelanggan->nama,
                    'penyerah' => Auth::user()->name,
                    'admin_id' => Auth::user()->id,
                    'pay' => $pay,
                    'due' => $due,
                    'tempo' => $tempo,
                    'tunai' => $tunai,
                    'transfer' => $transfer,
                    'ppn' => $ppn ?? 0,
                    'service_actions' => $mainTechDetails['actions_id'],
                    'products_id' => $mainTechDetails['products_id'],
                    'products' => $mainTechDetails['all_products'],
                    'biaya_j' => $mainTechDetails['biaya_j'],
                    'modal_j' => $mainTechDetails['modal_j'],
                    'cabang_id' => getCabangId()
                ]);


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

                    $teknisiServisDelete = TeknisiServis::where('service_transactions_id', $transaksi->id)->get();
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
                        $list_garansi = [];
                        $arr_service_actions_id = [];
                        $arr_products_id = [];
                        $arr_biaya_servis = [];
                        $arr_modal_sparepart = [];

                        $subTotalBiaya = 0;
                        $subTotalModal = 0;

                        if ($request->kondisi_servis !== 'Dibatalkan' && $userId) {
                            // --- 2. LOOP TINDAKAN ---
                            if (isset($techData['tindakan']) && is_array($techData['tindakan'])) {
                                foreach ($techData['tindakan'] as $action) {

                                    $garansi_data = $action['garansi'] ?? 0;

                                    $garansi = Carbon::now();
                                    if ($garansi_data != null) {
                                        $garansi_servis = $garansi->addDays(
                                            $garansi_data
                                        );
                                    } else {
                                        $garansi_servis = null;
                                    }

                                    $list_garansi[] = $garansi_servis;

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
                                            $this->createSparepartOrder($transaksi->customers_id, $sales_id, $sparepart);
                                        }
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
                                $nama_model = ModelSerie::find($request->model_series_id);
                                $bonus_interface = $nama_model->nominal_bonus ?? 0;
                            }
                        }

                        if ($request->kondisi_servis !== 'Dibatalkan' && $userId) {
                            TeknisiServis::create([
                                'service_transactions_id' => $transaksi->id,
                                'users_id' => $userId,
                                'tipe' => $tipeTeknisi,
                                'modal_sparepart' => $subTotalModal,
                                'biaya' => $subTotalBiaya,
                                'profit' => $profitTransaksi,
                                'profittoko' => $profitToko,
                                'persen_teknisi' => $persen_teknisi,
                                'bonus_interface' => $bonus_interface,
                                'garansi' => $expired[$index] ?? null,

                                // Detail JSON
                                'tindakan_servis' => count($list_tindakan_text) > 0 ? json_encode($list_tindakan_text) : null,
                                'service_actions' => json_encode($arr_service_actions_id),
                                'products' => json_encode($arr_products_id),
                                'biaya_j' => json_encode($arr_biaya_servis),
                                'modal_j' => json_encode($arr_modal_sparepart),
                                'garansi' => !empty($list_garansi) ? json_encode($list_garansi) : null,
                            ]);
                        }



                    } // End Foreach


                }



            }

            DB::commit();

             try {
                // $tindakanText = count($tindakan_servis) > 0
                //     ? "• " . implode("\n• ", $tindakan_servis)
                //     : "-";

                // $teknisiName = $request->users_id
                //     ? User::find($request->users_id)->name
                //     : '-';

                $tglAmbil = $request->tgl_ambil
                    ? Carbon::parse($request->tgl_ambil)->locale('id')->translatedFormat('d F Y')
                    : '-';

                $pesan = "📦 *TRANSAKSI BARU*\n\n"
                    . "🧾 *TRANSAKSI LANGSUNG*\n"
                    . "🧾 *Nomor Servis:* {$transaksi->nomor_servis}\n"
                    . "🧾 *Tipe:* {$transaksi->tipe}\n"
                    . "👤 *Pelanggan:* {$nama_pelanggan->nama}\n"
                    . "📱 *Barang:* {$nama_barang}\n"
                    // . "⚙️ *Tindakan Servis:*\n{$tindakanText}\n\n"
                    . "💰 *Total Modal Sparepart:* Rp " . number_format($transaksi->modal_sparepart, 0, ',', '.') . "\n"
                    . "💰 *Biaya:* Rp " . number_format($transaksi->biaya, 0, ',', '.') . "\n"
                    . "💸 *Diskon:* Rp " . number_format($transaksi->diskon ?? 0, 0, ',', '.') . "\n"
                    . "🧾 *Total Bayar:* Rp " . number_format($transaksi->pay, 0, ',', '.') . "\n"
                    . "💳 *Pembayaran:* {$transaksi->cara_pembayaran}\n\n"
                    // . "👨‍🔧 *Teknisi:* {$teknisiName}\n"
                    . "📅 *Tanggal Ambil:* {$tglAmbil}\n"
                    . "🧍‍♂️ *Penyerah:* " . Auth::user()->name;

                $this->sendMessage($pesan);

            } catch (\Exception $e) {
                \Log::error("Gagal kirim Telegram: " . $e->getMessage());
            }
            toast('Data servis berhasil disimpan.', 'success');

            return redirect()->route('transaksi-servis-sudah-diambil.index')->with('success', 'Data servis berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Error Multi Teknisi: " . $e->getMessage());
            toast('Data servis gagal disimpan.', 'error');

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

    }

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
        $order->cabang_id = getCabangId();
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
        $orderDetail->cabang_id = getCabangId();
        $orderDetail->product_discount_amount = 0;
        $orderDetail->save();
    }

    public function storeOld(Request $request)
    {
        dd($request->all());
        $nomor_servis = '' . mt_rand(date('Ymd00'), date('Ymd99')).rand(10,90);
        $nama_pelanggan = Customer::find($request->customers_id);

        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

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

        $garansi = Carbon::now();
        if ($request->garansi != null) {
            $expired = $garansi->addDays(
                $request->garansi
            );
        } else {
            $expired = null;
        }

        $modalSparepart = array_sum($request->modal_sparepart);
        $biaya = $request->biaya ?? 0;
        $profittransaksi = $biaya - $modalSparepart - $request->diskon;
        $bagihasil = ($biaya - $modalSparepart - $request->diskon) / 100;


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

        $expired = [];
        if (count($request->garansi) > 0) {
            foreach ($request->garansi as $val) {
                array_push($expired, Carbon::now()->addDays(
                    $val
                ));
            }
        } else {
            $expired = null;
        }

        if ($request->kondisi_servis == 'Dibatalkan') {
            $finalModal = $request->total_modal_sparepart;
        }else{
            $finalModal = $modalSparepart;
        }
        // dd($profittransaksi,$request->all());
        $ppn = 0;
        $cekppn = StoreSetting::where('cabang_id',getCabangId())->first();
        if (!empty($cekppn)) {
            if ($cekppn->is_tax == 1) {
                $ppn = $cekppn->ppn;
            }else{
                $ppn = 0;
            }
        }

        $cekTeknisi = User::find($request->users_id);
        if ($cekTeknisi->bagian_teknisi == 'Teknisi Interface') {
            if ($request->tipe == 'Interface') {
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

        // dd($request->all(),$qc_masuk_final,$qc_keluar_final);

        // Transaction create
        $transaksi = ServiceTransaction::create([
            'nomor_servis' => $nomor_servis,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'bonus_interface' => $bonus_interface,
            'tipe' => $request->tipe,
            'nama_barang' => $nama_barang,
            'kerusakan' => $request->kerusakan,
            'imei' => $request->imei,
            'warna' => $request->warna,
            'capacities_id' => $request->capacities_id,
            'kelengkapan' => $request->kelengkapan,
            'qc_masuk' => $qc_masuk_final,
            'status_servis' => "Sudah Diambil",
            'penerima' => $request->penerima,
            'users_id' => $request->users_id,
            'kondisi_servis' => $request->kondisi_servis,
            // 'service_actions_id' => $request->service_actions_id,
            'products_id' => $request->products_id[0],
            'tindakan_servis' => count($tindakan_servis) > 0 ? json_encode($tindakan_servis) : null,
            'modal_sparepart' => $finalModal,
            'biaya' => $request->biaya,
            'catatan' => $request->catatan,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - ($bagihasil * $persen_teknisi),
            'qc_keluar' => $qc_keluar_final,
            'cara_pembayaran' => $request->cara_pembayaran,
            'diskon' => $request->diskon,
            'garansi' => $request->garansi[0],
            'exp_garansi' => $expired[0],
            'exp_garansi_j' => json_encode($expired),
            'is_admin_toko' => Auth::user()->role == 'Admin Toko' ? 'Admin' : null,
            'is_approve' => Auth::user()->role == 'Kepala Toko' ? 'Setuju' : null,
            'tgl_disetujui' => $request->tgl_disetujui,
            'tgl_ambil' => $request->tgl_ambil,
            'pengambil' => $nama_pelanggan->nama,
            'penyerah' => Auth::user()->name,
            'admin_id' => Auth::user()->id,
            'pay' => $pay,
            'due' => $due,
            'tempo' => $tempo,
            'tunai' => $tunai,
            'transfer' => $transfer,
            'ppn' => $ppn ?? 0,
            'service_actions' => json_encode($request->service_actions_id),
            'products' => json_encode($request->products_id),
            'biaya_j' => json_encode($request->biaya_servis),
            'modal_j' => json_encode($request->modal_sparepart),
            'cabang_id' => getCabangId()
        ]);

        if (count($request->products_id) > 0) {

            foreach ($request->products_id as $key => $product) {
                if (!$product) continue;
                $spareparts = Product::find($product);
                $spareparts->stok -= 1;
                $spareparts->save();

                // Menambahkan data transaksi produk sparepart
                $nama_pelanggan = Customer::find($request->customers_id)->nama;
                $order = new Order();
                $order->customers_id = $request->customers_id;
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
                $order->is_approve = 'Setuju';
                $order->tgl_disetujui = Carbon::today();
                $order->cabang_id = getCabangId();
                $order->save();

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
                $orderDetail->garansi = $expired[$key];
                $orderDetail->product_discount_amount = 0;
                $orderDetail->cabang_id = getCabangId();
                $orderDetail->save();
            }
        }

        try {
            $tindakanText = count($tindakan_servis) > 0
                ? "• " . implode("\n• ", $tindakan_servis)
                : "-";

            $teknisiName = $request->users_id
                ? User::find($request->users_id)->name
                : '-';

            $tglAmbil = $request->tgl_ambil
                ? Carbon::parse($request->tgl_ambil)->locale('id')->translatedFormat('d F Y')
                : '-';

            $pesan = "📦 *TRANSAKSI BARU*\n\n"
                . "🧾 *TRANSAKSI LANGSUNG*\n"
                . "🧾 *Nomor Servis:* {$transaksi->nomor_servis}\n"
                . "🧾 *Tipe:* {$transaksi->tipe}\n"
                . "👤 *Pelanggan:* {$nama_pelanggan->nama}\n"
                . "📱 *Barang:* {$nama_barang}\n"
                // . "⚙️ *Tindakan Servis:*\n{$tindakanText}\n\n"
                . "💰 *Total Modal Sparepart:* Rp " . number_format($transaksi->modal_sparepart, 0, ',', '.') . "\n"
                . "💰 *Biaya:* Rp " . number_format($transaksi->biaya, 0, ',', '.') . "\n"
                . "💸 *Diskon:* Rp " . number_format($transaksi->diskon ?? 0, 0, ',', '.') . "\n"
                . "🧾 *Total Bayar:* Rp " . number_format($transaksi->pay, 0, ',', '.') . "\n"
                . "💳 *Pembayaran:* {$transaksi->cara_pembayaran}\n\n"
                . "👨‍🔧 *Teknisi:* {$teknisiName}\n"
                . "📅 *Tanggal Ambil:* {$tglAmbil}\n"
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
}
