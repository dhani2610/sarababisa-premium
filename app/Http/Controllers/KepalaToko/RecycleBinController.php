<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Debt;
use App\Models\User;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // Tambahkan ini
use Carbon\Carbon;
class RecycleBinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function service(Request $request)
    {
        // === LOGIC DATATABLES (AJAX) ===
        if ($request->ajax()) {
            // Ambil data sampah
            $data = ServiceTransaction::where('cabang_id', getCabangId())
                ->onlyTrashed()
                ->latest()
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('deleted_at', function($row){
                    return Carbon::parse($row->deleted_at)->format('d/m/Y');
                })
                ->editColumn('created_at', function($row){
                    return Carbon::parse($row->created_at)->format('d/m/Y');
                })
                ->editColumn('tindakan_servis', function($row){
                    return $row->tindakan_servis ?? '-';
                })
                ->addColumn('action', function($row){
                    // URL Routes
                    $restoreUrl = route('restore-keranjang-servis', $row->id);
                    $deleteUrl = route('hapus-keranjang-servis', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('delete');

                    // HTML Action (Restore + Delete Modal AlpineJS)
                    // Disamakan persis dengan view asli
                    $html = <<<HTML
                    <div class="space-x-1 flex justify-center items-center">
                        <a href="{$restoreUrl}">
                            <button class="text-blue-400 hover:text-blue-500 rounded-full">
                                <span class="sr-only">Pulihkan</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                            </button>
                        </a>

                        <div x-data="{ modalOpen: false }">
                            <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true">
                                <span class="sr-only">Delete</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                </svg>
                            </button>

                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                aria-hidden="true" x-cloak></div>

                            <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                role="dialog" aria-modal="true" x-show="modalOpen"
                                x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>

                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                    <div class="p-5 flex space-x-4 text-left">
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
                                                <div class="space-y-2">
                                                    <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap justify-end space-x-2">
                                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                                <form action="{$deleteUrl}" method="post">
                                                    {$csrf}
                                                    {$method}
                                                    <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    HTML;
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // === VIEW PAGE ===
        $services_count = ServiceTransaction::where('cabang_id', getCabangId())
            ->onlyTrashed()
            ->count();

        return view('pages.kepalatoko.keranjang-sampah.servis', compact('services_count'));
    }

    public function permanentlyDelete($id)
    {
        $item = ServiceTransaction::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data servis berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-servis');
    }

    public function restore($id)
    {
        $item = ServiceTransaction::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data servis berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-servis');
    }

    public function cleanService()
    {
        // Mengambil semua data yang telah dihapus
        $items = ServiceTransaction::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data servis berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-servis');
    }

    public function account()
    {
        return view('pages/kepalatoko/keranjang-sampah/akun');
    }

    public function permanentlyDeleteAccount($id)
    {
        $item = User::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data akun berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-akun');
    }

    public function restoreAccount($id)
    {
        $item = User::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data akun berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-akun');
    }

    public function cleanAccount()
    {
        // Mengambil semua data yang telah dihapus
        $items = User::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data akun berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-akun');
    }

    public function customer()
    {
        return view('pages/kepalatoko/keranjang-sampah/pelanggan');
    }

    public function permanentlyDeleteCustomer($id)
    {
        $item = Customer::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data pelanggan berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-pelanggan');
    }

    public function restoreCustomer($id)
    {
        $item = Customer::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data pelanggan berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-pelanggan');
    }

    public function cleanCustomer()
    {
        // Mengambil semua data yang telah dihapus
        $items = Customer::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data pelanggan berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-pelanggan');
    }

    public function product()
    {
        return view('pages/kepalatoko/keranjang-sampah/produk');
    }

    public function permanentlyDeleteProduct($id)
    {
        $item = Product::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data produk berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-produk');
    }

    public function restoreProduct($id)
    {
        $item = Product::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data produk berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-produk');
    }

    public function cleanProduct()
    {
        // Mengambil semua data yang telah dihapus
        $items = Product::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data produk berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-produk');
    }

    public function incident()
    {
        return view('pages/kepalatoko/keranjang-sampah/insiden');
    }

    public function permanentlyDeleteIncident($id)
    {
        $item = Incident::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data insiden berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-insiden');
    }

    public function restoreIncident($id)
    {
        $item = Incident::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data insiden berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-insiden');
    }

    public function cleanIncident()
    {
        // Mengambil semua data yang telah dihapus
        $items = Incident::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data insiden berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-insiden');
    }

    public function debt()
    {
        return view('pages/kepalatoko/keranjang-sampah/kasbon');
    }

    public function permanentlyDeleteDebt($id)
    {
        $item = Debt::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data kasbon berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-kasbon');
    }

    public function restoreDebt($id)
    {
        $item = Debt::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data kasbon berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-kasbon');
    }

    public function cleanDebt()
    {
        // Mengambil semua data yang telah dihapus
        $items = Debt::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data kasbon berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-kasbon');
    }

    public function expense()
    {
        return view('pages/kepalatoko/keranjang-sampah/pengeluaran');
    }

    public function permanentlyDeleteExpense($id)
    {
        $item = Expense::withTrashed()->findOrFail($id);

        $item->forceDelete();

        toast('Data pengeluaran berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-pengeluaran');
    }

    public function restoreExpense($id)
    {
        $item = Expense::withTrashed()->findOrFail($id);

        $item->restore();

        toast('Data pengeluaran berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-pengeluaran');
    }

    public function cleanExpense()
    {
        // Mengambil semua data yang telah dihapus
        $items = Expense::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data pengeluaran berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-pengeluaran');
    }

public function order(Request $request)
    {
        // === LOGIC DATATABLES (AJAX) ===
        if ($request->ajax()) {
            // Query sesuai dengan Livewire sebelumnya: join dengan order_details
            // Perhatikan: Karena ini query builder, pastikan nama kolom tidak ambigu
            $data = Order::where('orders.cabang_id', getCabangId())
                ->onlyTrashed()
                ->select([
                    'orders.*',
                    'order_details.modal',
                    'order_details.quantity',
                    'order_details.product_name',
                    'order_details.sub_total' // Asumsi ada kolom ini atau dihitung
                ])
                ->join('order_details', 'orders.id', '=', 'order_details.orders_id')
                ->with(['user', 'customer']) // Eager load relasi
                ->latest('orders.created_at')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('deleted_at', function($row){
                    return Carbon::parse($row->deleted_at)->format('d/m/Y');
                })
                ->editColumn('user_name', function($row){
                    if ($row->user) {
                        return $row->user->name;
                    }
                    return '<span class="text-red-600 font-medium">Akun sudah dihapus</span>';
                })
                ->editColumn('customer_name', function($row){
                    return $row->customer->nama ?? '-';
                })
                ->editColumn('total_modal', function($row){
                    return 'Rp. ' . number_format($row->modal * $row->quantity);
                })
                ->editColumn('sub_total', function($row){
                    return 'Rp. ' . number_format($row->sub_total);
                })
                ->addColumn('action', function($row){
                    // URL Routes
                    $restoreUrl = route('restore-keranjang-penjualan', $row->id);
                    $deleteUrl = route('hapus-keranjang-penjualan', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('delete');

                    // HTML Action (Restore + Delete Modal AlpineJS)
                    // Disamakan persis dengan view asli (Copy-Paste HTML)
                    $html = <<<HTML
                    <div class="space-x-1 flex justify-center items-center">
                        <a href="{$restoreUrl}">
                            <button class="text-blue-400 hover:text-blue-500 rounded-full">
                                <span class="sr-only">Pulihkan</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                            </button>
                        </a>

                        <div x-data="{ modalOpen: false }">
                            <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true">
                                <span class="sr-only">Delete</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                </svg>
                            </button>

                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                aria-hidden="true" x-cloak></div>

                            <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                role="dialog" aria-modal="true" x-show="modalOpen"
                                x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>

                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                    <div class="p-5 flex space-x-4 text-left">
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
                                                <div class="space-y-2">
                                                    <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap justify-end space-x-2">
                                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                                <form action="{$deleteUrl}" method="post">
                                                    {$csrf}
                                                    {$method}
                                                    <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
HTML;
                    return $html;
                })
                ->rawColumns(['user_name', 'action']) // 'user_name' dirender html karena ada span merah
                ->make(true);
        }

        // === VIEW PAGE ===
        // Hitung count (query builder agar lebih ringan daripada get()->count())
        $items_count = Order::where('cabang_id', getCabangId())
            ->onlyTrashed()
            ->count();

        return view('pages.kepalatoko.keranjang-sampah.penjualan', compact('items_count'));
    }

    public function permanentlyDeleteOrder($id)
    {
        $item = Order::withTrashed()->findOrFail($id);

        $item->forceDelete();

        OrderDetail::withTrashed()->where('orders_id', $item->id)->forceDelete();

        toast('Data penjualan berhasil dihapus secara permanen.', 'success');

        return redirect()->route('keranjang-penjualan');
    }

    public function restoreOrder($id)
    {
        $item = Order::withTrashed()->findOrFail($id);

        $item->restore();

        OrderDetail::withTrashed()->where('orders_id', $item->id)->restore();

        toast('Data penjualan berhasil dipulihkan.', 'success');

        return redirect()->route('keranjang-penjualan');
    }

    public function cleanOrder()
    {
        // Mengambil semua data yang telah dihapus
        $items = Order::where('cabang_id',getCabangId())->onlyTrashed()->get();

        // Melakukan penghapusan permanen untuk setiap data yang telah dihapus
        foreach ($items as $item) {
            $item->forceDelete();
        }

        toast('Semua keranjang sampah data penjualan berhasil dibersihkan.', 'success');

        return redirect()->route('keranjang-penjualan');
    }
}
