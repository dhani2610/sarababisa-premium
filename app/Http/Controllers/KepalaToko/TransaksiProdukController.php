<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Term;
use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;

class TransaksiProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jumlah_semua = Order::where('cabang_id',getCabangId())->get()->count();
        $jumlah_lunas = Order::where('cabang_id',getCabangId())->where('due', '0')->count();
        $jumlah_tidaklunas = Order::where('cabang_id',getCabangId())->where('due', '>', '0')->count();
        $storeSettings = StoreSetting::where('cabang_id', getCabangId())->first();
        return view('pages/kepalatoko/produk/transaksi', compact(
            'jumlah_semua',
            'jumlah_lunas',
            'jumlah_tidaklunas',
            'storeSettings',
        ));
    }

    public function data(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $orders = Order::select('orders.*', 'order_details.modal')
            ->join('order_details', 'orders.id', '=', 'order_details.orders_id')
            ->with(['user', 'customer'])
            ->where('orders.cabang_id',getCabangId())
            ->orderByRaw('is_approve IS NULL DESC')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get();

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('checkbox', fn($row) => '
                <div class="flex items-center">
                    <label class="inline-flex">
                        <span class="sr-only">Select</span>
                        <input class="table-item form-checkbox" type="checkbox" value="'.$row->id.'" @click="uncheckParent" />
                    </label>
                </div>
            ')
            ->addColumn('invoice_no', function ($row) {
                $link = route('transaksi-produk.edit', $row->id);
                if (auth()->user()->role != 'Investor') {
                    // return '<a href="'.$link.'" class="text-blue-600">'.$row->invoice_no.'</a>';
                    return '
                        <div class="flex items-center text-blue-600">
                            <a href="'.$link.'" class="text-blue-600">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                                <div class="font-medium">'.$row->invoice_no.'</div>
                            </a>
                        </div>';
                }else{
                     return '
                        <div class="flex items-center text-blue-600">
                            <a href="#" class="text-blue-600">
                                <div class="font-medium">'.$row->invoice_no.'</div>
                            </a>
                        </div>';
                }
                return $row->invoice_no;
            })
            ->addColumn('tgl_transaksi', fn($row) => \Carbon\Carbon::parse($row->created_at)->locale('id')->translatedFormat('d F Y'))
            ->addColumn('sales', fn($row) => $row->user ? $row->user->name : '<span class="text-red-500">Akun dihapus</span>')
            ->addColumn('pelanggan', fn($row) => $row->customer ? $row->customer->nama : '-')
            ->addColumn('pembayaran', fn($row) => $row->payment_method)
            ->addColumn('modal', fn($row) => 'Rp. '.number_format($row->modal))
            ->addColumn('sub_total', fn($row) => 'Rp. '.number_format($row->sub_total))
            ->addColumn('pay', fn($row) => 'Rp. '.number_format($row->pay))
            ->addColumn('sisa', function ($row) {
                if ($row->due != 0) {
                    $selisih = number_format($row->sub_total - $row->pay);
                    return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-rose-100 text-rose-600">Rp. '.$selisih.'</div>';
                }
                return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-emerald-100 text-emerald-600">Lunas</div>';
            })
            ->addColumn('item', function ($row) {
                $orderItem = OrderDetail::with('product')
                    ->where('orders_id', $row->id)
                    ->orderBy('id', 'DESC')
                    ->get();

                $produkDetails = '<ul class="list-disc pl-5 m-0">';
                foreach ($orderItem as $item) {
                    if ($item->product->categories_id === 1) {
                        $produkDetails .= '<li>'
                            . $item->product_name
                            . ' IMEI: ' . $item->product->nomor_seri
                            . '</li>';
                    } else {
                        $produkDetails .= '<li>'
                            . $item->product_name
                            . '</li>';
                    }
                }
                $produkDetails .= '</ul>';

                return '<div class="inline-flex font-medium rounded-lg text-left px-3 py-2 text-emerald-700">'
                    . $produkDetails
                    . '</div>';
            })

            ->addColumn('status', function ($row) {
                $route = route('transaksi-penjualan-approve.edit', $row->id);

                if (auth()->user()->role == 'Kepala Toko') {
                    $content = '<a href="'.$route.'">';
                } else {
                    $content = '';
                }

                if ($row->is_approve === null) {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-amber-500 text-white">Belum Disetujui</div>';
                } elseif ($row->is_approve === 'Setuju') {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-blue-500 text-white">Sudah Disetujui</div>';
                } else {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-red-500 text-white">Ditolak</div>';
                }

                if (auth()->user()->role != 'Investor') {
                    $content .= '</a>';
                }

                return $content;
            })
            ->addColumn('aksi', function ($row) {
                $show = route('transaksi-produk.show', $row->id);
                $delete = route('transaksi-produk.destroy', $row->id);
                $printerTermal = route('cetak-termal', $row->id);
                $printerInkjet = route('lunas-cetak-inkjet', $row->id);

                return '
                <div class="space-x-1 flex">
                    <!-- Detail -->
                    <a href="'.$show.'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6f32be" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                        </svg>
                    </a>

                    <!-- Printer Modal -->
            <div x-data="{ modalOpen: false }">
                <button @click.prevent="modalOpen = true" aria-controls="basic-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>

                <div
                    id="basic-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal content -->
                        <div class="px-5 pt-4 pb-1">
                            <div class="text-sm">
                                <div class="space-y-2">
                                    <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                <a href="'.$printerTermal.'" target="_blank">
                                    <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white flex items-center space-x-1">
                                        <span>Printer Termal</span>
                                    </button>
                                </a>
                                <a href="'.$printerInkjet.'" target="_blank">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white flex items-center space-x-1">
                                        <span>Printer Inkjet</span>
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Delete Modal -->
                    <div x-data="{ deleteOpen: false }">
                        <button class="text-rose-500" @click.prevent="deleteOpen = true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="4" y1="7" x2="20" y2="7" />
                                <line x1="10" y1="11" x2="10" y2="17" />
                                <line x1="14" y1="11" x2="14" y2="17" />
                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                        </button>
                        <div x-show="deleteOpen" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 flex items-center justify-center">
                            <div class="bg-white rounded shadow-lg p-4" @click.outside="deleteOpen = false">
                                <form action="'.$delete.'" method="POST">
                                    '.csrf_field().method_field('DELETE').'
                                    <p>Yakin ingin menghapus? Data tidak bisa dikembalikan.</p>
                                    <button type="submit" class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                    <button type="button" class="btn-sm border" @click="deleteOpen = false">Batal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            })
            ->rawColumns(['checkbox','invoice_no','sales','sisa','status','aksi','item'])
            ->make(true);
    }

    public function dataLunas(Request $request)
    {

        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $orders = Order::select('orders.*', 'order_details.modal')
            ->join('order_details', 'orders.id', '=', 'order_details.orders_id')
            ->where('orders.cabang_id',getCabangId())
            ->with(['user', 'customer'])
            ->where('due', '0')
            ->orderByRaw('is_approve IS NULL DESC')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get();

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('checkbox', fn($row) => '
                <div class="flex items-center">
                    <label class="inline-flex">
                        <span class="sr-only">Select</span>
                        <input class="table-item form-checkbox" type="checkbox" value="'.$row->id.'" @click="uncheckParent" />
                    </label>
                </div>
            ')
            ->addColumn('invoice_no', function ($row) {
                $link = route('transaksi-produk.edit', $row->id);
                if (auth()->user()->role != 'Investor') {
                    return '
                        <div class="flex items-center text-blue-600">
                            <a href="'.$link.'" class="text-blue-600">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                                <div class="font-medium">'.$row->invoice_no.'</div>
                            </a>
                        </div>';
                }else{
                     return '
                        <div class="flex items-center text-blue-600">
                            <a href="#" class="text-blue-600">
                                <div class="font-medium">'.$row->invoice_no.'</div>
                            </a>
                        </div>';
                }
                return $row->invoice_no;
            })
            ->addColumn('tgl_transaksi', fn($row) => \Carbon\Carbon::parse($row->created_at)->locale('id')->translatedFormat('d F Y'))
            ->addColumn('sales', fn($row) => $row->user ? $row->user->name : '<span class="text-red-500">Akun dihapus</span>')
            ->addColumn('pelanggan', fn($row) => $row->customer ? $row->customer->nama : '-')
            ->addColumn('pembayaran', fn($row) => $row->payment_method)
            ->addColumn('modal', fn($row) => 'Rp. '.number_format($row->modal))
            ->addColumn('sub_total', fn($row) => 'Rp. '.number_format($row->sub_total))
            ->addColumn('pay', fn($row) => 'Rp. '.number_format($row->pay))
            ->addColumn('sisa', function ($row) {
                if ($row->due != 0) {
                    $selisih = number_format($row->sub_total - $row->pay);
                    return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-rose-100 text-rose-600">Rp. '.$selisih.'</div>';
                }
                return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-emerald-100 text-emerald-600">Lunas</div>';
            })
             ->addColumn('item', function ($row) {
                $orderItem = OrderDetail::with('product')
                    ->where('orders_id', $row->id)
                    ->orderBy('id', 'DESC')
                    ->get();

                $produkDetails = '<ul class="list-disc pl-5 m-0">';
                foreach ($orderItem as $item) {
                    if ($item->product->categories_id === 1) {
                        $produkDetails .= '<li>'
                            . $item->product_name
                            . ' IMEI: ' . $item->product->nomor_seri
                            . '</li>';
                    } else {
                        $produkDetails .= '<li>'
                            . $item->product_name
                            . '</li>';
                    }
                }
                $produkDetails .= '</ul>';

                return '<div class="inline-flex font-medium rounded-lg text-left px-3 py-2 text-emerald-700">'
                    . $produkDetails
                    . '</div>';
            })
            ->addColumn('status', function ($row) {
                $route = route('transaksi-penjualan-approve.edit', $row->id);

                if (auth()->user()->role == 'Kepala Toko') {
                    $content = '<a href="'.$route.'">';
                } else {
                    $content = '';
                }

                if ($row->is_approve === null) {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-amber-500 text-white">Belum Disetujui</div>';
                } elseif ($row->is_approve === 'Setuju') {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-blue-500 text-white">Sudah Disetujui</div>';
                } else {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-red-500 text-white">Ditolak</div>';
                }

                if (auth()->user()->role != 'Investor') {
                    $content .= '</a>';
                }

                return $content;
            })
            ->addColumn('aksi', function ($row) {
                $show = route('transaksi-produk.show', $row->id);
                $delete = route('transaksi-produk.destroy', $row->id);
                $printerTermal = route('cetak-termal', $row->id);
                $printerInkjet = route('lunas-cetak-inkjet', $row->id);

                return '
                <div class="space-x-1 flex">
                    <!-- Detail -->
                    <a href="'.$show.'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6f32be" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                        </svg>
                    </a>

                    <!-- Printer Modal -->
            <div x-data="{ modalOpen: false }">
                <button @click.prevent="modalOpen = true" aria-controls="basic-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>

                <div
                    id="basic-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal content -->
                        <div class="px-5 pt-4 pb-1">
                            <div class="text-sm">
                                <div class="space-y-2">
                                    <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                <a href="'.$printerTermal.'" target="_blank">
                                    <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white flex items-center space-x-1">
                                        <span>Printer Termal</span>
                                    </button>
                                </a>
                                <a href="'.$printerInkjet.'" target="_blank">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white flex items-center space-x-1">
                                        <span>Printer Inkjet</span>
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Delete Modal -->
                    <div x-data="{ deleteOpen: false }">
                        <button class="text-rose-500" @click.prevent="deleteOpen = true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="4" y1="7" x2="20" y2="7" />
                                <line x1="10" y1="11" x2="10" y2="17" />
                                <line x1="14" y1="11" x2="14" y2="17" />
                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                        </button>
                        <div x-show="deleteOpen" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 flex items-center justify-center">
                            <div class="bg-white rounded shadow-lg p-4" @click.outside="deleteOpen = false">
                                <form action="'.$delete.'" method="POST">
                                    '.csrf_field().method_field('DELETE').'
                                    <p>Yakin ingin menghapus? Data tidak bisa dikembalikan.</p>
                                    <button type="submit" class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                    <button type="button" class="btn-sm border" @click="deleteOpen = false">Batal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            })
            ->rawColumns(['checkbox','invoice_no','sales','sisa','status','aksi','item'])
            ->make(true);
    }
    public function dataDue(Request $request)
    {
        $orders = Order::select('orders.*', 'order_details.modal')
            ->join('order_details', 'orders.id', '=', 'order_details.orders_id')
            ->with(['user', 'customer'])
            ->where('orders.cabang_id',getCabangId())
            ->where('due','>', '0')
            ->orderByRaw('is_approve IS NULL DESC')
            ->latest();

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('checkbox', fn($row) => '
                <div class="flex items-center">
                    <label class="inline-flex">
                        <span class="sr-only">Select</span>
                        <input class="table-item form-checkbox" type="checkbox" value="'.$row->id.'" @click="uncheckParent" />
                    </label>
                </div>
            ')
            ->addColumn('invoice_no', function ($row) {
                $link = route('transaksi-produk.edit', $row->id);
                if (auth()->user()->role != 'Investor') {
                    return '
                        <div class="flex items-center text-blue-600">
                            <a href="'.$link.'" class="text-blue-600">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                                <div class="font-medium">'.$row->invoice_no.'</div>
                            </a>
                        </div>';
                }else{
                     return '
                        <div class="flex items-center text-blue-600">
                            <a href="#" class="text-blue-600">
                                <div class="font-medium">'.$row->invoice_no.'</div>
                            </a>
                        </div>';
                }
                return $row->invoice_no;
            })
            ->addColumn('tgl_transaksi', fn($row) => \Carbon\Carbon::parse($row->created_at)->locale('id')->translatedFormat('d F Y'))
            ->addColumn('sales', fn($row) => $row->user ? $row->user->name : '<span class="text-red-500">Akun dihapus</span>')
            ->addColumn('pelanggan', fn($row) => $row->customer ? $row->customer->nama : '-')
            ->addColumn('pembayaran', fn($row) => $row->payment_method)
            ->addColumn('modal', fn($row) => 'Rp. '.number_format($row->modal))
            ->addColumn('sub_total', fn($row) => 'Rp. '.number_format($row->sub_total))
            ->addColumn('pay', fn($row) => 'Rp. '.number_format($row->pay))
            ->addColumn('sisa', function ($row) {
                if ($row->due != 0) {
                    $selisih = number_format($row->sub_total - $row->pay);
                    return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-rose-100 text-rose-600">Rp. '.$selisih.'</div>';
                }
                return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-emerald-100 text-emerald-600">Lunas</div>';
            })
            ->addColumn('item', function ($row) {
                $orderItem = OrderDetail::with('product')
                    ->where('orders_id', $row->id)
                    ->orderBy('id', 'DESC')
                    ->get();

                $produkDetails = '<ul class="list-disc pl-5 m-0">';
                foreach ($orderItem as $item) {
                    if ($item->product->categories_id === 1) {
                        $produkDetails .= '<li>'
                            . $item->product_name
                            . ' IMEI: ' . $item->product->nomor_seri
                            . '</li>';
                    } else {
                        $produkDetails .= '<li>'
                            . $item->product_name
                            . '</li>';
                    }
                }
                $produkDetails .= '</ul>';

                return '<div class="inline-flex font-medium rounded-lg text-left px-3 py-2 text-emerald-700">'
                    . $produkDetails
                    . '</div>';
            })
            ->addColumn('status', function ($row) {
                $route = route('transaksi-penjualan-approve.edit', $row->id);

                if (auth()->user()->role == 'Kepala Toko') {
                    $content = '<a href="'.$route.'">';
                } else {
                    $content = '';
                }

                if ($row->is_approve === null) {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-amber-500 text-white">Belum Disetujui</div>';
                } elseif ($row->is_approve === 'Setuju') {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-blue-500 text-white">Sudah Disetujui</div>';
                } else {
                    $content .= '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-red-500 text-white">Ditolak</div>';
                }

                if (auth()->user()->role != 'Investor') {
                    $content .= '</a>';
                }

                return $content;
            })
            ->addColumn('aksi', function ($row) {
                $show = route('transaksi-produk.show', $row->id);
                $delete = route('transaksi-produk.destroy', $row->id);
                $printerTermal = route('cetak-termal', $row->id);
                $printerInkjet = route('lunas-cetak-inkjet', $row->id);

                return '
                <div class="space-x-1 flex">
                    <!-- Detail -->
                    <a href="'.$show.'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6f32be" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                        </svg>
                    </a>

                    <!-- Printer Modal -->
            <div x-data="{ modalOpen: false }">
                <button @click.prevent="modalOpen = true" aria-controls="basic-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>

                <div
                    id="basic-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal content -->
                        <div class="px-5 pt-4 pb-1">
                            <div class="text-sm">
                                <div class="space-y-2">
                                    <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                <a href="'.$printerTermal.'" target="_blank">
                                    <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white flex items-center space-x-1">
                                        <span>Printer Termal</span>
                                    </button>
                                </a>
                                <a href="'.$printerInkjet.'" target="_blank">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white flex items-center space-x-1">
                                        <span>Printer Inkjet</span>
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Delete Modal -->
                    <div x-data="{ deleteOpen: false }">
                        <button class="text-rose-500" @click.prevent="deleteOpen = true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="4" y1="7" x2="20" y2="7" />
                                <line x1="10" y1="11" x2="10" y2="17" />
                                <line x1="14" y1="11" x2="14" y2="17" />
                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                        </button>
                        <div x-show="deleteOpen" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 flex items-center justify-center">
                            <div class="bg-white rounded shadow-lg p-4" @click.outside="deleteOpen = false">
                                <form action="'.$delete.'" method="POST">
                                    '.csrf_field().method_field('DELETE').'
                                    <p>Yakin ingin menghapus? Data tidak bisa dikembalikan.</p>
                                    <button type="submit" class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                    <button type="button" class="btn-sm border" @click="deleteOpen = false">Batal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            })
            ->rawColumns(['checkbox','invoice_no','sales','sisa','status','aksi','item'])
            ->make(true);
    }



    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Order::whereIn('id', $selectedIds)->delete();
        OrderDetail::whereIn('orders_id', $selectedIds)->delete();
        return response()->json(['message' => 'Data transaksi produk berhasil dihapus.']);
    }

    public function approveSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        Order::whereIn('id', $selectedIds)->update(['is_approve' => 'Setuju', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data transaksi produk berhasil disetujui.']);
    }

    public function rejectSelected(Request $request)
    {
        $tanggal = Carbon::now()->translatedFormat('Y-m-d');
        $selectedIds = $request->input('selectedIds');
        Order::whereIn('id', $selectedIds)->update(['is_approve' => 'Ditolak', 'tgl_disetujui' => $tanggal]);

        return response()->json(['message' => 'Data transaksi produk berhasil ditolak.']);
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
    public function show($orders_id)
    {
        $toko = User::find(1);
        $order = Order::with('customer')->where('id', $orders_id)->first();
        $orderItem = OrderDetail::with('product')->where('orders_id', $orders_id)->orderBy('id', 'DESC')->get();

        $produkDetails = '';
        foreach ($orderItem as $item) {

            // Tambahkan nomor seri jika kategori produk adalah 1
            if ($item->product->categories_id === 1) {
                $produkDetails .= $item->product_name . ' IMEI ' . $item->product->nomor_seri . ' (Rp ' . number_format($item->price, 0, ',', '.') . ' x ' . $item->quantity . ' pcs)%0A';
            } else {
                $produkDetails .= $item->product_name . ' (Rp ' . number_format($item->price, 0, ',', '.') . ' x ' . $item->quantity . ' pcs)%0A';
            }
        }

        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');
        $totalTax = $orderItem->sum('ppn');
        return view('pages.kepalatoko.produk.transaksi-detail', compact('order', 'orderItem', 'total', 'subtotal', 'totalTax', 'toko', 'produkDetails'));
    }

    public function OrderDueAjax($id)
    {
        $orders = Order::findOrFail($id);
        return response()->json($orders);
    } // End Method

    public function UpdateDue(Request $request)
    {

        $id = $request->id;
        $due_amount = $request->due;

        $allorder = Order::findOrFail($id);
        $maindue = $allorder->due;
        $mainpay = $allorder->pay;

        $paid_due = $maindue - $due_amount;
        $paid_pay = $mainpay + $due_amount;

        Order::findOrFail($id)->update([
            'due' => $paid_due,
            'pay' => $paid_pay,
        ]);

        return redirect()->route('transaksi-produk.index');
    } // End Method

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function cetaktermal($orders_id)
    {
        $order = Order::with('customer', 'user')->where('id', $orders_id)->first();
        $orderItem = OrderDetail::with('product')->where('orders_id', $orders_id)->orderBy('id', 'DESC')->get();
        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');
        $totalTax = $orderItem->sum('ppn');
        $totalWithoutTax = $order->sub_total - $totalTax;
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $order->invoice_no;
        $namaPelanggan = $order->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.produk.cetak-termal', [
            'order' => $order,
            'users' => $users,
            'orderItem' => $orderItem,
            'total' => $total,
            'subtotal' => $subtotal,
            'imagePath' => $imagePath,
            'totalTax' => $totalTax,
            'totalWithoutTax' => $totalWithoutTax,
        ]);

        $filename = 'Nota Penjualan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    public function cetakinkjet($orderId)
    {
        $order = Order::with('customer')->where('id', $orderId)->first();
        $orderItem = OrderDetail::with('product')->where('orders_id', $orderId)->orderBy('id', 'DESC')->get();
        $total = $orderItem->sum('total');
        $subtotal = $orderItem->sum('sub_total');
        $users = User::find(1);
        $terms = Term::find(3);
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $order->invoice_no;
        $namaPelanggan = $order->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.produk.lunas-cetak-inkjet', [
            'order' => $order,
            'users' => $users,
            'terms' => $terms,
            'orderItem' => $orderItem,
            'total' => $total,
            'subtotal' => $subtotal,
            'imagePath' => $imagePath,
            'toko' => $toko,
        ]);

        $filename = 'Nota Penjualan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    public function edit($id)
    {
        $item = Order::with('detailOrders')->findOrFail($id);
        $customers = Customer::all();
        $products = Product::all();
        $users = User::where('role', 'Sales')->get();

        return view('pages.kepalatoko.produk.transaksi-edit', [
            'item' => $item,
            'customers' => $customers,
            'products' => $products,
            'users' => $users,
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
        $item = Order::findOrFail($id);
        $nama_pelanggan = Customer::find($request->customers_id);

        // Loop untuk mengupdate order_details
        foreach ($request->input('order_details', []) as $orderDetailId => $orderDetailData) {
            $orderDetail = OrderDetail::findOrFail($orderDetailId);

            $profit = $orderDetailData['total'] - $orderDetailData['modal'];
            $profit_toko = $profit - ($profit / 100 * ($orderDetailData['persen_sales'] + $orderDetailData['persen_admin']));

            // Update order_details termasuk profit
            $orderDetail->update([
                'modal' => $orderDetailData['modal'],
                'total' => $orderDetailData['total'],
                'profit' => $profit,
                'profit_toko' => $profit_toko,
            ]);
        }

        // Transaction update
        $item->update([
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'users_id' => $request->users_id,
            'payment_method' => $request->payment_method,
            'pay' => $request->pay,
            'due' => $request->due,
            'created_at' => $request->created_at,
        ]);

        return redirect()->route('transaksi-produk.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Order::findOrFail($id);

        $item->delete();

        OrderDetail::where('orders_id', $item->id)->delete();

        return redirect()->route('transaksi-produk.index');
    }
}
