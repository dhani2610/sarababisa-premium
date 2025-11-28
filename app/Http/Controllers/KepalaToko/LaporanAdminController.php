<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;

class LaporanAdminController extends Controller
{
    public function index()
    {
        $users = User::where('cabang_id',getCabangId())->with('adminservice', 'adminsale')->where('role', 'Admin Toko')
            ->get();

        return view('pages/kepalatoko/laporan-admin', compact(
            'users'
        ));
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $admin = User::find($request->admin_id);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data servis
        $services = ServiceTransaction::with('brand', 'modelserie')->where('admin_id', $request->admin_id)->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->orderBy('tgl_ambil', 'asc')
            ->get();

        // Menghitung total tindakan
        $total_tindakan = ServiceTransaction::where('admin_id', $request->admin_id)->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->count();

        // Menghitung total biaya servis
        $total_biaya_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')->where('admin_id', $request->admin_id)
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->sum('biaya');

        // Menghitung total profit servis
        $total_profit_servis = ServiceTransaction::where('admin_id', $request->admin_id)->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->sum('profit');

        // Menghitung total bonus servis
        // $total_bonus_servis = $total_profit_servis / 100 * $admin->persen;

        // Mengambil data penjualan
        $orders = OrderDetail::with('order')->where('admin_id', $request->admin_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->orderBy('created_at', 'asc')
            ->get();

        // Menghitung total biaya penjualan
        $total_biaya_penjualan = OrderDetail::where('admin_id', $request->admin_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->sum('total');

        // Menghitung total profit
        $total_profit_penjualan = OrderDetail::where('admin_id', $request->admin_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->sum('profit');

        // Menghitung total item penjualan
        $total_penjualan = OrderDetail::where('admin_id', $request->admin_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->count();;

        // $total_bonus_penjualan = $total_profit_penjualan / 100 * $admin->persen;

        // $total_bonus_admin = $total_bonus_penjualan + $total_bonus_servis;


        // === Hitung Bonus Berdasarkan Tipe ===
        $tipeBonus = $admin->tipe_bonus_admin ?? 'Persen';
        $persen = $admin->persen ?? 0;
        $nominalTetap = $admin->nominal_bonus_admin ?? 0;

        if ($tipeBonus === 'Persen') {
            $total_bonus_servis = ($total_profit_servis / 100) * $persen;
            $total_bonus_penjualan = ($total_profit_penjualan / 100) * $persen;
        } elseif ($tipeBonus === 'Tetap') {
            $total_bonus_servis = $total_tindakan * $nominalTetap;
            $total_bonus_penjualan = $total_penjualan * $nominalTetap;
        } else {
            $total_bonus_servis = 0;
            $total_bonus_penjualan = 0;
        }

        $total_bonus_admin = $total_bonus_servis + $total_bonus_penjualan;

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-admin', [
            'users' => $users,
            'admin' => $admin,
            'imagePath' => $imagePath,
            'services' => $services,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_biaya_servis' => $total_biaya_servis,
            'total_bonus_servis' => $total_bonus_servis,
            'total_tindakan' => $total_tindakan,
            'orders' => $orders,
            'total_biaya_penjualan' => $total_biaya_penjualan,
            'total_penjualan' => $total_penjualan,
            'total_bonus_penjualan' => $total_bonus_penjualan,
            'total_bonus_admin' => $total_bonus_admin,
        ]);

        $filename = 'Laporan Admin ' . '' . $admin->name . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
}
