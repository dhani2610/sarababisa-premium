<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;

class LaporanTeknisiController extends Controller
{
    public function index()
    {
        $users = User::with('servicetransaction')
            ->where('role', 'Teknisi')
            ->get();


        return view('pages/kepalatoko/laporan-teknisi', compact('users'));
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $teknisi = User::find($request->users_id);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data servis
        $services = ServiceTransaction::with('brand', 'modelserie')->where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->orderBy('tgl_ambil', 'asc')
            ->get();

        // Menghitung total biaya
        $total_biaya = ServiceTransaction::where('status_servis', 'Sudah Diambil')->where('users_id', $request->users_id)
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->sum('biaya');

        

        // Menghitung total tindakan
        $total_tindakan = ServiceTransaction::where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->count();;

        // Menghitung total bonus
            $total_profit_interface = ServiceTransaction::where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->where('tipe', 'Interface')
                ->where('is_approve', 'Setuju')
                ->sum('bonus_interface');
            // Menghitung total profit
            $total_profit = ServiceTransaction::where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->where('is_approve', 'Setuju')
                ->where('tipe', 'Hardware')
                ->sum('profit');

        $total_bonus_prof = $total_profit / 100 * $teknisi->persen;
        $total_bonus = $total_bonus_prof + $total_profit_interface;
        // $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-teknisi', [
        return View('pages.kepalatoko.cetak-laporan-teknisi', [
            'users' => $users,
            'teknisi' => $teknisi,
            'imagePath' => $imagePath,
            'services' => $services,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_biaya' => $total_biaya,
            'total_tindakan' => $total_tindakan,
            'total_bonus' => $total_bonus,
        ]);

        $filename = 'Laporan Teknisi ' . '' . $teknisi->name . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
}
