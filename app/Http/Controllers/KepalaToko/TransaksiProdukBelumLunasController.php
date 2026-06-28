<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class TransaksiProdukBelumLunasController extends Controller
{
    public function index()
    {
        $jumlah_semua = Order::where('cabang_id', getCabangId())->where('tipe_status_pembayaran',0)->count();
        $jumlah_lunas = Order::where('cabang_id', getCabangId())->where('due', '0')->count();
        $jumlah_tidaklunas = Order::where('cabang_id', getCabangId())->where('due', '>', '0')->count();
        $storeSettings = StoreSetting::where('cabang_id', getCabangId())->first();

        $customers = Customer::where('cabang_id', getCabangId())
        ->whereHas('sale', function ($query) {
            $query->where('tipe_status_pembayaran', 0);
        })
        ->get();
        // Arahkan ke file blade yang baru
        return view('pages.kepalatoko.produk.transaksi-belum-lunas', compact(
            'jumlah_semua',
            'jumlah_lunas',
            'jumlah_tidaklunas',
            'customers',
            'storeSettings'
        ));
    }

    public function cetakPerCustomer(Request $request)
    {

        $request->validate(['customers_id' => 'required']);

        // 1. Mengambil logo dan nama toko (Kepala Toko)
        if (getCabangId() == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', getCabangId())
                ->where('id', '!=', 1)
                ->where('role', 'Kepala Toko')
                ->orderBy('id', 'asc')
                ->first();
        }

        // Fallback jika user kepala toko tidak ditemukan
        if (!$users) {
             $users = User::find(1);
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // 2. Filter Input
        $customers_id = $request->customers_id;
        $tipe       = $request->tipe; // Ambil input tipe (Sudah Disetujui / Belum Disetujui)

        $detailQuery = OrderDetail::with('order')
            ->where('cabang_id', getCabangId());

        $orderQuery = Order::where('cabang_id', getCabangId());

        $detailQuery->whereHas('order', function ($q) use ($customers_id) {
            $q->where('customers_id', $customers_id);
            $q->where('tipe_status_pembayaran', 0);

        });

        $orderQuery->where('customers_id', $customers_id);


        $orders = (clone $detailQuery)->orderBy('created_at', 'asc')->get();

        $total_biaya     = (clone $detailQuery)->sum('total');
        $total_penjualan = (clone $detailQuery)->sum('quantity'); // Total Item

        $sum_total       = (clone $detailQuery)->sum('total');
        $sum_sub_total   = (clone $detailQuery)->sum('sub_total');


        $dataOtherMetodePembayaran = [];
        foreach (getMetodePembayaran() as $key => $value) {
            $dt['metode'] = $value->nama;
            $dt['total']    = (clone $orderQuery)->where('payment_method',$value->nama)->sum('transfer');
            array_push($dataOtherMetodePembayaran,$dt);
        }

        $customerName  = Customer::find($request->customers_id)->nama;

        // 6. RENDER PDF
        $pdf = \PDF::loadView('pages.kepalatoko.cetak-laporan-penjualan-belum-lunas', [
            'users'           => $users,
            'imagePath'       => $imagePath,
            'orders'          => $orders,
            'total_penjualan' => $total_penjualan,
            'customerName'     => $customerName,
            'total_biaya'     => $total_biaya,
            'dataOtherMetodePembayaran'     => $dataOtherMetodePembayaran,
            'tipe_laporan'    => $tipe // (Opsional) jika ingin menampilkan judul tipe di PDF
        ]);

        $filename = 'Laporan Transaksi Penjualan (' . ($tipe ?: 'Default') . ') ' . $customerName . '.pdf';

        return $pdf->stream($filename);
    }

    public function data(Request $request)
    {
        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $query = Order::select('orders.*')
        ->selectRaw('(SELECT SUM(modal) FROM order_details WHERE order_details.orders_id = orders.id) as modal')
        ->with(['user', 'customer'])
        ->where('orders.cabang_id', getCabangId())
        ->where('orders.tipe_status_pembayaran', 0)
        ->orderByRaw('is_approve IS NULL DESC')
        ->latest();

        if (Auth::user()->role == 'Sales') {
            $query->where('orders.users_id', Auth::user()->id);
        }

        $orders = $query->skip($offset)->take($limit)->get();

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
            ->addColumn('status_pembayaran', function ($row) {
                if ($row->tipe_status_pembayaran == 0) {
                    return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-rose-100 text-rose-600">Belum Lunas</div>';
                }
                return '<div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-emerald-100 text-emerald-600">Lunas</div>';
            })
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

                   
                </div>';
            })
            ->rawColumns(['checkbox','invoice_no','sales','sisa','status','aksi','item','status_pembayaran'])
            ->make(true);
    }
}
