<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Worker;
use App\Models\Product;
use App\Models\Capacity;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ModelSerie;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Auth;

class BisaDiambilController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $storeSetting = StoreSetting::where('cabang_id',getCabangId())->first();;

        return view('pages/kepalatoko/servis/bisa-diambil',compact('storeSetting'));
    }

    public function getData(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $query = ServiceTransaction::with(['customer', 'user'])
            ->where('status_servis', 'Bisa Diambil')
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'desc')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                if (auth()->user()->role === 'Investor') return '';
                return '
                    <div class="flex items-center">
                        <label class="inline-flex">
                            <span class="sr-only">Select</span>
                            <input class="table-item form-checkbox" type="checkbox" value="'.$row->id.'" @click="uncheckParent" />
                        </label>
                    </div>
                ';
            })
            ->addColumn('nomor_servis', function ($row) {
                if (auth()->user()->role !== 'Investor') {
                    $tanggalTransaksi = \Carbon\Carbon::parse($row->created_at);
                    $hariIni = \Carbon\Carbon::today();
                    $tokoSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
                    if ((int) ($tokoSetting->is_edit_transaksi ?? 0) == 1 || $tanggalTransaksi->isSameDay($hariIni) || Auth::user()->role == 'Kepala Toko'){
                        return '
                            <a href="'.route('transaksi-servis-bisa-diambil.edit', $row->id).'">
                                <div class="flex items-center text-blue-600">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                        <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                    </svg>
                                    <div class="font-medium">'.$row->nomor_servis.'</div>
                                </div>
                            </a>';
                    }
                }
                return '<div class="font-medium">'.$row->nomor_servis.'</div>';
            })
            ->addColumn('tgl_terima', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y'))
            ->addColumn('penerima', fn($row) => '<div class="font-medium">'.e($row->penerima).'</div>')
            ->addColumn('pelanggan', function ($row) {
                if (!$row->customer)
                    return '<div class="font-medium text-rose-600">Data pelanggan telah dihapus</div>';
                return '<div class="font-medium">'.e($row->customer->nama).'</div>';
            })
            ->addColumn('hubungi', function ($row) {
                if (auth()->user()->role === 'Investor') return '';

                $nomor = $row->customer->nomor_hp ?? null;
                if (!$nomor) return '-';

                // Format nomor ke 628...
                $nomorwa = preg_replace('/^08/', '628', $nomor);

                // Ambil data toko & token
                $toko = User::find(1);
                $fonteeToken = StoreSetting::first()->fonnte ?? null;
                $notaQc = route('kepalatoko-cetak-qc', $row->id);

                // --- SUSUN PESAN (Gunakan \n untuk enter, jangan %0A manual dulu) ---
                $rawPesan = "*Notifikasi | {$toko->nama_toko}*\n" .
                            "Barang Servis: *{$row->nama_barang}*\n" .
                            "No. Servis: *{$row->nomor_servis}*\n" .
                            "Kondisi: *{$row->kondisi_servis}*\n" .
                            "Tanggal: " . Carbon::parse($row->tgl_selesai)->translatedFormat('d F Y') . "\n" .
                            "Status: *{$row->status_servis}*\n" .
                            "Biaya: Rp. " . number_format($row->biaya) . "\n\n" .
                            "Link QC: {$notaQc}\n\n" .
                            "Terima Kasih.";

                // $pesan = rawurlencode("*Notifikasi | {$toko->nama_toko}*%0ABarang Servis *{$row->nama_barang}*%0A"
                //     ."No. Servis *{$row->nomor_servis}*%0AKondisi: *{$row->kondisi_servis}*%0A"
                //     ."Tanggal: ".Carbon::parse($row->tgl_selesai)->translatedFormat('d F Y')."%0A"
                //     ."Status: *{$row->status_servis}*%0ABiaya: Rp. ".number_format($row->biaya)."%0A%0ALink QC: {$notaQc}%0A"."%0A%0ATerima Kasih.");

                // Encode untuk Link WA Biasa (mengubah spasi jadi %20, enter jadi %0A, dll)
                $waLinkPesan = rawurlencode($rawPesan);

                // Escape untuk Javascript (Fonnte) agar kutip/enter tidak bikin error JS
                $jsPesan = json_encode($rawPesan);
                // Kita trim kutip dua di awal/akhir dari hasil json_encode agar pas masuk ke function JS
                $jsPesan = trim($jsPesan, '"');

                // --- LOGIKA TOMBOL NOTIFIKASI (TOMBOL KE-2) ---
                if ($fonteeToken) {
                    // Jika pakai Fonnte
                    $btnNotif = '<a href="javascript:void(0)" onclick="kirimFontee(\''.$fonteeToken.'\', \''.$nomorwa.'\', \''.$jsPesan.'\')" title="Kirim Notifikasi (Fonnte)">';
                } else {
                    // Jika pakai WA API Biasa (Gratis)
                    $btnNotif = '<a href="https://wa.me/'.$nomorwa.'?text='.$waLinkPesan.'" target="_blank" title="Kirim Notifikasi (WA Biasa)">';
                }

                // --- RETURN TAMPILAN 2 IKON ---
                return '
                    <div class="flex space-x-2">

                        <a href="https://wa.me/'.$nomorwa.'" target="_blank" title="Chat Kosong/Manual">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#00b341" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1 -.9 3.8 8.5 8.5 0 0 1 -7.6 4.7 8.38 8.38 0 0 1 -3.8 -.9l-5.1 1.2l1.2 -5.1a8.38 8.38 0 0 1 -.9 -3.8 8.5 8.5 0 0 1 4.7 -7.6 8.38 8.38 0 0 1 3.8 -.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                            </svg>
                        </a>

                        '.$btnNotif.'
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#00abfb" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" />
                            </svg>
                        </a>

                    </div>';
            })
            ->addColumn('nama_barang', fn($r) => '<div class="font-medium">'.e($r->nama_barang).'</div>')
            ->addColumn('kerusakan', fn($r) => '<div class="font-medium">'.e($r->kerusakan).'</div>')

            ->addColumn('fungsi', function ($row)  {
                $url = route('kepalatoko-cetak-qc', $row->id);

                return '
                    <a href="' . $url . '" target="_blank" class="btn bg-indigo-500 hover:bg-indigo-600 text-white " title="Lihat PDF QC">
                        Lihat QC
                    </a>
                ';
            })
            // ->addColumn('fungsi', fn($r) => '<div class="font-medium">'.e($r->qc_masuk).'</div>')
            ->addColumn('kondisi', function ($row) {
                $color = match ($row->kondisi_servis) {
                    'Sudah jadi' => 'bg-emerald-100 text-emerald-600',
                    'Menunggu konfirmasi' => 'bg-amber-100 text-amber-600',
                    'Tidak bisa' => 'bg-rose-100 text-rose-500',
                    default => 'bg-slate-100 text-slate-500',
                };
                return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 '.$color.'">'.e($row->kondisi_servis).'</div>';
            })
            ->addColumn('tindakan', fn($r) => '<div class="font-medium">'.implode(', ', json_decode($r->tindakan_servis) ?? []).'</div>')
            ->addColumn('teknisi', function ($r) {
                return $r->user
                    ? '<div class="font-medium">'.e($r->user->name).'</div>'
                    : '<div class="font-medium text-red-600">-</div>';
            })
            ->addColumn('modal_sparepart', function ($r) {
                if (auth()->user()->role === 'Investor') return '';
                return '<div class="font-medium">Rp. '.number_format($r->modal_sparepart).'</div>';
            })
            ->addColumn('biaya', fn($r) => '<div class="font-medium">Rp. '.number_format($r->biaya).'</div>')
            ->addColumn('tgl_selesai', fn($r) => Carbon::parse($r->tgl_selesai)->format('d/m/Y'))
            ->addColumn('aksi', function ($row) {
                $delurl = route('transaksi-servis-bisa-diambil.destroy', $row->id);
                if (auth()->user()->role === 'Investor') return '';

                $tanggalTransaksi = \Carbon\Carbon::parse($row->created_at);
                $hariIni = \Carbon\Carbon::today();
                $tokoSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
                if ((int) ($tokoSetting->is_edit_transaksi ?? 0) == 1 || $tanggalTransaksi->isSameDay($hariIni) || auth()->user()->role == 'Kepala Toko'){
                    $styleHide = '';
                }else{
                    $styleHide = 'display:none!important';
                }
                return '
                    <div class="space-x-1 flex">
                          <button type="button"
                                class="text-indigo-500 hover:text-indigo-600 rounded-full btn-upload-foto ml-1"
                                data-id="' . $row->id . '"
                                title="Upload Foto Servis">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-camera" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"></path>
                            <path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                            </svg>
                        </button>
                            <div>
                                <button onclick="openPinModal(' . $row->id . ')" class="text-indigo-500 hover:text-indigo-600 rounded-full">
                                    <span class="sr-only">Service PIN & Pola</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6366f1" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <rect x="5" y="11" width="14" height="10" rx="2" />
                                        <path d="M8 11v-4a4 4 0 0 1 8 0v4" />
                                    </svg>
                                </button>
                            </div>

                            <div x-data="{ open: false }"
                                x-show="open"
                                @open-pin-modal-'.$row->id.'.window="open = true"
                                @close-pin-modal-'.$row->id.'.window="open = false"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                x-cloak
                                @click.self="open = false">

                                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                                    <div class="flex justify-between items-center border-b pb-2 mb-4">
                                        <h2 class="text-lg font-semibold text-gray-700">Service PIN & Pola</h2>
                                        <button @click="open=false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
                                    </div>

                                    <div class="space-y-4">
                                        <!-- PIN -->
                                        <div>
                                            <label class="text-sm font-medium text-gray-600">PIN</label> <br>
                                            <input type="number" value="{{ $process->pin }}"
                                                wire:model.defer="pin"  id="pinInput-'.$row->id.'"
                                                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200">
                                        </div>

                                        <!-- Pola -->
                                        <div>
                                            <label class="text-sm font-medium text-gray-600">Pola</label>
                                            <canvas id="sig-canvas-'.$row->id.'" class="sig-canvas border rounded w-full h-48 bg-gray-100"></canvas>
                                            <input type="hidden" id="polaInput-'.$row->id.'" wire:model.defer="pola" class="polaInput">
                                            <small class="text-gray-400">Gambar pola (opsional)</small>
                                        </div>

                                        <button type="button" onclick="resetCanvas('.$row->id.')"
                                            class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                                            Reset Pola
                                        </button>
                                    </div>

                                    <div class="mt-6 flex justify-end space-x-2">
                                        <button onclick="saveCanvasAjax('.$row->id.')"
                                            class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                                            Simpan
                                        </button>
                                    </div>

                                </div>
                            </div>
                        <a href="'.route('ubah-sudah-diambil-edit', $row->id).'" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#00b341" fill="none" viewBox="0 0 24 24">
                                <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2v-1a2 2 0 0 0 -2 -2h-2a2 2 0 0 0 -2 2v1z" />
                                <path d="M9 14l2 2l4 -4" />
                            </svg>
                        </a>

                        <a href="'.route('transaksi-servis-bisa-diambil.show', $row->id).'" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" stroke="#000" fill="none" viewBox="0 0 24 24">
                                <path d="M9 14l-4 -4l4 -4" /><path d="M5 10h11a4 4 0 1 1 0 8h-1" />
                            </svg>
                        </a>

                        <div x-data="{ deleteOpen: false }">
                            <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="deleteOpen = true" aria-controls="danger-modal">
                                <span class="sr-only">Delete</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <line x1="4" y1="7" x2="20" y2="7" />
                                    <line x1="10" y1="11" x2="10" y2="17" />
                                    <line x1="14" y1="11" x2="14" y2="17" />
                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                </svg>
                            </button>
                            <!-- Modal backdrop -->
                            <div
                                class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                x-show="deleteOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-out duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                aria-hidden="true"
                                x-cloak
                            ></div>
                            <!-- Modal dialog -->
                            <div
                                id="danger-modal"
                                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                role="dialog"
                                aria-modal="true"
                                x-show="deleteOpen"
                                x-transition:enter="transition ease-in-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in-out duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-4"
                                x-cloak
                            >
                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false">
                                    <div class="p-5 flex space-x-4">
                                        <!-- Icon -->
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                            <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                                                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                            </svg>
                                        </div>
                                        <!-- Content -->
                                        <div>
                                            <!-- Modal header -->
                                            <div class="mb-2">
                                                <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                                            </div>
                                            <!-- Modal content -->
                                            <div class="text-sm mb-10">
                                                <div class="space-y-2">
                                                    <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                                </div>
                                            </div>
                                            <!-- Modal footer -->
                                            <div class="flex flex-wrap justify-end space-x-2">
                                                <form action="'.$delurl.'" method="post">
                                                ' . method_field('delete') . csrf_field() . '
                                                    <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
            })
            ->rawColumns(['checkbox','nomor_servis','penerima','pelanggan','hubungi','nama_barang','kerusakan','fungsi','kondisi','tindakan','teknisi','modal_sparepart','biaya','tgl_selesai','aksi','fungsi'])
            ->make(true);
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

        return view('pages.kepalatoko.servis.kembali-proses', [
            'item' => $item,
        ]);
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data servis
        $services = ServiceTransaction::with('brand', 'modelserie', 'user')->where('status_servis', 'Bisa Diambil')
            ->whereDate('tgl_selesai', '>=', $start_date)
            ->whereDate('tgl_selesai', '<=', $end_date)
            ->orderBy('tgl_selesai', 'asc')
            ->get();

        // Menghitung total modal
        $total_modal = ServiceTransaction::where('status_servis', 'Bisa Diambil')
            ->whereDate('tgl_selesai', '>=', $start_date)
            ->whereDate('tgl_selesai', '<=', $end_date)
            ->sum('modal_sparepart');

        // Menghitung total biaya
        $total_biaya = ServiceTransaction::where('status_servis', 'Bisa Diambil')
            ->whereDate('tgl_selesai', '>=', $start_date)
            ->whereDate('tgl_selesai', '<=', $end_date)
            ->sum('biaya');

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-bisa-diambil', [
            'users' => $users,
            'imagePath' => $imagePath,
            'services' => $services,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_modal' => $total_modal,
            'total_biaya' => $total_biaya,
        ]);

        $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = ServiceTransaction::with('user', 'serviceaction', 'product')->findOrFail($id);
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $model_series = ModelSerie::all();
        $service_actions = ServiceAction::all();
        $products = Product::whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->get();
        $capacities = Capacity::all();
        $users = User::where('role', 'Teknisi')->get();
        $workers = Worker::where('jabatan', 'like', '%' . 'teknisi')->get();


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
        return view('pages.kepalatoko.servis.bisa-diambil-edit', [
            'item' => $item,
            'types' => $types,
            'customers' => $customers,
            'brands' => $brands,
            'model_series' => $model_series,
            'service_actions' => $service_actions,
            'products' => $products,
            'capacities' => $capacities,
            'users' => $users,
            'workers' => $workers,
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

        $profittransaksi = $request->biaya - $request->modal_sparepart;
        $bagihasil = $profittransaksi / 100;
        $nama_pelanggan = Customer::find($request->customers_id);


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
        // Transaction update
        $item->update([
            'created_at' => $request->created_at,
            'users_id' => $request->users_id,
            'penerima' => $request->penerima,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'nama_barang' => $nama_barang,
            'kerusakan' => $request->kerusakan,
            'qc_masuk' => $qc_masuk_data,
            'qc_keluar' => $qc_keluar_final,
            'kondisi_servis' => $request->kondisi_servis,
            'service_actions_id' => $request->service_actions_id,
            'products_id' => $request->products_id,
            'tindakan_servis' => $tindakan_servis,
            'modal_sparepart' => $request->modal_sparepart,
            'biaya' => $request->biaya,
            'persen_admin' => $request->persen_admin,
            'persen_teknisi' => $persen_teknisi,
            'omzet' => $request->biaya,
            'profit' => $profittransaksi,
            'profittoko' => $profittransaksi - ($bagihasil *= $persen_teknisi)
        ]);

        return redirect()->route('transaksi-servis-bisa-diambil.index');
    }

    public function back(Request $request, $id)
    {
        $item = ServiceTransaction::findOrFail($id);

        // Transaction update
        $item->update([
            'users_id' => null,
            'kondisi_servis' => null,
            'status_servis' => $request->status_servis,
            'tgl_selesai' => null,
            'service_actions_id' => null,
            'products_id' => null,
            'tindakan_servis' => null,
            'catatan' => null,
            'modal_sparepart' => null,
            'biaya' => null,
            'persen_admin' => null,
            'persen_teknisi' => null,
            'omzet' => null,
            'profit' => null,
            'profittoko' => null
        ]);

        return redirect()->route('transaksi-servis-bisa-diambil.index');
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

        return redirect()->route('transaksi-servis-bisa-diambil.index');
    }
}
