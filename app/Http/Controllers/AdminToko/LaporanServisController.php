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
use App\Models\Incident;
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
            ->where('cabang_id',getCabangId())
            ->get()
            ->sum('omzet');
        $profithari = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereDate('tgl_disetujui', today())
            ->where('cabang_id',getCabangId())
            ->get()
            ->sum('profittoko');
        // $omzetbulan = ServiceTransaction::with('serviceaction')
        //     ->where('is_approve', 'Setuju')
        //     ->whereMonth('tgl_disetujui', $currentMonth)
        //     ->where('cabang_id',getCabangId())
        //     ->get()
        //     ->sum('omzet');
        $omzetbulan = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->sum('omzet');

        $profitbulan = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereMonth('tgl_disetujui', $currentMonth)
            ->where('cabang_id',getCabangId())
            ->get()
            ->sum('profit');
        $omzettahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->where('cabang_id',getCabangId())
            ->get()
            ->sum('omzet');
        $profittahun = ServiceTransaction::with('serviceaction')
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $currentYear)
            ->where('cabang_id',getCabangId())
            ->get()
            ->sum('profit');
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        return view('pages/admintoko/laporan-servis', compact('omzethari', 'profithari', 'omzetbulan', 'profitbulan', 'omzettahun', 'profittahun', 'toko'));
    }

    // public function cetak(Request $request)
    // {
    //     // Mengambil logo dan nama toko
    //     $users = User::find(1);

    //     if (getCabangId() == 1) {
    //         $users = User::find(1);
    //     }else{
    //         $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
    //     }

    //     $toko = StoreSetting::where('cabang_id',getCabangId())->first();

    //     $logo = $users->profile_photo_path;
    //     $imagePath = public_path('storage/' . $logo);

    //     // Filter tanggal
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     // Mengambil data servis
    //     $user = Auth::user();

    //     // Filter user jika role Teknisi
    //     $isTeknisi = $user->role === 'Teknisi';
    //     $userId = $user->id;

    //     // Query dasar untuk ServiceTransaction
    //     $serviceQuery = ServiceTransaction::with('brand', 'modelserie', 'user')
    //         ->where('service_transactions.cabang_id', getCabangId())

    //         ->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date);
    //         // ->where('cabang_id', getCabangId());

    //     if ($isTeknisi) {
    //         $serviceQuery->where('users_id', $userId);
    //     }

    //     // Ambil semua servis
    //     $services = $serviceQuery->orderBy('tgl_ambil', 'asc')->get();

    //     // Servis yang ada DP
    //     $servicesDP = (clone $serviceQuery)
    //         ->whereNotNull('uang_muka')
    //         ->where('uang_muka', '!=', '0')
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();

    //     // Daftar servis
    //     $daftar_servis = (clone $serviceQuery)->select('tindakan_servis')->get();

    //     $total_servis = 0;
    //     foreach ($daftar_servis as $v) {
    //         $json = json_decode($v['tindakan_servis']) ?: [];
    //         $total_servis += count($json) ?: 1;
    //     }

    //     // Total pembayaran
    //     $total_tunai = (clone $serviceQuery)->sum('tunai');
    //     $total_transfer = (clone $serviceQuery)->sum('transfer');
    //     $total_kredit = (clone $serviceQuery)->sum('due');

    //     // Top brands
    //     // Top brands
    //     $topbrands = (clone $serviceQuery)
    //         ->join('brands', 'service_transactions.brands_id', '=', 'brands.id')
    //         ->select('brands.name as brand_name', DB::raw('COUNT(brands.id) as total'))
    //         ->groupBy('brands.id', 'brands.name')
    //         ->orderByDesc('total')
    //         ->limit(3)
    //         ->get();

    //     // Top model series
    //     $topmodelseries = (clone $serviceQuery)
    //         ->join('model_series', 'service_transactions.model_series_id', '=', 'model_series.id')
    //         ->select('model_series.name as model_name', DB::raw('COUNT(model_series.id) as total'))
    //         ->groupBy('model_series.id', 'model_series.name')
    //         ->orderByDesc('total')
    //         ->limit(3)
    //         ->get();

    //     // Top actions
    //     $topactions = (clone $serviceQuery)
    //         ->join('service_actions', 'service_transactions.service_actions_id', '=', 'service_actions.id')
    //         ->select('service_actions.nama_tindakan as action_name', DB::raw('COUNT(service_actions.id) as total'))
    //         ->groupBy('service_actions.id', 'service_actions.nama_tindakan')
    //         ->orderByDesc('total')
    //         ->limit(3)
    //         ->get();


    //     // Total modal, biaya, diskon, profit, dp
    //     $total_modal = (clone $serviceQuery)->sum('modal_sparepart');
    //     $total_biaya = (clone $serviceQuery)->sum('biaya');

    //     $total_diskon = ServiceTransaction::where('is_approve', 'Setuju')
    //         ->where('kondisi_servis', 'Sudah jadi')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->where('cabang_id', getCabangId())
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->when($isTeknisi, fn($q) => $q->where('users_id', $userId))
    //         ->sum('diskon');

    //     $total_profit = (clone $serviceQuery)->sum('profit');
    //     $total_dp = (clone $serviceQuery)->sum('uang_muka');

    //     // Query untuk Expense (pengeluaran)
    //     $expenseQuery = Expense::whereDate('created_at', '>=', $start_date)
    //         ->where('cabang_id', getCabangId())
    //         ->whereDate('created_at', '<=', $end_date);

    //     if ($isTeknisi) {
    //         $expenseQuery->where('users_id', $userId);
    //     }


    //     $total_insiden = Incident::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('biaya_toko');

    //     $totalInsiden = Incident::where('cabang_id',getCabangId())
    //         ->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->get()
    //         ->sum('biaya_toko');

    //     $total_pengeluaran = $expenseQuery->sum('price');
    //     $pengeluaran_data = $expenseQuery->get();

    //     // Hitung saldo akhir
    //     $saldo_akhir = $total_profit - $total_pengeluaran - $totalInsiden;


    //     $insiden = Incident::where('cabang_id',getCabangId())
    //         ->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->get();


    //     // return response()->json($services);
    //     $pdf = PDF::loadView('pages.admintoko.cetak-laporan-servis', [
    //         //    return View('pages.admintoko.cetak-laporan-servis', [
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
    //         'total_kredit' => $total_kredit,
    //         'insiden' => $insiden,
    //         'total_insiden' => $totalInsiden,
    //     ]);

    //     $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

    //     return $pdf->stream($filename);
    // }
   public function cetak(Request $request)
    {
        // 1. Mengambil logo dan nama toko
        $users = User::find(1);

        if (getCabangId() == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', getCabangId())
                ->where('id', '!=', 1)
                ->where('role', 'Kepala Toko')
                ->orderBy('id', 'asc')
                ->first();
        }

        $toko = StoreSetting::where('cabang_id', getCabangId())->first();

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // 2. Filter Input
        $start_date = $request->start_date;
        $end_date   = $request->end_date;
        $tipe       = $request->tipe; // Ambil input tipe

        // 3. Setup User & Role Teknisi
        $user      = Auth::user();
        $isTeknisi = $user->role === 'Teknisi';
        $userId    = $user->id;

        // 4. QUERY DASAR (BASE QUERY)
        // Kita siapkan query awal, belum di-get()
        $serviceQuery = ServiceTransaction::with('brand', 'modelserie', 'user')
            ->where('service_transactions.cabang_id', getCabangId());

        // Terapkan Filter Teknisi jika login sebagai teknisi
        if ($isTeknisi) {
            $serviceQuery->where('users_id', $userId);
        }

        // 5. LOGIKA FILTER BERDASARKAN TIPE
        $dateColumn = 'tgl_ambil'; // Default column sorting

        if ($tipe == 'Sudah Disetujui') {
            // Jika memilih laporan Sudah Disetujui
            $serviceQuery->where('is_approve', 'Setuju')
                ->whereDate('tgl_disetujui', '>=', $start_date)
                ->whereDate('tgl_disetujui', '<=', $end_date);

            $dateColumn = 'tgl_disetujui'; // Ubah sorting ke tgl_disetujui

        } else {
            // Default: Jika memilih laporan Sudah Diambil (atau null)
            $serviceQuery->where('status_servis', 'Sudah Diambil')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date);

            $dateColumn = 'tgl_ambil'; // Sorting pakai tgl_ambil
        }


        // Ambil semua servis utama
        $services = (clone $serviceQuery)->orderBy($dateColumn, 'asc')->get();

        // Servis yang ada DP
        $servicesDP = (clone $serviceQuery)
            ->whereNotNull('uang_muka')
            ->where('uang_muka', '!=', '0')
            ->orderBy($dateColumn, 'asc')
            ->get();

        // Hitung Total Servis (Jumlah Unit)
        // Logic lama Anda menghitung berdasarkan item di dalam JSON tindakan_servis
        $daftar_servis = (clone $serviceQuery)->select('tindakan_servis')->get();
        $total_servis = 0;
        foreach ($daftar_servis as $v) {
            $json = json_decode($v['tindakan_servis']) ?: [];
            // Jika json kosong hitung 1, jika ada isinya hitung jumlah itemnya
            $total_servis += (count($json) > 0) ? count($json) : 1;
        }

        // Statistik Top 3 Brands
        $topbrands = (clone $serviceQuery)
            ->join('brands', 'service_transactions.brands_id', '=', 'brands.id')
            ->select('brands.name as brand_name', DB::raw('COUNT(brands.id) as total'))
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Statistik Top 3 Model Series
        $topmodelseries = (clone $serviceQuery)
            ->join('model_series', 'service_transactions.model_series_id', '=', 'model_series.id')
            ->select('model_series.name as model_name', DB::raw('COUNT(model_series.id) as total'))
            ->groupBy('model_series.id', 'model_series.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Statistik Top 3 Actions
        $topactions = (clone $serviceQuery)
            ->join('service_actions', 'service_transactions.service_actions_id', '=', 'service_actions.id')
            ->select('service_actions.nama_tindakan as action_name', DB::raw('COUNT(service_actions.id) as total'))
            ->groupBy('service_actions.id', 'service_actions.nama_tindakan')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Hitung Keuangan (Menggunakan clone agar sesuai filter tipe di atas)
        $total_modal    = (clone $serviceQuery)->sum('modal_sparepart');
        $total_biaya    = (clone $serviceQuery)->sum('biaya');
        $total_diskon   = (clone $serviceQuery)->sum('diskon');
        $total_profit   = (clone $serviceQuery)->sum('profit'); // Pastikan kolom profit benar
        $total_dp       = (clone $serviceQuery)->sum('uang_muka');

        // Total Pembayaran
        $total_tunai    = (clone $serviceQuery)->sum('tunai');
        $total_transfer = (clone $serviceQuery)->whereNotIn('cara_pembayaran',getMetodePembayaran()->pluck('nama'))->sum('transfer');
        $total_kredit   = (clone $serviceQuery)->sum('due'); // due biasanya sisa tagihan/kredit

        // --- DATA PENGELUARAN & INSIDEN (Tetap created_at karena tidak ada status ambil/setuju) ---

        $expenseQuery = Expense::whereDate('created_at', '>=', $start_date)
            ->where('cabang_id', getCabangId())
            ->where('tipe', 1)
            ->whereDate('created_at', '<=', $end_date);

        if ($isTeknisi) {
            $expenseQuery->where('users_id', $userId);
        }

        $pengeluaran_data  = $expenseQuery->get();
        $total_pengeluaran = $expenseQuery->sum('price');

        // Data Insiden
        $incidentQuery = Incident::where('cabang_id', getCabangId())
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);

        $insiden       = $incidentQuery->get();
        $totalInsiden  = $incidentQuery->sum('biaya_toko'); // Menggunakan sum langsung biar lebih efisien

        // Hitung Saldo Akhir
        $saldo_akhir = $total_profit - $total_pengeluaran - $totalInsiden;

        $dataOtherMetodePembayaran = [];
        foreach (getMetodePembayaran() as $key => $value) {
            $dt['metode'] = $value->nama;
            $dt['total']    = (clone $serviceQuery)->where('cara_pembayaran',$value->nama)->sum('transfer');
            array_push($dataOtherMetodePembayaran,$dt);
        }
        // Render PDF
        $pdf = PDF::loadView('pages.admintoko.cetak-laporan-servis', [
            'users'             => $users,
            'toko'              => $toko,
            'imagePath'         => $imagePath,
            'servicesDP'        => $servicesDP,
            'services'          => $services,
            'start_date'        => $start_date,
            'end_date'          => $end_date,
            'total_modal'       => $total_modal,
            'total_biaya'       => $total_biaya,
            'total_diskon'      => $total_diskon,
            'total_profit'      => $total_profit,
            'topbrands'         => $topbrands,
            'topmodelseries'    => $topmodelseries,
            'topactions'        => $topactions,
            'total_servis'      => $total_servis,
            'total_tunai'       => $total_tunai,
            'total_transfer'    => $total_transfer,
            'total_dp'          => $total_dp,
            'saldo_akhir'       => $saldo_akhir,
            'pengeluaran_data'  => $pengeluaran_data,
            'total_pengeluaran' => $total_pengeluaran,
            'dataOtherMetodePembayaran' => $dataOtherMetodePembayaran,
            'total_kredit'      => $total_kredit,
            'insiden'           => $insiden,
            'total_insiden'     => $totalInsiden,
            'tipe_laporan'      => $tipe // Dikirim ke view jika ingin menampilkan judul dinamis
        ]);

        $filename = 'Laporan Transaksi Servis (' . ($tipe ?: 'Sudah Diambil') . ') ' . $start_date . ' sd ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
}
