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
        $users = User::forCabang($cabang_id)->where('role', 'Teknisi')->get();
        // $users = User::where('id',63)->where('cabang_id', $cabang_id)->where('role', 'Teknisi')->get();

        return view('pages/kepalatoko/laporan-teknisi', compact('users'));
    }
    public function getData(Request $request)
    {
        $cabang_id = getCabangId();

        // --- Logika AJAX DataTables ---
            $users = User::forCabang($cabang_id)
                ->where('role', 'Teknisi')
                ->with(['servicetransaction', 'targetServis']);

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('servis_ditangani', function($row) {
                    return count(servisIdMultiTeknisi($row->id));
                })
                ->addColumn('bonus', function($row) {
                    $ids = servisIdMultiTeknisi($row->id);
                    $bonus = 0;
                    foreach ($ids as $id) {
                        $bonus += getBonusTeknisiByTransaction($id, $row->id);
                    }
                    return 'Rp. ' . number_format($bonus);
                })
                ->addColumn('target_servis', function($row) {
                    $target = $row->targetServis->sum('item');
                    return $target != 0 ? $target : '-';
                })
                ->addColumn('progres', function($row) {
                    $target = $row->targetServis->sum('item');
                    if ($target != 0) {
                        $ids = servisIdMultiTeknisi($row->id);
                        $progres = (count($ids) / $target) * 100;
                        return round($progres, 2) . '%';
                    }
                    return '-';
                })
                ->addColumn('bonus_pencapaian', function($row) {
                    $target = $row->targetServis->sum('item');
                    if ($target != 0) {
                        $ids = servisIdMultiTeknisi($row->id);
                        $bonus = 0;
                        foreach ($ids as $id) {
                            $bonus += getBonusTeknisiByTransaction($id, $row->id);
                        }

                        $count = count($ids);
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
            ->where(function($q) use ($start_date, $end_date) {
                $q->where(function($sub) use ($start_date, $end_date) {
                    $sub->whereNotNull('tgl_ambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })->orWhere(function($sub) use ($start_date, $end_date) {
                    $sub->whereNull('tgl_ambil')
                        ->whereDate('tgl_disetujui', '>=', $start_date)
                        ->whereDate('tgl_disetujui', '<=', $end_date);
                });
            })
            ->where('cabang_id', getCabangId())
            ->orderBy('tgl_ambil', 'asc')
            ->get();

        $total_bonus = 0;
        foreach ($services as $service) {
            $bonusItem = getBonusTeknisiByTransaction($service->id, $teknisi->id);
            $service->bonus = $bonusItem;
            if ($service->is_approve === 'Setuju') {
                $total_bonus += $bonusItem;
            }
        }

        // Menghitung total biaya
        $total_biaya = $services->sum('biaya');

        // Menghitung total tindakan
        $total_tindakan = $services->count();

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-teknisi', [
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
