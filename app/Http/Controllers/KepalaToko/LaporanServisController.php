<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Incident;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class LaporanServisController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // --- Logika AJAX DataTables ---
        if ($request->ajax()) {

            $limit = $request->get('limit', 200);
            $offset = $request->get('offset', 0);
            $data = ServiceTransaction::with('user')
                ->where('cabang_id', getCabangId())
                ->where('is_approve', 'Setuju')
                ->whereIn('kondisi_servis', ['Sudah jadi', 'Tidak bisa', 'Dibatalkan'])
                ->orderBy('tgl_disetujui', 'desc')
                ->latest()
                ->skip($offset)
                ->take($limit)
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tgl_disetujui', function($row) {
                    return $row->tgl_disetujui ? Carbon::parse($row->tgl_disetujui)->translatedFormat('d F Y') : '-';
                })
                ->editColumn('tgl_ambil', function($row) {
                    return $row->tgl_ambil ? Carbon::parse($row->tgl_ambil)->translatedFormat('d F Y') : '-';
                })
                ->addColumn('teknisi', function($row) {
                    return $row->user ? $row->user->name : '<span class="text-red-600">Akun dihapus</span>';
                })
                ->editColumn('tindakan_servis', function($row) {
                    if ($row->tindakan_servis) {
                        $tindakan = json_decode($row->tindakan_servis);
                        return is_array($tindakan) ? implode(', ', $tindakan) : $row->tindakan_servis;
                    }
                    return '-';
                })
                ->editColumn('modal_sparepart', fn($row) => number_format($row->modal_sparepart))
                ->editColumn('biaya', fn($row) => number_format($row->biaya))
                ->editColumn('diskon', fn($row) => number_format($row->diskon))
                ->editColumn('profittoko', fn($row) => number_format($row->profittoko))
                ->rawColumns(['teknisi'])
                ->make(true);
        }

        // --- Logika Ringkasan Card (Tetap Sama) ---
        $omzethari = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereDate('tgl_disetujui', today())->sum('omzet');
        $profithari = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereDate('tgl_disetujui', today())->sum('profittoko');
        $total_hari = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereDate('tgl_disetujui', today())->count();

        $omzetbulan = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->sum('omzet');
        $profitbulan = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->sum('profittoko');
        $total_bulan = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->count();

        $omzettahun = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->sum('omzet');
        $profittahun = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->sum('profittoko');
        $total_tahun = ServiceTransaction::where('is_approve', 'Setuju')->where('cabang_id', getCabangId())->whereYear('tgl_disetujui', $currentYear)->count();

        $jumlah_total = ServiceTransaction::where('cabang_id', getCabangId())->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->count();

        return view('pages/kepalatoko/laporan-servis', compact(
            'omzethari', 'profithari', 'total_hari',
            'omzetbulan', 'profitbulan', 'total_bulan',
            'omzettahun', 'profittahun', 'total_tahun',
            'jumlah_total'
        ));
    }

    public function indexPajak(Request $request)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $cabang_id = getCabangId();

        // === LOGIKA AJAX DATATABLES ===
        if ($request->ajax()) {
            $data = ServiceTransaction::with('user')
                ->where('cabang_id', $cabang_id)
                ->where('ppn', '>', 0)
                ->where('is_approve', 'Setuju')
                ->whereIn('kondisi_servis', ['Sudah jadi', 'Tidak bisa', 'Dibatalkan'])
                ->orderBy('tgl_disetujui', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tgl_disetujui', function($row) {
                    return $row->tgl_disetujui ? Carbon::parse($row->tgl_disetujui)->translatedFormat('d F Y') : '-';
                })
                ->editColumn('tgl_ambil', function($row) {
                    return $row->tgl_ambil ? Carbon::parse($row->tgl_ambil)->translatedFormat('d F Y') : '-';
                })
                ->addColumn('teknisi', function($row) {
                    if ($row->user) {
                        return '<div class="font-medium">' . $row->user->name . '</div>';
                    }
                    return '<div class="font-medium text-red-600">Akun sudah dihapus</div>';
                })
                ->editColumn('tindakan_servis', function($row) {
                    if ($row->tindakan_servis) {
                        $tindakan = json_decode($row->tindakan_servis);
                        return is_array($tindakan) ? implode(', ', $tindakan) : $row->tindakan_servis;
                    }
                    return '-';
                })
                ->editColumn('modal_sparepart', fn($row) => number_format($row->modal_sparepart))
                ->editColumn('biaya', fn($row) => number_format($row->biaya))
                ->editColumn('diskon', fn($row) => number_format($row->diskon))
                ->addColumn('pajak_display', function($row) {
                    $ppnRate = !empty($row->ppn) ? $row->ppn : 0;
                    $pajak = $row->biaya * ($ppnRate / 100);
                    return number_format($pajak);
                })
                ->addColumn('grand_total_display', function($row) {
                    $ppnRate = !empty($row->ppn) ? $row->ppn : 0;
                    $pajak = $row->biaya * ($ppnRate / 100);
                    return number_format($row->biaya + $pajak);
                })
                ->rawColumns(['teknisi'])
                ->make(true);
        }

        // === LOGIKA RINGKASAN CARD (SESUAI FLOW ASLI ANDA) ===
        $omzethari = ServiceTransaction::where('is_approve', 'Setuju')->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->where('cabang_id', $cabang_id)->whereDate('tgl_disetujui', today())->where('ppn', '>', 0)->sum('omzet');

        $pajakhari = ServiceTransaction::where('is_approve', 'Setuju')->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->where('cabang_id', $cabang_id)->whereDate('tgl_disetujui', today())->get()->sum(function ($trx) {
            return $trx->biaya * (!empty($trx->ppn) ? $trx->ppn : 0) / 100;
        });

        $omzetbulan = ServiceTransaction::where('is_approve', 'Setuju')->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->where('cabang_id', $cabang_id)->where('ppn', '>', 0)->sum('omzet');

        $pajakbulan = ServiceTransaction::where('is_approve', 'Setuju')->whereYear('tgl_disetujui', $currentYear)->whereMonth('tgl_disetujui', $currentMonth)->where('cabang_id', $cabang_id)->get()->sum(function ($trx) {
            return $trx->biaya * (!empty($trx->ppn) ? $trx->ppn : 0) / 100;
        });

        $omzettahun = ServiceTransaction::where('is_approve', 'Setuju')->whereYear('tgl_disetujui', $currentYear)->where('cabang_id', $cabang_id)->where('ppn', '>', 0)->sum('omzet');

        $pajaktahun = ServiceTransaction::where('is_approve', 'Setuju')->whereYear('tgl_disetujui', $currentYear)->where('cabang_id', $cabang_id)->get()->sum(function ($trx) {
            return $trx->biaya * (!empty($trx->ppn) ? $trx->ppn : 0) / 100;
        });

        // Menghitung jumlah untuk header tabel (Sudah jadi & PPN > 0)
        $jumlah_total = ServiceTransaction::where('cabang_id', $cabang_id)->where('ppn', '>', 0)->where('is_approve', 'Setuju')->where('kondisi_servis', "Sudah jadi")->count();

        return view('pages/kepalatoko/laporan-pajak-servis', compact('omzethari', 'pajakhari', 'omzetbulan', 'pajakbulan', 'omzettahun', 'pajaktahun', 'jumlah_total'));
    }

    // public function cetak(Request $request)
    // {
    //     // Mengambil logo dan nama toko
    //     $users = User::find(1);

    //     $logo = $users->profile_photo_path;
    //     $imagePath = public_path('storage/' . $logo);

    //     // Filter tanggal
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     // Mengambil data servis
    //     $services = ServiceTransaction::with('brand', 'modelserie', 'user','teknisi_tambahan')->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();
    //     // dd($services);


    //     // Menghitung total item servis
    //     $daftar_servis = ServiceTransaction::select('tindakan_servis')->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();

    //     // $total_servis = 0;
    //     // foreach ($daftar_servis as $v) {
    //     //     $json = json_decode($v['tindakan_servis']) ? json_decode($v['tindakan_servis']) : [];
    //     //     $total_servis += count($json) == 0 ? 1 : count($json);
    //     // }

    //     // Menghitung total pembayaran tunai
    //     $total_tunai = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('tunai');

    //     $total_dp = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('uang_muka');

    //     // Menghitung total pembayaran transfer
    //     $total_transfer = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('transfer');

    //     // Menghitung total pembayaran kredit
    //     $total_kredit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('due');

    //     // Mengambil data insiden
    //     $incidents = Incident::with('worker')->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Mengambil data pengeluaran
    //     $expenses = Expense::with('user')->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Mengambil data brand terbanyak
    //     $topbrands =
    //         ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_disetujui', '>=', $start_date)
    //         ->whereDate('tgl_disetujui', '<=', $end_date)
    //         ->where('service_transactions.cabang_id',getCabangId())
    //         ->select('brands.name as brand_name')
    //         ->join('brands', 'service_transactions.brands_id', '=', 'brands.id')
    //         ->groupBy('brand_name')
    //         ->orderBy(DB::raw('COUNT(*)'), 'desc')
    //         ->limit(3)
    //         ->get();

    //     // Mengambil data model seri terbanyak
    //     $topmodelseries =
    //         ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_disetujui', '>=', $start_date)
    //         ->whereDate('tgl_disetujui', '<=', $end_date)
    //         ->where('service_transactions.cabang_id',getCabangId())
    //         ->select('model_series.name as model_name')
    //         ->join('model_series', 'service_transactions.model_series_id', '=', 'model_series.id')
    //         ->groupBy('model_name')
    //         ->orderBy(DB::raw('COUNT(*)'), 'desc')
    //         ->limit(3)
    //         ->get();

    //     // Mengambil data model seri terbanyak
    //     $topactions =
    //         ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_disetujui', '>=', $start_date)
    //         ->whereDate('tgl_disetujui', '<=', $end_date)
    //         ->where('service_transactions.cabang_id',getCabangId())
    //         ->select('service_actions.nama_tindakan as action_name')
    //         ->join('service_actions', 'service_transactions.service_actions_id', '=', 'service_actions.id')
    //         ->groupBy('action_name')
    //         ->orderBy(DB::raw('COUNT(*)'), 'desc')
    //         ->limit(3)
    //         ->get();

    //     // Menghitung total modal
    //     $total_modal = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //     // ->where('is_approve', 'Setuju')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('modal_sparepart');

    //     // Menghitung total biaya
    //     $total_biaya = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //     // ->where('is_approve', 'Setuju')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('biaya');

    //     // Menghitung total diskon
    //     $total_diskon = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //     // ->where('is_approve', 'Setuju')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('diskon');

    //     // Menghitung total profit
    //     $total_profit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
    //     // ->where('is_approve', 'Setuju')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('profittoko');

    //     // Menghitung total insiden
    //     $total_insiden = Incident::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('biaya_toko');

    //     // Menghitung total pengeluaran
    //     $total_pengeluaran = Expense::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->sum('price');

    //     $servicesDP = ServiceTransaction::with('brand', 'modelserie', 'user')->where('status_servis', 'Sudah Diambil')
    //         ->whereDate('tgl_ambil', '>=', $start_date)
    //         ->whereDate('tgl_ambil', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->whereNotNull('uang_muka')
    //         ->where('uang_muka','!=','0')
    //         ->orderBy('tgl_ambil', 'asc')
    //         ->get();

    //     $totalInsiden = Incident::where('cabang_id',getCabangId())
    //         ->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->get()
    //         ->sum('biaya_toko');

    //     $total_servis = $services->count();
    //     $saldo_akhir = $total_profit - $total_pengeluaran - $totalInsiden;

    //     $pengeluaran_data = Expense::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->where('cabang_id',getCabangId())
    //         ->get();

    //     $insiden = Incident::where('cabang_id',getCabangId())
    //         ->whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->get();

    //     // return response()->json($services);
    //     $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-servis', [
    //     // return view('pages.kepalatoko.cetak-laporan-servis', [
    //         'users' => $users,
    //         'imagePath' => $imagePath,
    //         'services' => $services,
    //         'servicesDP' => $servicesDP,
    //         'incidents' => $incidents,
    //         'expenses' => $expenses,
    //         'start_date' => $start_date,
    //         'end_date' => $end_date,
    //         'total_modal' => $total_modal,
    //         'total_biaya' => $total_biaya,
    //         'total_diskon' => $total_diskon,
    //         'total_profit' => $total_profit,
    //         'total_insiden' => $totalInsiden,
    //         'total_pengeluaran' => $total_pengeluaran,
    //         'topbrands' => $topbrands,
    //         'topmodelseries' => $topmodelseries,
    //         'topactions' => $topactions,
    //         'total_servis' => $total_servis,
    //         'total_tunai' => $total_tunai,
    //         'total_transfer' => $total_transfer,
    //         'total_kredit' => $total_kredit,
    //         'saldo_akhir' => $saldo_akhir,
    //         'pengeluaran_data' => $pengeluaran_data,
    //         'total_dp' => $total_dp,
    //         'totalInsiden' => $totalInsiden,
    //         'insiden' => $insiden,
    //     ]);

    //     $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

    //     return $pdf->stream($filename);
    // }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter Input
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $tipe = $request->tipe; // Ambil input tipe dari form

        // --- LOGIC UTAMA (BASE QUERY) ---
        // Kita buat query dasar dulu agar tidak perlu if-else di setiap variabel
        $serviceQuery = ServiceTransaction::where('cabang_id', getCabangId());

        // Variable untuk sorting nanti
        $dateColumn = 'tgl_ambil';

        if ($tipe == 'Sudah Disetujui') {
            // REQUEST USER: Tambah where is_approve dan ganti tanggal jadi tgl_disetujui
            $serviceQuery->where('is_approve', 'Setuju')
                        ->whereDate('tgl_disetujui', '>=', $start_date)
                        ->whereDate('tgl_disetujui', '<=', $end_date);

            $dateColumn = 'tgl_disetujui'; // Set kolom tanggal untuk sorting
        } else {
            // DEFAULT: Sudah Diambil (Logic Lama)
            $serviceQuery->where('status_servis', 'Sudah Diambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);

            $dateColumn = 'tgl_ambil'; // Set kolom tanggal untuk sorting
        }

        // --- EKSEKUSI DATA (Menggunakan clone agar query dasar tidak berubah) ---

        // 1. Mengambil data list servis
        $services = (clone $serviceQuery)
            ->with('brand', 'modelserie', 'user', 'teknisi_tambahan')
            ->orderBy($dateColumn, 'asc')
            ->get();

        // 2. Menghitung total pembayaran (Tunai, DP, Transfer, Kredit)
        $total_tunai    = (clone $serviceQuery)->sum('tunai');
        $total_dp       = (clone $serviceQuery)->sum('uang_muka');
        $total_transfer = (clone $serviceQuery)->sum('transfer');
        $total_kredit   = (clone $serviceQuery)->sum('due');

        // 3. Menghitung Keuangan (Modal, Biaya, Diskon, Profit)
        $total_modal    = (clone $serviceQuery)->sum('modal_sparepart');
        $total_biaya    = (clone $serviceQuery)->sum('biaya');
        $total_diskon   = (clone $serviceQuery)->sum('diskon');
        $total_profit   = (clone $serviceQuery)->sum('profittoko');


        // 7. Data Services DP (Khusus yg ada DP)
        $servicesDP = (clone $serviceQuery)
            ->with('brand', 'modelserie', 'user')
            ->whereNotNull('uang_muka')
            ->where('uang_muka', '!=', '0')
            ->orderBy($dateColumn, 'asc')
            ->get();

        $total_servis = $services->count();


        // --- DATA NON-SERVIS (Incidents & Expenses) ---
        // Ini tetap menggunakan created_at karena tidak berhubungan langsung dengan status servis

        $incidents = Incident::with('worker')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'asc')
            ->get();

        $expensesToko = Expense::with('user')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->where('tipe', 0)
            ->orderBy('created_at', 'asc')
            ->get();
        $expensesServis = Expense::with('user')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->where('tipe', 1)
            ->orderBy('created_at', 'asc')
            ->get();
        $expensesServisProduk = Expense::with('user')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id', getCabangId())
            ->where('tipe', 2)
            ->orderBy('created_at', 'asc')
            ->get();

        $total_insiden = $incidents->sum('biaya_toko');
        $total_pengeluaran_toko = $expensesToko->sum('price');
        $total_pengeluaran_servis = $expensesServis->sum('price');
        $total_pengeluaran_produk = $expensesServisProduk->sum('price');

        // Hitung Saldo Akhir
        $saldo_akhir = $total_profit - $total_pengeluaran_toko - $total_pengeluaran_servis - $total_pengeluaran_produk - $total_insiden;
        // --- RETURN PDF ---
        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-servis', [
            'users'             => $users,
            'imagePath'         => $imagePath,
            'services'          => $services,
            'servicesDP'        => $servicesDP,
            'incidents'         => $incidents,
            'start_date'        => $start_date,
            'end_date'          => $end_date,
            'total_modal'       => $total_modal,
            'total_biaya'       => $total_biaya,
            'total_diskon'      => $total_diskon,
            'total_profit'      => $total_profit,
            'total_insiden'     => $total_insiden,
            'total_pengeluaran_toko' => $total_pengeluaran_toko,
            'total_pengeluaran_servis' => $total_pengeluaran_servis,
            'total_pengeluaran_produk' => $total_pengeluaran_produk,
            // 'topbrands'         => $topbrands,
            // 'topmodelseries'    => $topmodelseries,
            // 'topactions'        => $topactions,
            'total_servis'      => $total_servis,
            'total_tunai'       => $total_tunai,
            'total_transfer'    => $total_transfer,
            'total_kredit'      => $total_kredit,
            'saldo_akhir'       => $saldo_akhir,
            'pengeluaran_data_toko'  => $expensesToko, // Bisa pakai variabel expenses yg sama
            'pengeluaran_data_servis'  => $expensesServis, // Bisa pakai variabel expenses yg sama
            'pengeluaran_data_produk'  => $expensesServisProduk, // Bisa pakai variabel expenses yg sama
            'total_dp'          => $total_dp,
            'totalInsiden'      => $total_insiden,
            'insiden'           => $incidents,
            'tipe_laporan'      => $tipe // Opsional: kirim ke view biar judulnya dinamis
        ]);

        $filename = 'Laporan Transaksi Servis (' . $tipe . ') ' . $start_date . ' sd ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
    public function cetakPajak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data servis
        $services = ServiceTransaction::with('brand', 'modelserie', 'user')->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->orderBy('tgl_ambil', 'asc')
            ->where('ppn','>',0)
            ->get();
        // dd($services);


        // Menghitung total item servis
        $daftar_servis = ServiceTransaction::select('tindakan_servis')->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->orderBy('tgl_ambil', 'asc')
            ->get();

        $total_servis = 0;
        foreach ($daftar_servis as $v) {
            $json = json_decode($v['tindakan_servis']) ? json_decode($v['tindakan_servis']) : [];
            $total_servis += count($json) == 0 ? 1 : count($json);
        }

        // Menghitung total pembayaran tunai
        $total_tunai = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('tunai');

        $total_dp = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('uang_muka');

        // Menghitung total pembayaran transfer
        $total_transfer = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('transfer');

        // Menghitung total pembayaran kredit
        $total_kredit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('due');

        // Mengambil data insiden
        $incidents = Incident::with('worker')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->orderBy('created_at', 'asc')
            ->get();

        // Mengambil data pengeluaran
        $expenses = Expense::with('user')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->orderBy('created_at', 'asc')
            ->get();

        // Mengambil data brand terbanyak
        $topbrands =
            ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->select('brands.name as brand_name')
            ->join('brands', 'service_transactions.brands_id', '=', 'brands.id')
            ->groupBy('brand_name')
            ->orderBy(DB::raw('COUNT(*)'), 'desc')
            ->limit(3)
            ->get();

        // Mengambil data model seri terbanyak
        $topmodelseries =
            ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->select('model_series.name as model_name')
            ->join('model_series', 'service_transactions.model_series_id', '=', 'model_series.id')
            ->groupBy('model_name')
            ->orderBy(DB::raw('COUNT(*)'), 'desc')
            ->limit(3)
            ->get();

        // Mengambil data model seri terbanyak
        $topactions =
            ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->select('service_actions.nama_tindakan as action_name')
            ->join('service_actions', 'service_transactions.service_actions_id', '=', 'service_actions.id')
            ->groupBy('action_name')
            ->orderBy(DB::raw('COUNT(*)'), 'desc')
            ->limit(3)
            ->get();

        // Menghitung total modal
        $total_modal = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('modal_sparepart');

        // Menghitung total biaya
        $total_biaya = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('biaya');

        // Menghitung total diskon
        $total_diskon = ServiceTransaction::where('is_approve', 'Setuju')
            ->where('kondisi_servis', "Sudah jadi")
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('diskon');

        // Menghitung total profit
        $total_profit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('profit');

        // Menghitung total insiden
        $total_insiden = Incident::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('biaya_toko');

        // Menghitung total pengeluaran
        $total_pengeluaran = Expense::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->sum('price');

        $servicesDP = ServiceTransaction::with('brand', 'modelserie', 'user')->where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', '>=', $start_date)
            ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
            ->whereNotNull('uang_muka')
            ->where('uang_muka','!=','0')
            ->orderBy('tgl_ambil', 'asc')
            ->where('ppn','>',0)
            ->get();

        $pajak = ServiceTransaction::with('serviceaction')
        ->where('is_approve', 'Setuju')
        ->whereDate('tgl_ambil', '>=', $start_date)
        ->whereDate('tgl_ambil', '<=', $end_date)
            ->where('cabang_id',getCabangId())
        ->get()
        ->sum(function ($trx) {
            $ppn = !empty($trx->ppn) ? $trx->ppn : 0;
            return $trx->biaya * $ppn / 100; // hanya ambil nilai PPN
        });
            // return response()->json([$services,$pajak]);
        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-pajak-servis', [
        // return view('pages.kepalatoko.cetak-laporan-pajak-servis', [
            'users' => $users,
            'imagePath' => $imagePath,
            'services' => $services,
            'servicesDP' => $servicesDP,
            'incidents' => $incidents,
            'expenses' => $expenses,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_modal' => $total_modal,
            'total_biaya' => $total_biaya,
            'total_diskon' => $total_diskon,
            'total_profit' => $total_profit,
            'total_insiden' => $total_insiden,
            'total_pengeluaran' => $total_pengeluaran,
            'topbrands' => $topbrands,
            'topmodelseries' => $topmodelseries,
            'topactions' => $topactions,
            'total_servis' => $total_servis,
            'total_tunai' => $total_tunai,
            'total_transfer' => $total_transfer,
            'total_kredit' => $total_kredit,
            'pajak' => $pajak,
            'total_dp' => $total_dp
        ]);

        $filename = 'Laporan Pajak Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }
}
