<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Models\StoreSetting;
use App\Models\User;
use App\Models\MetodePembayaran;
use App\Models\TeknisiServis;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Customer;

class BelumLunasController extends Controller
{
    public function index()
    {
        $storeSetting = StoreSetting::where('cabang_id', getCabangId())->first();
        // Mengarah ke file blade baru
        return view('pages.kepalatoko.servis.belum-lunas', compact('storeSetting'));
    }

    public function cetakPerCustomer(Request $request)
    {
        $request->validate(['customers_id' => 'required']);

        $users = User::find(1);
        if (getCabangId() == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini. lalu setting kop di pengaturan toko melalui akun kepala toko', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Query untuk data yang BELUM LUNAS saja (tipe_status_pembayaran = 0)
        $services = ServiceTransaction::where('cabang_id', getCabangId())
            ->where('customers_id', $request->customers_id)
            ->where('tipe_status_pembayaran', 0)
            ->with('brand', 'modelserie', 'user')
            ->get();

        $customerName  = Customer::find($request->customers_id)->nama;
        if ($services->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi belum lunas untuk pelanggan ini.');
        }

        $total_biaya = $services->sum('biaya');
        $total_modal = $services->sum('modal_sparepart');
        $total_diskon = $services->sum('diskon');
        $total_profit = $services->sum('profit');

          $dataOtherMetodePembayaran = [];
        foreach (getMetodePembayaran() as $key => $value) {
            $dt['metode'] = $value->nama;
            $dt['total']    = (clone $services)->where('cara_pembayaran',$value->nama)->sum('transfer');
            array_push($dataOtherMetodePembayaran,$dt);
        }
        // return response()->json($services);

        $pdf = \PDF::loadView('pages.kepalatoko.cetak-laporan-customer-belum-lunas', [
            'services' => $services,
            'total_biaya' => $total_biaya,
            'total_modal' => $total_modal,
            'total_diskon' => $total_diskon,
            'total_profit' => $total_profit,
            'users' => $users,
            'customerName' => $customerName,
            'imagePath' => $imagePath,
            'dataOtherMetodePembayaran' => $dataOtherMetodePembayaran,
            'customer_name' => $services->first()->customer->nama ?? 'Pelanggan',
        ]);

        return $pdf->stream('Laporan_Belum_Lunas_' . $request->customers_id . '.pdf');
    }


    public function getData(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $query = ServiceTransaction::with(['customer', 'user'])
            ->where('status_servis', 'Sudah Diambil')
            ->where('tipe_status_pembayaran', '0')
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'desc')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()

            // ✅ Checkbox
            ->addColumn('checkbox', function ($row) {
                if (auth()->user()->role == 'Investor') return '';
                return '
                    <div class="flex items-center">
                        <label class="inline-flex">
                            <input class="table-item form-checkbox" type="checkbox" value="' . $row->id . '" />
                        </label>
                    </div>
                ';
            })

            // ✅ Nomor Servis (link edit kalau bukan investor)
            ->addColumn('nomor_servis', function ($row) {
                $url = route('transaksi-servis-sudah-diambil.edit', $row->id);
                if (auth()->user()->role != 'Investor') {
                    $tanggalTransaksi = \Carbon\Carbon::parse($row->created_at);
                    $hariIni = \Carbon\Carbon::today();
                    $tokoSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
                    if ((int) ($tokoSetting->is_edit_transaksi ?? 0) == 1 || $tanggalTransaksi->isSameDay($hariIni) || Auth::user()->role == 'Kepala Toko'){
                        return '
                            <a href="'.$url.'" class="text-blue-600 flex items-center">
                                <svg class="w-5 h-5 mr-1 fill-current" viewBox="0 0 32 32"><path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4z"/></svg>
                                '.$row->nomor_servis.'
                            </a>
                        ';
                    }
                }
                return '<div class="font-medium">'.$row->nomor_servis.'</div>';
            })

            // ✅ Tanggal Terima
            ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y'))

            ->addColumn('tipe_status_pembayaran', fn($row) => $row->tipe_status_pembayaran == 1 ? 'Lunas' : 'Belum Lunas')
            // ✅ Penerima
            ->addColumn('penerima', fn($row) => $row->penerima ?? '')

            // ✅ Pelanggan
            ->addColumn('pelanggan', fn($row) => $row->customer?->nama ?? '')

            // ✅ Hubungi (whatsapp + fonnte)
            ->addColumn('hubungi', function ($row) {
                 if (!$row->customer) return '';
                    $toko = StoreSetting::where('cabang_id',$row->cabang_id)->first();
                    $nomor = $row->customer->nomor_hp;
                    $nomorClean = preg_replace('/[^0-9]/', '', $nomor);

                    // 2. Baru ubah 08 menjadi 628
                    $nomorwa = preg_replace('/^08/', '628', $nomorClean);
                    $fonnteToken = $toko->fonnte ?? null;
                    $hasToken = !empty($fonnteToken);

                    if ($row->cabang_id == 1) {
                        $kp = User::find(1);
                    } else {
                        $kp = User::where('cabang_id', $row->cabang_id)->where('id', '!=', 1)->where('role', 'Kepala Toko')->orderBy('id', 'asc')->first();
                    }
                    $metodePembayaranPaymentGateway = MetodePembayaran::where('cabang_id',getCabangId())->where('nama',$row->cara_pembayaran)->first();
                    if (!empty($metodePembayaranPaymentGateway)) {
                        $is_pg = true;
                    }else{
                        $is_pg = false;
                    }

                    $message = "*Notifikasi Service*\n{$kp->nama_toko}\n\n"
                        . "No. Service : {$row->nomor_servis}\n"
                        . "Nama user : *{$row->nama_pelanggan}*\n"
                        . "Unit : {$row->nama_barang}\n"
                        . "Diambil : {$row->pengambil}\n"
                        . "Tanggal : " . Carbon::parse($row->tgl_ambil)->translatedFormat('d F Y (H:i)') . "\n"
                        . "Status : {$row->kondisi_servis}\n"
                        . "Garansi sampai : " . ($row->exp_garansi ? Carbon::parse($row->exp_garansi)->translatedFormat('d F Y') : 'Tidak ada garansi') . "\n"
                        . "Pembayaran : {$row->cara_pembayaran}\n\n"
                        . "Link tracking : " . env('APP_URL') . "/tracking\n"
                        . "Link Nota : " . route('kepalatoko-pengambilan-cetak-inkjet', $row->id)  . "\n";
                        if ($is_pg) {
                            $message .= "Link QC : " . route('kepalatoko-cetak-qc', $row->id) . "\n";

                            $message .= "Link Pembayaran : " . route('payment', $row->id) . "\n\n";
                        } else {
                            $message .= "Link QC : " . route('kepalatoko-cetak-qc', $row->id) . "\n\n";
                        }

                    $banks = old('banks', json_decode($kp->banks ?? '[]', true));

                    $message .= 'Informasi Pembayaran :' ."\n";

                    $message .= 'Bank : '. $kp->bank . "\n";
                    $message .= 'Norek : '. $kp->rekening . "\n";
                    $message .= 'a.n : '. $kp->pemilik_rekening . "\n";

                    if (!empty($banks)) {

                        foreach ($banks as $bank) {
                            $message .= 'Bank : '. $bank['bank'] . "\n";
                            $message .= 'Norek : '. $bank['rekening'] . "\n";
                            $message .= 'a.n : '. $bank['pemilik'] . "\n";
                        }
                    }

                    $message .= "Terimakasih";

                    $waMessage = rawurlencode($message);

                    $btn = '
                    <div class="flex space-x-1">
                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    ';


                    if ($hasToken) {
                        // ✅ Kirim otomatis via Fonnte
                        $btn .= '
                            <a href="javascript:void(0)"
                                onclick="kirimFontee(\'' . $fonnteToken . '\', \'' . $nomorwa . '\', `' . str_replace('`', '\`', $message) . '`)"
                                title="Kirim otomatis via Fonnte">
                                <svg xmlns=\'http://www.w3.org/2000/svg\' class=\'icon icon-tabler icon-tabler-file-invoice\' width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'#00abfb\' fill=\'none\' stroke-linecap=\'round\' stroke-linejoin=\'round\'>
                                    <path stroke=\'none\' d=\'M0 0h24v24H0z\' fill=\'none\'/>
                                    <path d=\'M14 3v4a1 1 0 0 0 1 1h4\' />
                                    <path d=\'M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z\' />
                                    <line x1=\'9\' y1=\'7\' x2=\'10\' y2=\'7\' />
                                    <line x1=\'9\' y1=\'13\' x2=\'15\' y2=\'13\' />
                                    <line x1=\'13\' y1=\'17\' x2=\'15\' y2=\'17\' />
                                </svg>
                            </a>
                        ';
                    } else {
                        // 💬 Manual via WhatsApp
                        $btn .= '
                            <a href="https://wa.me/' . $nomorwa . '?text=' . $waMessage . '" target="_blank"
                                title="Kirim manual via WhatsApp">
                                <svg xmlns=\'http://www.w3.org/2000/svg\' class=\'icon icon-tabler icon-tabler-file-invoice\' width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'#00abfb\' fill=\'none\' stroke-linecap=\'round\' stroke-linejoin=\'round\'>
                                    <path stroke=\'none\' d=\'M0 0h24v24H0z\' fill=\'none\'/>
                                    <path d=\'M14 3v4a1 1 0 0 0 1 1h4\' />
                                    <path d=\'M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z\' />
                                    <line x1=\'9\' y1=\'7\' x2=\'10\' y2=\'7\' />
                                    <line x1=\'9\' y1=\'13\' x2=\'15\' y2=\'13\' />
                                    <line x1=\'13\' y1=\'17\' x2=\'15\' y2=\'17\' />
                                </svg>
                            </a>
                        ';
                    }

                    $btn .= '
                        <div class="z-10 absolute bottom-full left-1/2 -translate-x-1/2">
                            <div class="min-w-56 bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-out duration-200"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                x-cloak>
                                <div class="text-xs text-slate-200">
                                    Kirim Nota Pengambilan dan link untuk cek Status Garansi
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>';

                    return $btn;
            })

            // ✅ Nama Barang
            ->addColumn('nama_barang', fn($row) => $row->nama_barang)

            // ✅ Kerusakan
            ->addColumn('kerusakan', fn($row) => ucfirst($row->kerusakan))

            // ✅ QC Masuk & QC Keluar
            ->addColumn('qc_masuk', fn($row) => ucfirst($row->qc_masuk))
            ->addColumn('qc_keluar', fn($row) => ucfirst($row->qc_keluar))
            ->addColumn('fungsi', function ($row)  {
                $url = route('kepalatoko-cetak-qc', $row->id);

                return '
                    <a href="' . $url . '" target="_blank" class="btn bg-indigo-500 hover:bg-indigo-600 text-white " title="Lihat PDF QC">
                        Lihat QC
                    </a>
                ';
            })

            // ✅ Kondisi Servis
            ->addColumn('kondisi_servis', function ($row) {
                $colors = [
                    'Sudah jadi' => 'bg-emerald-100 text-emerald-600',
                    'Menunggu konfirmasi' => 'bg-amber-100 text-amber-600',
                    'Tidak bisa' => 'bg-rose-100 text-rose-500',
                ];
                $color = $colors[$row->kondisi_servis] ?? 'bg-slate-100 text-slate-500';
                return '<div class="inline-flex rounded-full px-2.5 py-0.5 '.$color.'">'.$row->kondisi_servis.'</div>';
            })

            // ✅ Tindakan Servis
            // ->addColumn('tindakan_servis', fn($row) => implode(', ', json_decode($row->tindakan_servis) ?? []))

            // // ✅ Teknisi
            // ->addColumn('teknisi', fn($row) => $row->user?->name ?? '<span class="text-red-600">Akun dihapus</span>')

            ->addColumn('tindakan_servis', function ($row) {
                $teknisiServis = TeknisiServis::where('service_transactions_id', $row->id)->get();

                if ($teknisiServis->isEmpty()) {
                    return '<div class="font-medium">'.implode(', ', json_decode($row->tindakan_servis) ?? []).'</div>';
                } else {
                    $tindakanArr = [];
                    foreach ($teknisiServis as $ts) {
                        $decodedItem = json_decode($ts->tindakan_servis);

                        if (is_array($decodedItem)) {
                            $tindakanArr = array_merge($tindakanArr, $decodedItem);
                        }
                        elseif (!empty($ts->tindakan_servis)) {
                            $tindakanArr[] = $ts->tindakan_servis;
                        }
                    }

                    $tindakanArr = array_unique($tindakanArr);

                    return '<div class="font-medium">'.implode(', ', $tindakanArr).'</div>';
                }
            })
            // ->addColumn('tindakan', fn($r) => '<div class="font-medium">'.implode(', ', json_decode($r->tindakan_servis) ?? []).'</div>')
            ->addColumn('teknisi', function ($row) {

                $teknisiServis = TeknisiServis::where('service_transactions_id', $row->id)->get();

                if ($teknisiServis->isEmpty()) {
                    // return '<div class="font-medium">'.e($row->user->name).'</div>';
                    return $row->user
                        ? '<div class="font-medium">'.e($row->user->name).'</div>'
                        : '<div class="font-medium text-red-600">-</div>';
                } else {
                    $tindakanArr = [];
                    foreach ($teknisiServis as $ts) {
                        $tindakanArr[] = $ts->teknisi->name ?? '-';
                    }

                    $tindakanArr = array_unique($tindakanArr);

                    return '<div class="font-medium">'.implode(', ', $tindakanArr).'</div>';
                }

                // return $r->user
                //     ? '<div class="font-medium">'.e($r->user->name).'</div>'
                //     : '<div class="font-medium text-red-600">-</div>';
            })
            // ✅ Modal, Biaya, Diskon
            ->addColumn('modal_sparepart', fn($row) => 'Rp. '.number_format($row->modal_sparepart))
            ->addColumn('biaya', fn($row) => 'Rp. '.number_format($row->biaya))
            ->addColumn('diskon', fn($row) => 'Rp. '.number_format($row->diskon))

            // ✅ Cara Pembayaran
            ->addColumn('cara_pembayaran', fn($row) => $row->cara_pembayaran)

            // ✅ Tanggal Ambil
            ->addColumn('tgl_ambil', fn($row) => Carbon::parse($row->tgl_ambil)->format('d/m/Y H:i'))

            // ✅ Pengambil / Penyerah
            ->addColumn('pengambil', fn($row) => $row->pengambil)
            ->addColumn('penyerah', fn($row) => $row->penyerah)

            // ✅ Masa Garansi
            ->addColumn('exp_garansi', function ($row) {
                if (!$row->exp_garansi) return 'Tidak Ada';
                $color = $row->exp_garansi < now() ? 'text-red-600' : 'text-blue-600';
                return '<div class="font-medium '.$color.'">'.Carbon::parse($row->exp_garansi)->format('d/m/Y').'</div>';
            })

            // ✅ Status Approve
            ->addColumn('status', function ($row) {
                $url = auth()->user()->role == 'Kepala Toko'
                    ? route('transaksi-servis-approve.edit', $row->id)
                    : '#';

                $status = match ($row->is_approve) {
                    null => ['Belum Disetujui', 'bg-amber-500'],
                    'Setuju' => ['Sudah Disetujui', 'bg-blue-500'],
                    default => ['Ditolak', 'bg-red-500'],
                };

                return '<a href="'.$url.'" class="inline-flex px-2.5 py-0.5 text-white rounded-full '.$status[1].'">'.$status[0].'</a>';
            })

            // ✅ Aksi
            ->addColumn('aksi', function ($row) {
                $showRoute   = route('transaksi-servis-sudah-diambil.show', $row->id);
                $destroyBase = route('transaksi-servis-sudah-diambil.destroy', '');
                $termalBase  = route('kepalatoko-nota-pengambilan-termal', '');
                $inkjetBase  = route('kepalatoko-pengambilan-cetak-inkjet', '');

                $tanggalTransaksi = \Carbon\Carbon::parse($row->created_at);
                $hariIni = \Carbon\Carbon::today();
                $tokoSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
                if ((int) ($tokoSetting->is_edit_transaksi ?? 0) == 1 || $tanggalTransaksi->isSameDay($hariIni) || auth()->user()->role == 'Kepala Toko'){
                    $styleHide = '';
                }else{
                    $styleHide = 'display:none!important';
                }
                $urlMultiTeknisi = route('multi-teknisi', $row->id);

                return '
                <div class="space-x-1 flex">

                    <!-- Start Printer -->
                    <div x-data="{ showPrint : false, printId: null }"
                        x-show="showPrint"
                        x-on:open-print.window="showPrint = true; printId = $event.detail.id"
                        x-on:close-print.window="showPrint = false"
                        x-on:keydown.escape.window="showPrint = false"
                        class="fixed z-50 inset-0">

                        <div x-on:click="showPrint = false" class="fixed inset-0 bg-slate-900 bg-opacity-40" x-cloak></div>

                        <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                            x-show="showPrint" x-cloak>
                            <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full">
                                <div class="px-5 py-3 border-b border-slate-200">
                                    <div class="flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                        <button class="text-slate-400 hover:text-slate-500" x-on:click="$dispatch(\'close-print\')">
                                            <svg class="w-4 h-4 fill-current">
                                                <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="px-5 pt-4 pb-1">
                                    <div class="text-sm">
                                        <p>Silahkan pilih printer untuk cetak Nota Pengambilan Servis Selesai.</p>
                                    </div>
                                </div>
                                <div class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <a x-bind:href="\''.$termalBase.'/\' + printId" target="_blank">
                                            <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white">
                                                <span class="mr-1">
                                                    <svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' stroke=\'#fff\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\'>
                                                        <path stroke=\'none\' d=\'M0 0h24v24H0z\' fill=\'none\'/>
                                                        <path d=\'M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2\' />
                                                        <path d=\'M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4\' />
                                                        <rect x=\'7\' y=\'13\' width=\'10\' height=\'8\' rx=\'2\' />
                                                    </svg>
                                                </span>
                                                Printer Termal
                                            </button>
                                        </a>
                                        <a x-bind:href="\''.$inkjetBase.'/\' + printId" target="_blank">
                                            <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                                <span class="mr-1">
                                                    <svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' stroke=\'#fff\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\'>
                                                        <path stroke=\'none\' d=\'M0 0h24v24H0z\' fill=\'none\'/>
                                                        <path d=\'M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2\' />
                                                        <path d=\'M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4\' />
                                                        <rect x=\'7\' y=\'13\' width=\'10\' height=\'8\' rx=\'2\' />
                                                    </svg>
                                                </span>
                                                Printer Inkjet
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Printer -->

                    <button x-data x-on:click="$dispatch(\'open-print\', { id: '.$row->id.' })">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#00abfb" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                            <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                            <rect x="7" y="13" width="10" height="8" rx="2" />
                        </svg>
                    </button>
                </div>';
            })


            ->rawColumns(['fungsi','checkbox', 'nomor_servis', 'hubungi', 'kondisi_servis', 'status', 'aksi', 'exp_garansi','tindakan_servis','teknisi'])
            ->make(true);
    }
}
