<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\User;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class LaporanServisController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $omzethari = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_disetujui', today())
            ->get()
            ->sum('omzet');
        $profithari = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_disetujui', today())
            ->get()
            ->sum('profittoko');
        $omzetbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('omzet');
        $profitbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->get()
            ->sum('profittoko');
        $omzettahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->get()
            ->sum('omzet');
        $profittahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->get()
            ->sum('profittoko');
        $toko = StoreSetting::find(1);
        return view('pages/admintoko/laporan-servis', compact('omzethari', 'profithari', 'omzetbulan', 'profitbulan', 'omzettahun', 'profittahun', 'toko'));
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);
        $toko = StoreSetting::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data servis
        $user = Auth::user();

        // Filter user jika role Teknisi
        $isTeknisi = $user->role === 'Teknisi';
        $userId = $user->id;

        // Query dasar untuk ServiceTransaction
        $serviceQuery = ServiceTransaction::with('brand', 'modelserie', 'user')
            ->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date);

        if ($isTeknisi) {
            $serviceQuery->where('users_id', $userId);
        }

        // Ambil semua servis
        $services = $serviceQuery->orderBy('tgl_ambil', 'asc')->get();

        // Servis yang ada DP
        $servicesDP = (clone $serviceQuery)
            ->whereNotNull('uang_muka')
            ->where('uang_muka', '!=', '0')
            ->orderBy('tgl_ambil', 'asc')
            ->get();

        // Daftar servis
        $daftar_servis = (clone $serviceQuery)->select('tindakan_servis')->get();

        $total_servis = 0;
        foreach ($daftar_servis as $v) {
            $json = json_decode($v['tindakan_servis']) ?: [];
            $total_servis += count($json) ?: 1;
        }

        // Total pembayaran
        $total_tunai = (clone $serviceQuery)->sum('tunai');
        $total_transfer = (clone $serviceQuery)->sum('transfer');
        $total_kredit = (clone $serviceQuery)->sum('due');

        // Top brands
        // Top brands
        $topbrands = (clone $serviceQuery)
            ->join('brands', 'service_transactions.brands_id', '=', 'brands.id')
            ->select('brands.name as brand_name', DB::raw('COUNT(brands.id) as total'))
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Top model series
        $topmodelseries = (clone $serviceQuery)
            ->join('model_series', 'service_transactions.model_series_id', '=', 'model_series.id')
            ->select('model_series.name as model_name', DB::raw('COUNT(model_series.id) as total'))
            ->groupBy('model_series.id', 'model_series.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Top actions
        $topactions = (clone $serviceQuery)
            ->join('service_actions', 'service_transactions.service_actions_id', '=', 'service_actions.id')
            ->select('service_actions.nama_tindakan as action_name', DB::raw('COUNT(service_actions.id) as total'))
            ->groupBy('service_actions.id', 'service_actions.nama_tindakan')
            ->orderByDesc('total')
            ->limit(3)
            ->get();


        // Total modal, biaya, diskon, profit, dp
        $total_modal = (clone $serviceQuery)->sum('modal_sparepart');
        $total_biaya = (clone $serviceQuery)->sum('biaya');

        $total_diskon = ServiceTransaction::where('is_approve', 'Setuju')
            ->where('kondisi_servis', 'Sudah jadi')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->when($isTeknisi, fn($q) => $q->where('users_id', $userId))
            ->sum('diskon');

        $total_profit = (clone $serviceQuery)->sum('profit');
        $total_dp = (clone $serviceQuery)->sum('uang_muka');

        // Query untuk Expense (pengeluaran)
        $expenseQuery = Expense::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);

        if ($isTeknisi) {
            $expenseQuery->where('users_id', $userId);
        }

        $total_pengeluaran = $expenseQuery->sum('price');
        $pengeluaran_data = $expenseQuery->get();

        // Hitung saldo akhir
        $saldo_akhir = $total_profit - $total_pengeluaran;

        // return response()->json($services);
        $pdf = PDF::loadView('pages.admintoko.cetak-laporan-servis', [
            //    return View('pages.admintoko.cetak-laporan-servis', [
            'users' => $users,
            'toko' => $toko,
            'imagePath' => $imagePath,
            'servicesDP' => $servicesDP,
            'services' => $services,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_modal' => $total_modal,
            'total_biaya' => $total_biaya,
            'total_diskon' => $total_diskon,
            'total_profit' => $total_profit,
            'topbrands' => $topbrands,
            'topmodelseries' => $topmodelseries,
            'topactions' => $topactions,
            'total_servis' => $total_servis,
            'total_tunai' => $total_tunai,
            'total_transfer' => $total_transfer,
            'total_dp' => $total_dp,
            'saldo_akhir' => $saldo_akhir,
            'pengeluaran_data' => $pengeluaran_data,
            'total_pengeluaran' => $total_pengeluaran,
            'total_kredit' => $total_kredit
        ]);

        $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
    // public function cetak(Request $request)
    // {
    //     // Mengambil logo dan nama toko
    //     $users = User::find(1);
    //     $toko = StoreSetting::find(1);

    //     $logo = $users->profile_photo_path;
    //     $imagePath = public_path('storage/' . $logo);

    //     // Filter tanggal
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     // Mengambil data servis
    //     $services = ServiceTransaction::with('brand', 'modelserie', 'user')->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();
    //     $servicesDP = ServiceTransaction::with('brand', 'modelserie', 'user')->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->whereNotNull('uang_muka')
    //         ->where('uang_muka','!=','0')
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();
    //     // return response()->json($services);

    //     // Menghitung total item servis
    //     $daftar_servis = ServiceTransaction::select('tindakan_servis')->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();

    //     $total_servis = 0;
    //     foreach ($daftar_servis as $v) {
    //         $json = json_decode($v['tindakan_servis']) ? json_decode($v['tindakan_servis']) : [];
    //         $total_servis += count($json) == 0 ? 1 : count($json);
    //     }

    //     // Menghitung total pembayaran tunai
    //     $total_tunai = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('tunai');

    //     // Menghitung total pembayaran transfer
    //     $total_transfer = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('transfer');

    //     // Menghitung total pembayaran kredit
    //     $total_kredit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('due');

    //     // Mengambil data brand terbanyak
    //     $topbrands =
    //         ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->select('brands.name as brand_name')
    //         ->join('brands', 'service_transactions.brands_id', '=', 'brands.id')
    //         ->groupBy('brand_name')
    //         ->orderBy(DB::raw('COUNT(*)'), 'desc')
    //         ->limit(3)
    //         ->get();

    //     // Mengambil data model seri terbanyak
    //     $topmodelseries =
    //         ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->select('model_series.name as model_name')
    //         ->join('model_series', 'service_transactions.model_series_id', '=', 'model_series.id')
    //         ->groupBy('model_name')
    //         ->orderBy(DB::raw('COUNT(*)'), 'desc')
    //         ->limit(3)
    //         ->get();

    //     // Mengambil data model seri terbanyak
    //     $topactions =
    //         ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->select('service_actions.nama_tindakan as action_name')
    //         ->join('service_actions', 'service_transactions.service_actions_id', '=', 'service_actions.id')
    //         ->groupBy('action_name')
    //         ->orderBy(DB::raw('COUNT(*)'), 'desc')
    //         ->limit(3)
    //         ->get();

    //     // Menghitung total modal
    //     $total_modal = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('modal_sparepart');

    //     // Menghitung total biaya
    //     $total_biaya = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('biaya');

    //     // Menghitung total diskon
    //     $total_diskon = ServiceTransaction::where('is_approve', 'Setuju')
    //         ->where('kondisi_servis', "Sudah jadi")
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('diskon');

    //     // Menghitung total profit
    //     $total_profit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('profit');

    //     $total_dp = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->sum('uang_muka');

    //     // Menghitung total pengeluaran
    //     $total_pengeluaran = Expense::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->sum('price');
    //     $total_servis = $services->count();
    //     $saldo_akhir = $total_profit - $total_pengeluaran;

    //     $pengeluaran_data = Expense::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->get();
    //     // return response()->json($services);
    //     $pdf = PDF::loadView('pages.admintoko.cetak-laporan-servis', [
    // //    return View('pages.admintoko.cetak-laporan-servis', [
    //         'users' => $users,
    //         'toko' => $toko,
    //         'imagePath' => $imagePath,
    //         'servicesDP' => $servicesDP,
    //         'services' => $services,
    //         'start_date' => $start_date,
    //         'end_date' => $end_date,
    //         'total_modal' => $total_modal,
    //         'total_biaya' => $total_biaya,
    //         'total_diskon' => $total_diskon,
    //         'total_profit' => $total_profit,
    //         'topbrands' => $topbrands,
    //         'topmodelseries' => $topmodelseries,
    //         'topactions' => $topactions,
    //         'total_servis' => $total_servis,
    //         'total_tunai' => $total_tunai,
    //         'total_transfer' => $total_transfer,
    //         'total_dp' => $total_dp,
    //         'saldo_akhir' => $saldo_akhir,
    //         'pengeluaran_data' => $pengeluaran_data,
    //         'total_pengeluaran' => $total_pengeluaran,
    //         'total_kredit' => $total_kredit
    //     ]);

    //     $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

    //     return $pdf->stream($filename);
    // }
}
