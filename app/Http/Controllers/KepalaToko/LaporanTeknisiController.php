<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\TeknisiServis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables; // Tambahkan ini

class LaporanTeknisiController extends Controller
{
    public function index(Request $request)
    {
        $cabang_id = getCabangId();

        // --- Logika AJAX DataTables ---
        if ($request->ajax()) {
            $users = User::where('cabang_id', $cabang_id)
                ->where('role', 'Teknisi')
                ->with(['servicetransaction', 'targetServis']);

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('servis_ditangani', function($row) {
                    return $row->servicetransaction->count();
                })
                ->addColumn('bonus', function($row) {
                    $bonus_cek = ($row->servicetransaction->where('tipe', 'Hardware')->sum('profit') / 100) * $row->persen;
                    $bonus = $bonus_cek + $row->servicetransaction->where('tipe', 'Interface')->sum('bonus_interface');
                    return 'Rp. ' . number_format($bonus);
                })
                ->addColumn('target_servis', function($row) {
                    $target = $row->targetServis->sum('item');
                    return $target != 0 ? $target : '-';
                })
                ->addColumn('progres', function($row) {
                    $target = $row->targetServis->sum('item');
                    if ($target != 0) {
                        $progres = ($row->servicetransaction->count() / $target) * 100;
                        return round($progres, 2) . '%';
                    }
                    return '-';
                })
                ->addColumn('bonus_pencapaian', function($row) {
                    $target = $row->targetServis->sum('item');
                    if ($target != 0) {
                        $bonus_cek = ($row->servicetransaction->where('tipe', 'Hardware')->sum('profit') / 100) * $row->persen;
                        $bonus = $bonus_cek + $row->servicetransaction->where('tipe', 'Interface')->sum('bonus_interface');

                        $count = $row->servicetransaction->count();
                        $reward = $bonus * (($count / $target) * 100) / 100;

                        return $count < $target ? 'Rp. ' . number_format($reward) : 'Rp. ' . number_format($bonus);
                    }
                    return '-';
                })
                ->make(true);
        }

        // Untuk Modal Cetak Laporan
        $users = User::where('cabang_id', $cabang_id)->where('role', 'Teknisi')->get();

        return view('pages/kepalatoko/laporan-teknisi', compact('users'));
    }
    public function getData(Request $request)
    {
        $cabang_id = getCabangId();

        // --- Logika AJAX DataTables ---
            $users = User::where('cabang_id', $cabang_id)
                ->where('role', 'Teknisi')
                ->with(['servicetransaction', 'targetServis']);

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('servis_ditangani', function($row) {
                    return $row->servicetransaction->count();
                })
                ->addColumn('bonus', function($row) {
                    $bonus_cek = ($row->servicetransaction->where('tipe', 'Hardware')->sum('profit') / 100) * $row->persen;
                    $bonus = $bonus_cek + $row->servicetransaction->where('tipe', 'Interface')->sum('bonus_interface');
                    return 'Rp. ' . number_format($bonus);
                })
                ->addColumn('target_servis', function($row) {
                    $target = $row->targetServis->sum('item');
                    return $target != 0 ? $target : '-';
                })
                ->addColumn('progres', function($row) {
                    $target = $row->targetServis->sum('item');
                    if ($target != 0) {
                        $progres = ($row->servicetransaction->count() / $target) * 100;
                        return round($progres, 2) . '%';
                    }
                    return '-';
                })
                ->addColumn('bonus_pencapaian', function($row) {
                    $target = $row->targetServis->sum('item');
                    if ($target != 0) {
                        $bonus_cek = ($row->servicetransaction->where('tipe', 'Hardware')->sum('profit') / 100) * $row->persen;
                        $bonus = $bonus_cek + $row->servicetransaction->where('tipe', 'Interface')->sum('bonus_interface');

                        $count = $row->servicetransaction->count();
                        $reward = $bonus * (($count / $target) * 100) / 100;

                        return $count < $target ? 'Rp. ' . number_format($reward) : 'Rp. ' . number_format($bonus);
                    }
                    return '-';
                })
                ->make(true);
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
        $services = ServiceTransaction::with('brand', 'modelserie')
            ->whereIn('id', servisIdMultiTeknisi($request->users_id))
            ->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->orderBy('tgl_ambil', 'asc')
            ->get();
        // dd($services,servisIdMultiTeknisi($request->users_id));
        // Menghitung total biaya
        $total_biaya = ServiceTransaction::where('status_servis', 'Sudah Diambil')->where('users_id', $request->users_id)
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('biaya');

        // Menghitung total tindakan
        $total_tindakan = ServiceTransaction::where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->count();;

        // Menghitung total bonus
            $total_profit_interface = ServiceTransaction::where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
                ->where('tipe', 'Interface')
                ->where('is_approve', 'Setuju')
                ->sum('bonus_interface');
            // Menghitung total profit
            $total_profit = ServiceTransaction::where('users_id', $request->users_id)->where('status_servis', 'Sudah Diambil')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
                ->where('is_approve', 'Setuju')
                ->where('tipe', 'Hardware')
                ->sum('profit');


        $bonusTeknisiServisInterface = TeknisiServis::where('users_id', $request->users_id)
            ->where('tipe', 'Interface')
            ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                $query->where('is_approve', 'Setuju')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date);
            })
            ->sum('bonus_interface');

        $bonusTeknisiServisHardware = TeknisiServis::where('users_id', $request->users_id)
            ->where('tipe', 'Hardware')
            ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                $query->where('is_approve', 'Setuju')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date);
            })
            ->get()
            // Jika Anda ingin menghitung bagi hasil (profit * persen / 100):
            ->sum(function ($item) {
                // Rumus: Profit Barang * Persen Teknisi / 100
                return $item->profit * ($item->persen_teknisi / 100);
            });

        $total_bonus_prof = $total_profit / 100 * $teknisi->persen +$bonusTeknisiServisHardware;
        $total_bonus = $total_bonus_prof + $total_profit_interface + $bonusTeknisiServisInterface ;
        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-teknisi', [
        // return View('pages.kepalatoko.cetak-laporan-teknisi', [
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
