<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Worker;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\StoreSetting;
use App\Models\ModelSerie;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TipeOs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
class TransaksiServisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $processes = ServiceTransaction::with('customer')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orderByDesc('updated_at')->paginate(10);
        $processes_count = ServiceTransaction::whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $bisadiambil = ServiceTransaction::with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->paginate(10);
        $jumlahbisadiambil = ServiceTransaction::with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->count();
        $jumlah_semua = ServiceTransaction::all()->count();
        return view('pages/kepalatoko/servis/transaksi-servis', compact(
            'processes',
            'processes_count',
            'jumlah_bisa_diambil',
            'jumlah_sudah_diambil',
            'jumlah_semua',
            'bisadiambil',
            'jumlahbisadiambil'
        ));
    }


    public function getFoto($id)
    {
        $servis = ServiceTransaction::find($id);

        $response = [
            'masuk' => [],
            'selesai' => []
        ];

        // Helper untuk format file
        $formatFile = function($filename) {
            $path = 'servis/' . $filename; // Sesuaikan path di storage/app/public/servis
            if(Storage::disk('public')->exists($path)){
                $fullUrl = asset('storage/servis/' . $filename);
                return [
                    'source' => $filename,
                    'options' => [
                        'type' => 'local', // Menandakan file ini sudah ada di server
                        'file' => [
                            'name' => $filename,
                            'size' => Storage::disk('public')->size($path),
                            'type' => Storage::disk('public')->mimeType($path),
                        ],
                        'metadata' => [
                            'poster' => $fullUrl, // Untuk preview
                            'url' => $fullUrl     // Untuk zoom/popup
                        ]
                    ]
                ];
            }
            return null;
        };

        // Loop Foto Masuk
        if ($servis->foto_masuk) {
            $files = json_decode($servis->foto_masuk, true) ?? [];
            foreach ($files as $file) {
                if($f = $formatFile($file)) $response['masuk'][] = $f;
            }
        }

        // Loop Foto Selesai
        if ($servis->foto_selesai) {
            $files = json_decode($servis->foto_selesai, true) ?? [];
            foreach ($files as $file) {
                if($f = $formatFile($file)) $response['selesai'][] = $f;
            }
        }

        return response()->json($response);
    }
    // 2. Upload Foto (Dipanggil saat file di-drop)
    public function uploadFoto(Request $request, $id)
    {
        $servis = ServiceTransaction::find($id);
        $type = $request->input('type'); // 'masuk' atau 'selesai'

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Simpan file ke folder storage/app/public/servis
            $file->storeAs('public/servis', $filename);

            // Update Database (Append ke array JSON)
            $column = ($type == 'masuk') ? 'foto_masuk' : 'foto_selesai';
            $currentFiles = json_decode($servis->$column, true) ?? [];
            $currentFiles[] = $filename;

            $servis->$column = json_encode($currentFiles);
            $servis->save();

            // Return filename agar FilePond tahu ID file ini
            return response($filename, 200);
        }

        return response()->json(['error' => 'No file'], 400);
    }

    // 3. Delete Foto (Dipanggil saat tombol silang diklik di FilePond)
    public function deleteFoto(Request $request, $id)
    {
        $servis = ServiceTransaction::find($id);
        $filename = $request->getContent(); // FilePond mengirim nama file di body
        $type = $request->input('type'); // dikirim via query string

        $column = ($type == 'masuk') ? 'foto_masuk' : 'foto_selesai';
        $currentFiles = json_decode($servis->$column, true) ?? [];

        // Cari dan hapus dari array
        if (($key = array_search($filename, $currentFiles)) !== false) {
            unset($currentFiles[$key]);

            // Hapus file fisik dari storage
            if (Storage::disk('public')->exists('servis/' . $filename)) {
                Storage::disk('public')->delete('servis/' . $filename);
            }
        }

        // Simpan array baru ke DB
        $servis->$column = json_encode(array_values($currentFiles));
        $servis->save();

        return response()->json(['success' => true]);
    }


    public function getData(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);
        $query = ServiceTransaction::where('cabang_id',getCabangId())->with(['customer'])
        ->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])
        ->orderByDesc('updated_at')
        ->latest()
        ->skip($offset)
        ->take($limit)
        ->get();

        return DataTables::of($query)
        ->addIndexColumn()

        // Checkbox untuk bulk action
        ->addColumn('checkbox', function ($row) {
            // hanya tampilkan checkbox bila bukan investor (cek auth di blade juga)
            return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
        })

        // Nomor Servis (link edit kalau bukan investor)
        ->addColumn('nomor_servis', function ($row) {
            if (auth()->user()->role != 'Investor') {
                $tanggalTransaksi = \Carbon\Carbon::parse($row->created_at);
                $hariIni = \Carbon\Carbon::today();
                $tokoSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
                if ((int) ($tokoSetting->is_edit_transaksi ?? 0) == 1 || $tanggalTransaksi->isSameDay($hariIni) || Auth::user()->role == 'Kepala Toko'){
                    $link = route('transaksi-servis.edit', $row->id);
                    return '<a href="' . $link . '">
                                <div class="flex items-center text-blue-600">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                        <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z"/>
                                    </svg>
                                    <div class="font-medium">' . e($row->nomor_servis) . '</div>
                                </div>
                            </a>';
                }
            }
            return '<div class="font-medium">' . e($row->nomor_servis) . '</div>';
        })

        // Tanggal terima (created_at formatted)
        ->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->format('d/m/Y');
        })

        // Penerima
        ->addColumn('penerima', function ($row) {
            return e($row->penerima);
        })

        // Pelanggan dengan fallback bila dihapus
        ->addColumn('pelanggan', function ($row) {
            if ($row->customer) {
                // jika model customer masih ada
                return '<div class="font-medium">' . e($row->customer->nama) . '</div>';
            }
            return '<div class="font-medium text-rose-600">Data pelanggan telah dihapus</div>';
        })

        // Hubungi (WA tombol + kirim Fontee)
        ->addColumn('hubungi', function ($row) {
            $nomor = $row->customer->nomor_hp ?? null;
            $nomorwa = $nomor ? preg_replace('/^08/', '628', preg_replace('/\D+/', '', $nomor)) : null;
            $fonteeToken = StoreSetting::where('cabang_id',getCabangId())->first()->fonnte ?? null;
            $toko = optional($row->customer)->nama ?? config('app.name');

            if ($row->cabang_id == 1) {
                $kp = User::find(1);
            } else {
                $kp = User::where('cabang_id', $row->cabang_id)->where('id', '!=', 1)->where('role', 'Kepala Toko')->orderBy('id', 'asc')->first();
            }

            $html = '<div class="flex space-x-1">';
            if ($nomorwa) {
                $html .= '
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <a href="https://api.whatsapp.com/send?phone=' . $nomorwa . '&text=" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                <path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" />
                            </svg>
                        </a>
                    </div>';
            }
            // Kirim Fontee / manual WA button — gunakan JS function kirimFontee(...) di blade
            $tokoName = e(config('app.name'));
            $notaLink = route('kepalatoko-cetak-inkjet', $row->id);
            $notaQc = route('kepalatoko-cetak-qc', $row->id);
            $trackingLink = env('APP_URL') . '/tracking';
            // $message = rawurlencode("*Notifikasi Service*\n" . $tokoName . "\n\n" .
            //     "No. Service : " . $row->nomor_servis . "\n" .
            //     "Nama user : *" . $row->nama_pelanggan . "*\n" .
            //     "Unit : " . $row->nama_barang . "\n" .
            //     "Diterima : " . $row->penerima . "\n" .
            //     "Tanggal : " . Carbon::parse($row->created_at)->translatedFormat('d F Y h:i') . "\n" .
            //     "Kerusakan : " . $row->kerusakan . "\n\n" .
            //     "Link tracking : " . $trackingLink . "\n" .
            //     "Link nota : " . $notaLink . "\n" .
            //     "Link QC : " . $notaQc . "\n\n" .
            //     "Terimakasih");

            $banks = old('banks', json_decode($kp->banks ?? '[]', true));

            // 1. Definisikan bagian awal pesan
            $text = "*Notifikasi Service*\n" . $tokoName . "\n\n" .
                    "No. Service : " . $row->nomor_servis . "\n" .
                    "Nama user : *" . $row->nama_pelanggan . "*\n" .
                    "Unit : " . $row->nama_barang . "\n" .
                    "Diterima : " . $row->penerima . "\n" .
                    "Tanggal : " . Carbon::parse($row->created_at)->translatedFormat('d F Y h:i') . "\n" .
                    "Kerusakan : " . $row->kerusakan . "\n\n" .
                    "Link tracking : " . $trackingLink . "\n" .
                    "Link nota : " . $notaLink . "\n" .
                    "Link QC : " . $notaQc . "\n\n";


            $text .= 'Informasi Pembayaran :' ."\n";

            $text .= 'Bank : '. $kp->bank . "\n";
            $text .= 'Norek : '. $kp->rekening . "\n";
            $text .= 'a.n : '. $kp->pemilik_rekening . "\n";

            if (!empty($banks)) {
                foreach ($banks as $bank) {
                    $text .= 'Bank : '. $bank['bank'] . "\n";
                    $text .= 'Norek : '. $bank['rekening'] . "\n";
                    $text .= 'a.n : '. $bank['pemilik'] . "\n";
                }
            }

            $text .= "Terimakasih";

            $message = rawurlencode($text);

            if ($fonteeToken && $nomorwa) {
                // call kirimFontee JS with token & number & message
                $html .= '<div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <a href="javascript:void(0)" onclick="kirimFontee(\'' . e($fonteeToken) . '\', \'' . $nomorwa . '\', \'' . $message . '\')">
                                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#00abfb" fill="none" stroke-width="1.5">
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                    <line x1="9" y1="7" x2="10" y2="7"/>
                                    <line x1="9" y1="13" x2="15" y2="13"/>
                                    <line x1="13" y1="17" x2="15" y2="17"/>
                                </svg>
                            </a>
                        </div>';
            } elseif ($nomorwa) {
                $html .= '<div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <a href="https://wa.me/' . $nomorwa . '/?text=' . $message . '" target="_blank">
                                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" stroke="#00abfb" fill="none" stroke-width="1.5">
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                    <line x1="9" y1="7" x2="10" y2="7"/>
                                    <line x1="9" y1="13" x2="15" y2="13"/>
                                    <line x1="13" y1="17" x2="15" y2="17"/>
                                </svg>
                            </a>
                        </div>';
            }

            $html .= '</div>';
            return $html;
        })

        // Nama Barang
        ->addColumn('nama_barang', function ($row) {
            return '<div class="font-medium">' . e($row->nama_barang) . '</div>';
        })

        // Kelengkapan
        ->addColumn('kelengkapan', function ($row) {
            $k = $row->kelengkapan;
            return '<div class="font-medium">' . ($k ? e($k) : 'Hanya Barang') . '</div>';
        })

        // Kerusakan
        ->addColumn('kerusakan', function ($row) {
            return '<div class="font-medium capitalize">' . e($row->kerusakan) . '</div>';
        })

        // Fungsi (qc_masuk)
        // ->addColumn('qc_masuk', function ($row) {
        //     return '<div class="font-medium capitalize">' . e($row->qc_masuk) . '</div>';
        // })
        ->addColumn('qc_masuk', function ($row) {
            $url = route('kepalatoko-cetak-qc', $row->id);

            return '
                <a href="' . $url . '" target="_blank" class="btn bg-indigo-500 hover:bg-indigo-600 text-white " title="Lihat PDF QC">
                    Lihat QC
                </a>
            ';
        })

        // Uang muka
        ->addColumn('uang_muka', function ($row) {
            return '<div class="font-medium">Rp. ' . number_format($row->uang_muka) . '</div>';
        })

        // Estimasi biaya
        ->addColumn('estimasi_biaya', function ($row) {
            return '<div class="font-medium">Rp. ' . number_format($row->estimasi_biaya) . '</div>';
        })

        // Estimasi pengerjaan
        ->addColumn('estimasi_pengerjaan', function ($row) {
            return '<div class="font-medium">' . e($row->estimasi_pengerjaan) . '</div>';
        })

        // Status (warna dinamis -- sama seperti logic di blade lama)
        ->addColumn('status', function ($row) {
            $s = $row->status_servis;
            if ($s === 'Sedang Dikerjakan') {
                $status_color = 'bg-emerald-100 text-emerald-600';
            } elseif ($s === 'Menunggu Sparepart') {
                $status_color = 'bg-amber-100 text-amber-600';
            } elseif ($s === 'Menunggu Konfirmasi') {
                $status_color = 'bg-rose-100 text-rose-500';
            } elseif ($s === 'Sedang Tes') {
                $status_color = 'bg-blue-100 text-blue-600';
            } else {
                $status_color = 'bg-slate-100 text-slate-500';
            }

            $link = route('ubah-status-proses-edit', $row->id);

            return '<a href="' . $link . '"><div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 ' . $status_color . '">' . e($s) . '</div></a>';
        })

        // Aksi lengkap (PIN modal, printer modal, delete modal, konfirmasi link, dll)
        ->addColumn('aksi', function ($row) {
            $id = $row->id;
            $pinModalId = "pin-modal-{$id}";
            $dangerModalId = "danger-modal-{$id}";
            $printTermal = route('kepalatoko-cetak-termal', $id);
            $printInkjet = route('kepalatoko-cetak-inkjet', $id);
            $ubahBisaAmbil = route('ubah-bisa-diambil-edit', $id);
            $deleteRoute = route('transaksi-servis.destroy', $id);

            // Build aksi HTML mirip persis dengan blade kamu
            $html = '<div class="space-x-1 flex">';

            $urlMultiTeknisi = route('multi-teknisi', $row->id);


            // PIN & Pola (menyertakan wire:click dari blade asli)
            $html .= '
                <button type="button"
                        class="text-indigo-500 hover:text-indigo-600 rounded-full btn-upload-foto ml-1"
                        data-id="' . $id . '"
                        title="Upload Foto Servis">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-camera" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"></path>
                    <path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                    </svg>
                </button>
            ';
            $html .= '
                <div>
                    <button onclick="openPinModal(' . $id . ')" class="text-indigo-500 hover:text-indigo-600 rounded-full">
                        <span class="sr-only">Service PIN & Pola</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6366f1" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <rect x="5" y="11" width="14" height="10" rx="2" />
                            <path d="M8 11v-4a4 4 0 0 1 8 0v4" />
                        </svg>
                    </button>
                </div>
            ';

            // PIN modal (Alpine) - gunakan id unik jika perlu
            $html .= '
            <div x-data="{ open: false }"
                x-show="open"
                @open-pin-modal-' . $id . '.window="open = true"
                @close-pin-modal-' . $id . '.window="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                x-cloak
                @click.self="open = false">

                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <div class="flex justify-between items-center border-b pb-2 mb-4">
                        <h2 class="text-lg font-semibold text-gray-700">Service PIN & Pola</h2>
                        <button @click="open=false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">PIN</label> <br>
                            <input type="number" value="' . e($row->pin) . '" id="pinInput-' . $id . '" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200">
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-600">Pola</label>
                            <canvas id="sig-canvas-' . $id . '" class="sig-canvas border rounded w-full h-48 bg-gray-100"></canvas>
                            <input type="hidden" id="polaInput-' . $id . '" class="polaInput">
                            <small class="text-gray-400">Gambar pola (opsional)</small>
                        </div>

                        <button type="button" onclick="resetCanvas(' . $id . ')" class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">Reset Pola</button>
                    </div>

                    <div class="mt-6 flex justify-end space-x-2">
                        <button onclick="saveCanvasAjax(' . $id . ')" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">Simpan</button>
                    </div>

                </div>
            </div>
            ';


                // Konfirmasi -> ubah status jadi Bisa Diambil
                $html .= '
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <a href="' . $urlMultiTeknisi . '">
                            <button class="text-slate-400 hover:text-slate-500 rounded-full" title="Ubah menjadi Bisa Diambil">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                    <rect x="9" y="3" width="6" height="4" rx="2" />
                                    <path d="M9 14l2 2l4 -4" />
                                </svg>
                            </button>
                        </a>
                    </div>
                ';

            // Printer modal
            $html .= '
            <div x-data="{ modalOpen: false }">
                <button @click.prevent="modalOpen = true" aria-controls="basic-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>

                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-cloak></div>

                <div id="basic-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false"><div class="sr-only">Close</div><svg class="w-4 h-4 fill-current"><path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" /></svg></button>
                            </div>
                        </div>

                        <div class="px-5 pt-4 pb-1">
                            <div class="text-sm">
                                <div class="space-y-2">
                                    <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-4">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                <a href="' . $printTermal . '" target="_blank"><button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white">Printer Termal</button></a>
                                <a href="' . $printInkjet . '" target="_blank"><button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Printer Inkjet</button></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ';

                // Delete modal
                $html .= '
                <div x-data="{ deleteOpen: false }">
                    <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="deleteOpen = true" aria-controls="danger-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <line x1="4" y1="7" x2="20" y2="7" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </button>

                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="deleteOpen" x-cloak></div>

                    <div id="danger-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="deleteOpen" x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false">
                            <div class="p-5 flex space-x-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                    <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z"/></svg>
                                </div>

                                <div>
                                    <div class="mb-2"><div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div></div>
                                    <div class="text-sm mb-10"><div class="space-y-2"><p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p></div></div>
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <form action="' . $deleteRoute . '" method="post">
                                            ' . method_field('delete') . csrf_field() . '
                                            <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ';

            $html .= '</div>';
            return $html;
        })

        ->rawColumns([
            'checkbox','nomor_servis','pelanggan','hubungi','nama_barang','kelengkapan','kerusakan','qc_masuk',
            'uang_muka','estimasi_biaya','estimasi_pengerjaan','status','aksi'
        ])
        ->make(true);
    }

    // Fetch current data for the modal
    public function getPinPola($id)
    {
        $service = ServiceTransaction::find($id);

        if (!$service) {
            return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $service->id,
                'pin' => $service->pin,
                'pola' => $service->pola // This is the JSON string of the canvas drawing
            ]
        ]);
    }

    // Save the submitted data
    public function updatePinPolaNew(Request $request, $id)
    {
        $service = ServiceTransaction::find($id);

        if (!$service) {
            return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
        }

        $service->pin = $request->pin;
        $service->pola = $request->pola; // Save canvas JSON data
        $service->save();

        return response()->json(['status' => 'success', 'message' => 'PIN & Pola berhasil disimpan']);
    }



    public function updatePinPola(Request $request, $id)
    {
        $request->validate([
            'pin' => 'nullable|string|max:10',
            'pola' => 'nullable|string',
        ]);

        $service = ServiceTransaction::findOrFail($id);
        $service->pin = $request->pin;
        $service->pola = $request->pola;
        $service->save();

        return response()->json([
            'success' => true,
            'message' => 'PIN & Pola berhasil disimpan.'
        ]);
    }


    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        ServiceTransaction::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data transaksi servis berhasil dihapus.']);
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
        $qc_data = $request->qc_masuk ?? [];

        // Gabungkan dengan baris Custom (jika ada input manual)
        if ($request->has('custom_item_name')) {
            foreach ($request->custom_item_name as $key => $name) {
                // Hanya proses jika nama item tidak kosong
                if (!empty($name)) {
                    // Ambil value statusnya (OK/Rusak/dll), default '-' jika kosong
                    $val = $request->custom_qc_masuk[$key] ?? '-';

                    // Masukkan ke array utama
                    $qc_data[$name] = $val;
                }
            }
        }

        // Ubah array menjadi JSON agar bisa disimpan di database text/longtext
        $qc_masuk_final = json_encode($qc_data);
        // dd($qc_masuk_final);

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

        // dd($request->all(),$request->customers_id,insertManualPelanggan($request->customers_manual));

        $nomor_servis = '' . mt_rand(date('Ymd00'), date('Ymd99'));
        $nama_pelanggan = Customer::find($request->customers_id);
        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

        // Transaction create
        $transaksi = ServiceTransaction::create([
            'admin_id' => Auth::user()->id,
            'is_admin_toko' => Auth::user()->role == 'Admin Toko' ? 'Admin' : null,
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
            // 'qc_masuk' => $request->qc_masuk,
            'qc_masuk' => $qc_masuk_final,
            'estimasi_pengerjaan' => $request->estimasi_pengerjaan,
            'estimasi_biaya' => $request->estimasi_biaya,
            'uang_muka' => $request->uang_muka,
            'status_servis' => $request->status_servis,
            'penerima' => $request->penerima,
            'cabang_id' => getCabangId(),
        ]);

        try {
            $tglMasuk = $transaksi->created_at
                ? Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('d F Y')
                : '-';

            $pesan = "📦 *TRANSAKSI BARU*\n\n"
                . "🧾 *PROSES DITINGGAL*\n"
                . "🧾 *Status Servis:* {$transaksi->status_servis}\n"
                . "🧾 *Nomor Servis:* {$transaksi->nomor_servis}\n"
                . "👤 *Pelanggan:* {$nama_pelanggan->nama}\n"
                . "📱 *Barang:* {$nama_barang}\n"
                // . "⚙️ *Tindakan Servis:*\n{$tindakanText}\n\n"
                . "🧾 *Estimasi Pengerjaan:* {$transaksi->estimasi_pengerjaan}\n"
                . "💰 *Estimasi Biaya:* Rp " . number_format($transaksi->estimasi_biaya, 0, ',', '.') . "\n"
                . "💰 *Uang Muka:* Rp " . number_format($transaksi->uang_muka, 0, ',', '.') . "\n\n"
                . "📅 *Tanggal Masuk:* {$tglMasuk}\n"
                . "🧍‍♂️ *penerima:* " . Auth::user()->name;

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
        $services = ServiceTransaction::with('brand', 'modelserie', 'user')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->orderBy('created_at', 'asc')
            ->get();

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-proses', [
            'users' => $users,
            'imagePath' => $imagePath,
            'services' => $services,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }

    public function cetaktermal($id)
    {
        $items = ServiceTransaction::with('customer')->findOrFail($id);
        // $users = User::find(1);
        if ($items->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$items->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini. lalu setting kop di pengaturan toko melalui akun kepala toko', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;
        $toko = StoreSetting::where('cabang_id', getCabangId())->first();
        $pdf = PDF::loadView('pages.kepalatoko.servis.notaterima-cetak-termal', [
        // return view('pages.kepalatoko.servis.notaterima-cetak-termal', [
            'toko' => $toko,
            'users' => $users,
            'items' => $items,
            'imagePath' => $imagePath,
        ]);

        $filename = 'Nota Terima ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    private function convertJsonSignatureToBase64($jsonPola)
    {
        // 1. Decode JSON
        $strokes = json_decode($jsonPola, true);

        if (empty($strokes)) {
            return null;
        }

        // 2. Buat Canvas Image (Ukuran sesuaikan dengan canvas signature pad, misal 500x300)
        // Gunakan ukuran yang cukup besar agar tidak terpotong
        $width = 500;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);

        // 3. Set Background Transparan
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        // 4. Set Warna Garis (Hitam)
        $black = imagecolorallocate($image, 0, 0, 0);

        // Set ketebalan garis
        imagesetthickness($image, 3);

        // 5. Loop Koordinat dan Gambar Garis
        foreach ($strokes as $stroke) {
            $points = $stroke['points'];
            $count = count($points);

            // Perlu minimal 2 titik untuk membuat garis
            for ($i = 0; $i < $count - 1; $i++) {
                imageline(
                    $image,
                    $points[$i]['x'],
                    $points[$i]['y'],
                    $points[$i + 1]['x'],
                    $points[$i + 1]['y'],
                    $black
                );
            }
        }

        // 6. Output ke Base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    public function cetakinkjet($id)
    {
        $items = ServiceTransaction::with('customer')->findOrFail($id);
        // $users = User::find(1);
        if ($items->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$items->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini. lalu setting kop di pengaturan toko melalui akun kepala toko', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }
        $terms = Term::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;
        $toko = StoreSetting::where('cabang_id', getCabangId())->first();

        // --- BAGIAN BARU: KONVERSI POLA ---
        // Cek apakah pola ada isinya dan berupa JSON (bukan URL gambar lama)
        $polaImage = null;

        if (!empty($items->pola)) {
            // Cek sederhana apakah ini JSON koordinat atau sudah base64/url
            // Kalau JSON biasanya diawali kurung siku '['
            if (substr(trim($items->pola), 0, 1) === '[') {
                // Konversi JSON ke Gambar Base64
                $polaImage = $this->convertJsonSignatureToBase64($items->pola);
            } else {
                // Jika data lama (sudah berupa URL/Base64), pakai langsung
                $polaImage = $items->pola;
            }
        }

        // Jika hasil konversi null atau data kosong, pakai gambar default
        if (empty($polaImage)) {
            $polaImage = public_path('images/pola.png');
        }
        // ----------------------------------
        // dd($items,$polaImage);
        $pdf = PDF::loadView('pages.kepalatoko.servis.notaterima-cetak-inkjet', [
        // return view('pages.kepalatoko.servis.notaterima-cetak-inkjet', [
            'toko' => $toko,
            'users' => $users,
            'items' => $items,
            'terms' => $terms,
            'imagePath' => $imagePath,
            'polaImage' => $polaImage,
        ]);

        $filename = 'Nota Terima ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    public function cetakQc($id)
    {
        $items = ServiceTransaction::with(['customer','admin'])->findOrFail($id);
        // dd($items);
        // 1. Decode JSON ke Array
        $qcMasuk = $items->qc_masuk ? json_decode($items->qc_masuk, true) : [];
        $qcKeluar = $items->qc_keluar ? json_decode($items->qc_keluar, true) : [];

        $qcItems = $qcMasuk != null ? array_keys($qcMasuk) : [];

        if (empty($qcItems) && !empty($qcKeluar)) {
            $qcItems = array_keys($qcKeluar);
        }

        if (empty($qcItems)) {
            $qcItems = [];
        }

        if ($items->cabang_id == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', $items->cabang_id)->where('id', '!=', 1)->where('role', 'Kepala Toko')->orderBy('id', 'asc')->first();
        }

        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu...', 'error');
            return redirect()->back();
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.notaqc-cetak', [
        // return View('pages.kepalatoko.servis.notaqc-cetak', [
            'users' => $users,
            'items' => $items,
            'imagePath' => $imagePath,
            'qcMasuk' => $qcMasuk,   // Data Status Masuk (OK/Rusak/Null)
            'qcKeluar' => $qcKeluar, // Data Status Keluar
            'qcItems' => $qcItems    // Daftar Nama Item (Dinamis dari DB)
        ]);

        $filename = 'QC Check ' . $invoiceNumber . ' - ' . $namaPelanggan . '.pdf';

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
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $types = Type::where('cabang_id',getCabangId())->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $service_actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $users = User::where('cabang_id',getCabangId())->where('role', 'Teknisi')->get();
        $workers = Worker::where('cabang_id',getCabangId())->whereIn('id', $users->pluck('workers_id'))->get();

        return view('pages.kepalatoko.servis.transaksi-servis-edit', [
            'item' => $item,
            'types' => $types,
            'customers' => $customers,
            'brands' => $brands,
            'model_series' => $model_series,
            'service_actions' => $service_actions,
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

        $request->merge([
            'estimasi_biaya' => str_replace('.', '', $request->estimasi_biaya),
            'uang_muka' => str_replace('.', '', $request->uang_muka),
        ]);
        $item = ServiceTransaction::findOrFail($id);
        $nama_pelanggan = Customer::find($request->customers_id);
        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

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
            'qc_masuk' => $qc_masuk_final,
            'qc_keluar' => $qc_keluar_final,
            'estimasi_pengerjaan' => $request->estimasi_pengerjaan,
            'estimasi_biaya' => $request->estimasi_biaya,
            'uang_muka' => $request->uang_muka,
            'penerima' => $request->penerima
        ]);

        return redirect()->route('transaksi-servis.index');
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

        return redirect()->route('transaksi-servis.index');
    }
}
