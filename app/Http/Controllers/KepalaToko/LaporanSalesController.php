<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanSalesController extends Controller
{
    public function index()
    {
        $users = User::where('cabang_id',getCabangId())->with('sale')->where('role', 'Sales')->get();


        return view('pages/kepalatoko/laporan-sales', compact('users'));
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $sales = User::find($request->users_id);

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $hitung_bonus = $request->hitung_bonus;
        $metode_pembayaran = $request->metode_pembayaran;

        // Mengambil data penjualan
        if ($metode_pembayaran === "Kredit") {
            $orders = OrderDetail::with('order')->where('payment_method', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->orderBy('created_at', 'asc')
                ->get();
            // Menghitung total item penjualan
            $total_penjualan = OrderDetail::where('payment_method', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->sum('quantity');
            // Menghitung total biaya
            $total_biaya = OrderDetail::where('payment_method', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->sum('total');
            // Menghitung total profit
            $total_profit = OrderDetail::where('payment_method', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->sum('profit');
        } else {
            $orders = OrderDetail::with('order')->where('payment_method', '!=', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->orderBy('created_at', 'asc')
                ->get();
            // Menghitung total item penjualan
            $total_penjualan = OrderDetail::where('payment_method', '!=', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->sum('quantity');
            // Menghitung total biaya
            $total_biaya = OrderDetail::where('payment_method', '!=', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->sum('total');
            // Menghitung total profit
            $total_profit = OrderDetail::where('payment_method', '!=', 'Kredit')->where('users_id', $request->users_id)
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->sum('profit');
        }

        if ($hitung_bonus === "profit") {
            $total_bonus = $total_profit / 100 * $sales->persen;
        } else {
            $total_bonus = $total_biaya / 100 * $sales->persen;
        }

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-sales', [
            'users' => $users,
            'sales' => $sales,
            'imagePath' => $imagePath,
            'orders' => $orders,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_biaya' => $total_biaya,
            'total_profit' => $total_profit,
            'total_penjualan' => $total_penjualan,
            'total_bonus' => $total_bonus,
            'hitung_bonus' => $hitung_bonus,
            'metode_pembayaran' => $metode_pembayaran,
        ]);

        $filename = 'Laporan Sales ' . '' . $sales->name . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
}
