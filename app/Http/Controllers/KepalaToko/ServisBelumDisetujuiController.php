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
use App\Models\TeknisiServis;
use App\Models\ServiceTransaction;
use App\Models\MetodePembayaran;
use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

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

    public function getData(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $query = ServiceTransaction::with(['customer', 'user'])
            ->where('status_servis', 'Sudah Diambil')->where('is_approve', null)
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
                $url = route('transaksi-servis-belum-disetujui.edit', $row->id);
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
                    $toko = StoreSetting::where('cabang_id',$row->cabang_id)->first();

                    $nomor = $row->customer->nomor_hp;
                    $nomorwa = preg_replace('/^08/', '628', $nomor);
                    $fonnteToken = $toko->fonnte ?? null;
                    $hasToken = !empty($fonnteToken);

                    if ($row->cabang_id == 1) {
                        $kp = User::find(1);
                    } else {
                        $kp = User::where('cabang_id', $row->cabang_id)->where('id', '!=', 1)->where('role', 'Kepala Toko')->orderBy('id', 'asc')->first();
                    }
                    $banks = old('banks', json_decode($kp->banks ?? '[]', true));

                    $metodePembayaranPaymentGateway = MetodePembayaran::where('is_payment_gateway',1)->first();
                    if (!empty($metodePembayaranPaymentGateway)) {
                        if($metodePembayaranPaymentGateway->nama == $row->cara_pembayaran){
                            $is_pg = true;
                        }else{
                            $is_pg = false;
                        }
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
                    ? route('servis-belum-disetujui-approve.edit', $row->id)
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
                $destroyBase = route('transaksi-servis-belum-disetujui.destroy', '');
                $termalBase  = route('kepalatoko-nota-pengambilan-termal', '');
                $inkjetBase  = route('kepalatoko-pengambilan-cetak-inkjet', '');

                return '
                <div class="space-x-1 flex">
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

                    <button x-data x-on:click="$dispatch(\'open-delete\', { id: '.$row->id.' })"
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


            ->rawColumns(['checkbox', 'nomor_servis', 'hubungi', 'kondisi_servis', 'status', 'aksi', 'exp_garansi','fungsi'])
            ->make(true);
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
        $customers = Customer::where('cabang_id',getCabangId())->get();
        $types = Type::where('cabang_id',getCabangId())->get();
        $brands = Brand::where('cabang_id',getCabangId())->get();
        $model_series = ModelSerie::where('cabang_id',getCabangId())->get();
        $service_actions = ServiceAction::where('cabang_id',getCabangId())->get();
        $capacities = Capacity::where('cabang_id',getCabangId())->get();
        $users = User::where('role', 'Teknisi')->where('cabang_id',getCabangId())->get();
        $workers = Worker::whereIn('id', $users->pluck('workers_id'))->where('cabang_id',getCabangId())->get();
        $products = Product::whereHas('subCategory', function ($query) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('category_name', 'Sparepart');
            });
        })->where('stok', '>=', 1)->where('cabang_id',getCabangId())->get();
        $sales = User::where('role', 'Sales')->where('cabang_id',getCabangId())->get();

           // 1. Decode JSON ke Array
        $qcMasuk = $item->qc_masuk ? json_decode($item->qc_masuk, true) : [];
        $qcKeluar = $item->qc_keluar ? json_decode($item->qc_keluar, true) : [];
        // dd($qcMasuk,$items->qc_masuk);
        if ($qcMasuk != null) {
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

        $teknisiServis = TeknisiServis::where('service_transactions_id', $id)->get();

        if ($teknisiServis->isEmpty()) {
            $dummyTeknisi = new TeknisiServis();

            $dummyTeknisi->service_transactions_id = $item->id;
            $dummyTeknisi->users_id = $item->users_id; // Teknisi utama
            $dummyTeknisi->tipe = $item->tipe; // Hardware/Software
            $dummyTeknisi->modal_sparepart = $item->modal_sparepart;
            $dummyTeknisi->biaya = $item->biaya;
            $dummyTeknisi->profit = $item->profit;
            $dummyTeknisi->profittoko = $item->profittoko;
            $dummyTeknisi->bonus_interface = $item->bonus_interface;
            $dummyTeknisi->persen_teknisi = $item->persen_teknisi;
            $dummyTeknisi->tindakan_servis = $item->tindakan_servis;
            $dummyTeknisi->garansi = $item->exp_garansi_j; // Sesuaikan nama kolom
            $dummyTeknisi->service_actions = $item->service_actions;
            $dummyTeknisi->products = $item->products;
            $dummyTeknisi->biaya_j = $item->biaya_j;
            $dummyTeknisi->modal_j = $item->modal_j;

            $teknisiServis->push($dummyTeknisi);
        }
        // dd($teknisiServis,$item);

        return view('pages.kepalatoko.servis.belum-disetujui-edit', [
            'sales' => $sales,
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
            'qcItems' => $qcItems,
            'qcMasuk' => $qcMasuk,
            'qcKeluar' => $qcKeluar,
            'teknisiServis' => $teknisiServis,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function updateOld(Request $request, $id)
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
                elseif (!empty($request->tindakan_servis_manual[$key])) {
                    $tindakan = $request->tindakan_servis_manual[$key];
                }

                // 5. Tambahkan ke array jika ada tindakan yang valid
                if ($tindakan !== null) {
                    array_push($tindakan_servis, $tindakan);
                }
            }
        }

        $profittransaksi = $request->biaya - $request->modal_sparepart - $request->diskon;
        $bagihasil = ($request->biaya - $request->modal_sparepart - $request->diskon) / 100;
        $nama_pelanggan = Customer::find($request->customers_id);


        $ppn = 0;
        $cekppn = StoreSetting::where('cabang_id',getCabangId())->first();
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

        $transfer = $biayaFinal;
        $tunai = 0;
        $due = 0;
        $pay = $biayaFinal;

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
            'service_actions' => json_encode($request->service_actions_id),
            'products' => json_encode($request->products_id),
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
        // dd($request->all(),$item);

        return redirect()->route('transaksi-servis-belum-disetujui.index');
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());
       $itemOrigin = ServiceTransaction::findOrFail($id);
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
                                        // $this->createSparepartOrder($itemOrigin->customers_id, $sales_id, $sparepart);
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
                            $nama_model = ModelSerie::find($itemOrigin->model_series_id);
                            $bonus_interface = $nama_model->nominal_bonus ?? 0;
                        }elseif ($cekTeknisi && $cekTeknisi->bagian_teknisi == 'Teknisi Interface' && $tipeTeknisi == 'Interface Leveling') {
                            $nama_model = ModelSerie::find($itemOrigin->model_series_id);

                            $biayaPerAction = (int)filter_var($action['biaya_servis'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                            $bonus_leveling = \App\Models\BonusLeveling::where('id_user',$cekTeknisi->id)
                            ->where('id_jenis_barang',$request->types_id)
                            ->where('id_tipe_os',$nama_model->id_tipe_os)
                            ->where('start_rate','<=',(int)$biayaPerAction)
                            ->where('end_rate','>=',(int)$biayaPerAction)
                            ->first();
                            if (!empty($bonus_leveling)) {
                                $bonus_interface = $bonus_leveling->nominal_bonus ?? 0;
                            }else{
                                $nama_model = ModelSerie::find($request->model_series_id);
                                $bonus_interface = $nama_model->nominal_bonus ?? 0;
                            }
                        }
                    }

                    if ($request->kondisi_servis !== 'Dibatalkan' && $userId) {
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
                            'modal_j' => json_encode($arr_modal_sparepart),
                            'garansi' => !empty($list_garansi) ? json_encode($list_garansi) : null,
                        ]);
                    }


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
                            'modal_j' => json_encode($arr_modal_sparepart),
                            'garansi' => !empty($garansi_data) ? $garansi_data[0] ?? null : null,
                            'exp_garansi' => !empty($list_garansi) ? $list_garansi[0] ?? null : null,
                            'exp_garansi_j' => !empty($list_garansi) ? json_encode($list_garansi) : null,
                            'bagian_teknisi' => $cekTeknisi->bagian_teknisi,
                            'tipe_teknisi' => $tipeTeknisi,
                        ];
                    }

                } // End Foreach

                $nama_tipe = Type::find($request->types_id);
                $nama_merek = Brand::find($request->brands_id);
                $nama_model = ModelSerie::find($request->model_series_id);
                $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;
                $nama_pelanggan = Customer::find($request->customers_id);


                $ppn = 0;
                $cekppn = StoreSetting::where('cabang_id',getCabangId())->first();
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

                $grandProfit = $grandTotalBiaya - $grandTotalModal;

                $itemOrigin->update([
                    'created_at' => $request->created_at,
                    'tgl_disetujui' => $request->tgl_disetujui,
                    'kondisi_servis' => $request->kondisi_servis,
                    'tgl_selesai' => $request->tgl_selesai,
                    'penerima' => $request->penerima,
                    'qc_masuk' => $qc_masuk_final,
                    'qc_keluar' => $qc_keluar_final,
                    'customers_id' => $request->customers_id,
                    'nama_pelanggan' => $nama_pelanggan->nama,
                    'types_id' => $request->types_id,
                    'brands_id' => $request->brands_id,
                    'model_series_id' => $request->model_series_id,
                    'kerusakan' => $request->kerusakan,
                    'qc_masuk' => $qc_masuk_data,
                    'qc_keluar' => $qc_keluar_data,
                    'uang_muka' => $request->uang_muka,
                    'diskon' => $request->diskon,
                    'cara_pembayaran' => $request->cara_pembayaran,
                    'tipe' => $mainTechDetails['tipe_teknisi'],
                    'garansi' => $mainTechDetails['garansi'],
                    'exp_garansi' => $mainTechDetails['exp_garansi'],
                    'exp_garansi_j' => json_encode($mainTechDetails['exp_garansi_j']),
                    'tgl_ambil' => $request->tgl_ambil,
                    'pengambil' => $request->pengambil,
                    'pay' => $pay,
                    'due' => $due,
                    'tempo' => $tempo,
                    'tunai' => $tunai,
                    'transfer' => $transfer,
                    'ppn' => $ppn ?? 0,
                    'users_id' => $mainTechnicianId, // Penanggung Jawab Utama
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
            toast('Data servis berhasil disimpan.', 'success');

            return redirect()->route('transaksi-servis-belum-disetujui.index')->with('success', 'Data servis berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
            \Log::error("Error Multi Teknisi: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
        $item = ServiceTransaction::findOrFail($id);

        $item->delete();

        return redirect()->route('transaksi-servis-belum-disetujui.index');
    }
}
