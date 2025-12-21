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
use Illuminate\Support\Facades\Request;

class RecycleBinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function service()
    {
        return view('pages/kepalatoko/keranjang-sampah/servis');
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

    public function order()
    {
        return view('pages/kepalatoko/keranjang-sampah/penjualan');
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
