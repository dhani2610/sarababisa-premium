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

        // Untuk Modal Cetak Laporan
        $users = User::where('cabang_id', $cabang_id)->where('role', 'Teknisi')->get();
        // $users = User::where('id',63)->where('cabang_id', $cabang_id)->where('role', 'Teknisi')->get();

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
                    $bonus = $bonus_cek + $row->servicetransaction->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])->sum('bonus_interface');
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
                        $bonus = $bonus_cek + $row->servicetransaction->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])->sum('bonus_interface');

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
        // $users = User::find(1);
        if (getCabangId() == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini. lalu setting kop di pengaturan toko melalui akun kepala toko', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }

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

        $services->map(function ($service) use ($request, $teknisi) {
            $relasiTeknisi = \App\Models\TeknisiServis::where('service_transactions_id', $service->id)
                ->where('users_id', $request->users_id)
                ->first();

            if ($relasiTeknisi) {
                // Cek apakah akun tersebut Teknisi Interface dan tipenya Interface Leveling (atau varian Interface lainnya)
                $tipeInterface = ['Interface Leveling', 'Interface', 'Interface Persentase'];


                if ($teknisi->bagian_teknisi == 'Teknisi Interface' && in_array($relasiTeknisi->tipe, $tipeInterface)) {
                    $service->bonus_interface = $relasiTeknisi->bonus_interface;


                }
                elseif ($relasiTeknisi->tipe == 'Hardware') {
                    $service->bonus_interface = 0;
                }


                // $service->tipe = $relasiTeknisi->tipe;

            } else {
                $service->bonus_interface = 0;
            }
            // dd($relasiTeknisi,$teknisi->bagian_teknisi == 'Teknisi Interface' && in_array($relasiTeknisi->tipe, $tipeInterface),$relasiTeknisi->bonus_interface,$service);


            return $service;
        });

        // dd($services,servisIdMultiTeknisi($request->users_id));
        // Menghitung total biaya
        $total_biaya = $services->sum('biaya');

        // Menghitung total tindakan
       $total_tindakan = $services->count();

        // 3. Menghitung total bonus interface (Hanya jumlahkan yang is_approve = 'Setuju' dan tipe Interface)
        $total_profit_interface = $services->where('is_approve', 'Setuju')
            ->whereIn('tipe', ['Interface', 'Interface Leveling', 'Interface Persentase'])
            ->sum('bonus_interface');

        // 4. Menghitung total profit hardware (Hanya jumlahkan yang is_approve = 'Setuju' dan tipe Hardware)
        $total_profit = $services->where('is_approve', 'Setuju')
            ->where('tipe', 'Hardware')
            ->sum('profit');

        // 5. Kalkulasi akhir
        $total_bonus_prof = ($total_profit / 100) * $teknisi->persen;
        $total_bonus = $total_bonus_prof + $total_profit_interface;

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
