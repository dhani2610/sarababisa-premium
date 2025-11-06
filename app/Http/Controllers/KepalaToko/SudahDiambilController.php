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
use Yajra\DataTables\Facades\DataTables;

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


    public function getData(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $query = ServiceTransaction::with(['customer', 'user'])
            ->where('status_servis', 'Sudah Diambil')
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
                if (auth()->user()->role == 'Kepala Toko') {
                    return '
                        <a href="'.$url.'" class="text-blue-600 flex items-center">
                            <svg class="w-5 h-5 mr-1 fill-current" viewBox="0 0 32 32"><path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4z"/></svg>
                            '.$row->nomor_servis.'
                        </a>
                    ';
                }
                return '<div class="font-medium">'.$row->nomor_servis.'</div>';
            })

            // ✅ Tanggal Terima
            ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y'))

            // ✅ Penerima
            ->addColumn('penerima', fn($row) => $row->penerima ?? '')

            // ✅ Pelanggan
            ->addColumn('pelanggan', fn($row) => $row->customer?->nama ?? '')

            // ✅ Hubungi (whatsapp + fonnte)
            ->addColumn('hubungi', function ($row) {
                 if (!$row->customer) return '';
                    $toko = StoreSetting::first();
                    $nomor = $row->customer->nomor_hp;
                    $nomorwa = preg_replace('/^08/', '628', $nomor);
                    $fonnteToken = $toko->fonnte ?? null;
                    $hasToken = !empty($fonnteToken);

                    $message = "*Notifikasi Service*\n{$toko->nama_toko}\n\n"
                        . "No. Service : {$row->nomor_servis}\n"
                        . "Nama user : *{$row->nama_pelanggan}*\n"
                        . "Unit : {$row->nama_barang}\n"
                        . "Diambil : {$row->pengambil}\n"
                        . "Tanggal : " . Carbon::parse($row->tgl_ambil)->translatedFormat('d F Y (H:i)') . "\n"
                        . "Status : {$row->kondisi_servis}\n"
                        . "Garansi sampai : " . ($row->exp_garansi ? Carbon::parse($row->exp_garansi)->translatedFormat('d F Y') : 'Tidak ada garansi') . "\n"
                        . "Pembayaran : {$row->cara_pembayaran}\n\n"
                        . "Link tracking : " . env('APP_URL') . "/tracking\n"
                        . "Link Nota : " . route('kepalatoko-pengambilan-cetak-inkjet', $row->id)  . "\n\n"
                        . "Terimakasih";

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
                            <a href="https://wa.me/' . $nomorwa . '/?text=' . $waMessage . '" target="_blank"
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
            ->addColumn('tindakan_servis', fn($row) => implode(', ', json_decode($row->tindakan_servis) ?? []))

            // ✅ Teknisi
            ->addColumn('teknisi', fn($row) => $row->user?->name ?? '<span class="text-red-600">Akun dihapus</span>')

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
                $tokoSetting = \App\Models\StoreSetting::find(1);
                if ((int) ($tokoSetting->is_edit_transaksi ?? 0) == 1 || $tanggalTransaksi->isSameDay($hariIni) || auth()->user()->role == 'Kepala Toko'){
                    $styleHide = '';
                }else{
                    $styleHide = 'display:none!important';
                }

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

                    <div style="'.$styleHide.'" class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <a href="'.$showRoute.'">
                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#000" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M9 14l-4 -4l4 -4" />
                                    <path d="M5 10h11a4 4 0 1 1 0 8h-1" />
                                </svg>
                            </button>
                        </a>
                        <div class="z-10 absolute right-full top-1/2 -translate-y-1/2">
                            <div class="bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-out duration-200"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                x-cloak>
                                <div class="text-xs text-slate-200 whitespace-nowrap">Kembalikan status ke bisa diambil</div>
                            </div>
                        </div>
                    </div>

                    <!-- Start Delete Modal -->
                    <div x-data="{ showDelete: false, deleteId: null }"
                        x-show="showDelete"
                        x-on:open-delete.window="showDelete = true; deleteId = $event.detail.id"
                        x-on:close-delete.window="showDelete = false"
                        x-on:keydown.escape.window="showDelete = false"
                        class="fixed z-50 inset-0">

                        <div x-on:click="showDelete = false" class="fixed inset-0 bg-slate-900 bg-opacity-40" x-cloak></div>

                        <div id="danger-modal"
                            class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                            role="dialog" aria-modal="true"
                            x-show="showDelete" x-cloak>
                            <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                                <div class="p-5 flex space-x-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                        <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="mb-2">
                                            <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                                        </div>
                                        <div class="text-sm mb-10">
                                            <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                        </div>
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600"
                                                    x-on:click="$dispatch(\'close-delete\')">Batal</button>
                                            <form x-bind:action="\''.$destroyBase.'/\' + deleteId" method="post">
                                                '.csrf_field().method_field('delete').'
                                                <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button style="'.$styleHide.'" x-data x-on:click="$dispatch(\'open-delete\', { id: '.$row->id.' })"
                            class="text-rose-500 hover:text-rose-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#ff2825" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="4" y1="7" x2="20" y2="7" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </button>

                </div>';
            })


            ->rawColumns(['checkbox', 'nomor_servis', 'hubungi', 'kondisi_servis', 'status', 'aksi', 'exp_garansi'])
            ->make(true);
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
        $penerima = User::whereNotIn('role',['Investor'])->get();
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
