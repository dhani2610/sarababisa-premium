<?php

namespace App\Http\Controllers\KepalaToko;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Type;
use App\Models\Order;
use App\Models\Budget;
use App\Models\Target;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Category;
use App\Models\Incident;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Purchase;
use App\Models\Cabang;
use App\Models\Refund;
use Illuminate\Http\Request;

class DashboardCabangController extends Controller
{

    public function index()
    {
        return view('pages/kepalatoko/dashboard-cabang');
    }

    public function getJsonChart(Request $request)
    {
        $cabang = Cabang::orderBy('id','asc')->get();

        // RANGE YANG DIPILIH USER
        $start = $request->start_month_year;   // example: "2024-01"
        $end   = $request->end_month_year;     // example: "2024-03"

        // Jika tidak ada input → default bulan ini
        if (!$start || !$end) {
            $start = now()->format('Y-m');
            $end   = now()->format('Y-m');
        }

        // AMBIL LIST BULAN DIANTARA RANGE
        $startDate = \Carbon\Carbon::parse($start . '-01');
        $endDate   = \Carbon\Carbon::parse($end . '-01');

        $listMonths = [];
        $listMonthsIndo = [];

        $indoMonths = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        while ($startDate <= $endDate) {

            // Format Y-m (misal 2025-01)
            $listMonths[] = $startDate->format('Y-m');

            // Format Indo: Jan 25
            $bulan = $indoMonths[(int)$startDate->format('n')];
            $tahun2 = $startDate->format('y');
            $listMonthsIndo[] = $bulan . ' ' . $tahun2;

            $startDate->addMonth();
        }


        // HASIL AKHIR
        $dataAnggaran = [];

        // =========================
        // LOOP PER CABANG ANGGARAN
        // =========================
        foreach ($cabang as $cab) {

            // Loop per bulan
            foreach ($listMonths as $ym) {

                $total = Budget::where('cabang_id', $cab->id)
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$ym])
                    ->sum('total');

                // push ke JSON:
                $dataAnggaran[$cab->nama_cabang][$ym] = $total;
            }
        }

       // HASIL AKHIR
        $dataPencapaian = [];

        // =========================
        // LOOP PER CABANG ALL OMSET
        // =========================
        foreach ($cabang as $cab) {

            // Loop per bulan
            foreach ($listMonths as $ym) {

                // Ambil year & month dari list bulan
                [$year, $month] = explode('-', $ym);

                // ======================
                // PROFIT BERSIH SERVIS
                // ======================
                $bulanprofitbersihservis = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('omzet');

                // ======================
                // PROFIT BERSIH PENJUALAN
                // ======================
                $profitpenjualan = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->whereHas('detailOrders', function ($q) use ($year, $month) {
                        $q->where('is_approve', 'Setuju');
                    })
                    ->with(['detailOrders' => function ($q) use ($year, $month) {
                        $q->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                        ->groupBy('orders_id');
                    }])
                    ->get();

                $bulanprofitbersihpenjualan = $profitpenjualan->sum(
                    fn($order) => $order->detailOrders->sum('total_omzet')
                );

                $bulantotalprofitbersih = $bulanprofitbersihservis + $bulanprofitbersihpenjualan;

                // ======================
                // PUSH KE JSON
                // ======================
                $dataPencapaian[$cab->nama_cabang][$ym] =$bulantotalprofitbersih;
            }
        }

       // HASIL AKHIR
        $dataOmset = [];

        // =========================
        // LOOP PER CABANG ALL OMSET
        // =========================
        foreach ($cabang as $cab) {

            // Loop per bulan
            foreach ($listMonths as $ym) {

                // Ambil year & month dari list bulan
                [$year, $month] = explode('-', $ym);

                // ======================
                // PROFIT BERSIH SERVIS
                // ======================
                $bulanprofitbersihservis = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('omzet');

                // ======================
                // PROFIT BERSIH PENJUALAN
                // ======================
                $profitpenjualan = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->whereHas('detailOrders', function ($q) use ($year, $month) {
                        $q->where('is_approve', 'Setuju');
                    })
                    ->with(['detailOrders' => function ($q) use ($year, $month) {
                        $q->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                        ->groupBy('orders_id');
                    }])
                    ->get();

                $bulanprofitbersihpenjualan = $profitpenjualan->sum(
                    fn($order) => $order->detailOrders->sum('total_omzet')
                );

                $bulantotalprofitbersih = $bulanprofitbersihservis + $bulanprofitbersihpenjualan;

                // ======================
                // PROFIT KOTOR SERVIS
                // ======================
                $bulanprofitkotorservis = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('omzet');

                // ======================
                // PROFIT KOTOR PENJUALAN
                // ======================
                $bulanprofitkotorpenjualan = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->with(['detailOrders' => function ($q) use ($year, $month) {
                        $q->select('orders_id', DB::raw('SUM(total) as total_omzet'))
                        ->groupBy('orders_id');
                    }])
                    ->get()
                    ->sum(fn($order) => $order->detailOrders->sum('total_omzet'));

                $bulantotalprofitkotor = $bulanprofitkotorservis + $bulanprofitkotorpenjualan;

                // ======================
                // OMSET BULANAN
                // ======================
                $totalOmsetBulanIni = Budget::where('cabang_id', $cab->id)
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$ym])
                    ->sum('total');

                // ======================
                // PUSH KE JSON
                // ======================
                $dataOmset[$cab->nama_cabang][$ym] = [
                    'omset'               => $totalOmsetBulanIni,
                ];
            }
        }

       // HASIL AKHIR
        $dataOmsetService = [];

        // =========================
        // LOOP PER CABANG OMSET SERVIS
        // =========================
        foreach ($cabang as $cab) {

            // Loop per bulan
            foreach ($listMonths as $ym) {

                // Ambil year & month dari list bulan
                [$year, $month] = explode('-', $ym);

                // ======================
                // PROFIT BERSIH SERVIS
                // ======================
                $omset = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('omzet');


                // ======================
                // PUSH KE JSON
                // ======================
                $dataOmsetService[$cab->nama_cabang][$ym] = [
                    'omset'               => $omset,
                ];
            }
        }


       $dataOmsetProduk = [];

        // =========================
        // LOOP PER CABANG OMZET PRODUK
        // =========================
        foreach ($cabang as $cab) {

            foreach ($listMonths as $ym) {

                [$year, $month] = explode('-', $ym);

                // ======================
                // AMBIL ORDER + DETAIL + PRODUK
                // ======================
                $orders = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->with([
                        'detailOrders.product' => function ($q) {
                            $q->select('id', 'categories_id');
                        }
                    ])
                    ->get();

                // ======================
                // SIAPKAN TEMPAT PENAMPUNG
                // ======================
                $kategoriData = [
                    1 => ['nama' => 'Handphone', 'total_omset' => 0],
                    2 => ['nama' => 'Sparepart', 'total_omset' => 0],
                    3 => ['nama' => 'Aksesoris', 'total_omset' => 0],
                    4 => ['nama' => 'Tools', 'total_omset' => 0],
                ];

                // ======================
                // LOOP DETAIL ORDER
                // ======================
                foreach ($orders as $ord) {

                    foreach ($ord->detailOrders as $detail) {

                        $catId = $detail->product->categories_id ?? 4; // default Tools bila tidak ada

                        // Tambahkan data detail-nya
                        // $kategoriData[$catId]['data'][] = $detail;

                        // OMSET (gunakan sub_total order)
                        // $kategoriData[$catId]['total_omset'] += $ord->sub_total;

                        // PROFIT
                        $kategoriData[$catId]['total_omset'] += $detail->sub_total;
                    }
                }

                // ======================
                // INPUT KE JSON BESAR
                // ======================
                $dataOmsetProduk[$cab->nama_cabang][$ym] = $kategoriData;
            }
        }

        // ===============================PROFIT=================================
         // HASIL AKHIR
        $dataProfit = [];

        // =========================
        // LOOP PER CABANG ALL PROFIT
        // =========================
        foreach ($cabang as $cab) {

            // Loop per bulan
            foreach ($listMonths as $ym) {

                // Ambil year & month dari list bulan
                [$year, $month] = explode('-', $ym);

                // ======================
                // PROFIT BERSIH SERVIS
                // ======================
                $bulanprofitbersihservis = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('profittoko');

                // ======================
                // PROFIT BERSIH PENJUALAN
                // ======================
                $profitpenjualan = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->whereHas('detailOrders', function ($q) use ($year, $month) {
                        $q->where('is_approve', 'Setuju');
                    })
                    ->with(['detailOrders' => function ($q) use ($year, $month) {
                        $q->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                        ->groupBy('orders_id');
                    }])
                    ->get();

                $bulanprofitbersihpenjualan = $profitpenjualan->sum(
                    fn($order) => $order->detailOrders->sum('total_profit')
                );

                $bulantotalprofitbersih = $bulanprofitbersihservis + $bulanprofitbersihpenjualan;

                // ======================
                // PROFIT KOTOR SERVIS
                // ======================
                $bulanprofitkotorservis = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('profit');

                // ======================
                // PROFIT KOTOR PENJUALAN
                // ======================
                $bulanprofitkotorpenjualan = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->with(['detailOrders' => function ($q) use ($year, $month) {
                        $q->select('orders_id', DB::raw('SUM(profit) as total_profit'))
                        ->groupBy('orders_id');
                    }])
                    ->get()
                    ->sum(fn($order) => $order->detailOrders->sum('total_profit'));

                $bulantotalprofitkotor = $bulanprofitkotorservis + $bulanprofitkotorpenjualan;

                // ======================
                // OMSET BULANAN
                // ======================
                $totalOmsetBulanIni = Budget::where('cabang_id', $cab->id)
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$ym])
                    ->sum('total');

                // ======================
                // PUSH KE JSON
                // ======================
                $dataProfit[$cab->nama_cabang][$ym] = [
                    'profit'               => $totalOmsetBulanIni,
                ];
            }
        }


        // HASIL AKHIR
        $dataProfitService = [];

        // =========================
        // LOOP PER CABANG PROFIT SERVIS
        // =========================
        foreach ($cabang as $cab) {

            // Loop per bulan
            foreach ($listMonths as $ym) {

                // Ambil year & month dari list bulan
                [$year, $month] = explode('-', $ym);

                // ======================
                // PROFIT BERSIH SERVIS
                // ======================
                $omset = ServiceTransaction::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->where('is_approve', 'Setuju')
                    ->sum('profittoko');


                // ======================
                // PUSH KE JSON
                // ======================
                $dataProfitService[$cab->nama_cabang][$ym] = [
                    'omset'               => $omset,
                ];
            }
        }



       $dataProdukProduk = [];

        // =========================
        // LOOP PER CABANG PROFIT PRODUK
        // =========================
        foreach ($cabang as $cab) {

            foreach ($listMonths as $ym) {

                [$year, $month] = explode('-', $ym);

                // ======================
                // AMBIL ORDER + DETAIL + PRODUK
                // ======================
                $orders = Order::where('cabang_id', $cab->id)
                    ->whereYear('tgl_disetujui', $year)
                    ->whereMonth('tgl_disetujui', $month)
                    ->with([
                        'detailOrders.product' => function ($q) {
                            $q->select('id', 'categories_id');
                        }
                    ])
                    ->get();

                // ======================
                // SIAPKAN TEMPAT PENAMPUNG
                // ======================
                $kategoriData = [
                    1 => ['nama' => 'Handphone', 'total_profit' => 0],
                    2 => ['nama' => 'Sparepart', 'total_profit' => 0],
                    3 => ['nama' => 'Aksesoris', 'total_profit' => 0],
                    4 => ['nama' => 'Tools', 'total_profit' => 0],
                ];

                // ======================
                // LOOP DETAIL ORDER
                // ======================
                foreach ($orders as $ord) {

                    foreach ($ord->detailOrders as $detail) {

                        $catId = $detail->product->categories_id ?? 4; // default Tools bila tidak ada

                        // Tambahkan data detail-nya
                        // $kategoriData[$catId]['data'][] = $detail;

                        // OMSET (gunakan sub_total order)
                        // $kategoriData[$catId]['total_omset'] += $ord->sub_total;

                        // PROFIT
                        $kategoriData[$catId]['total_profit'] += $detail->profit_toko;
                    }
                }

                // ======================
                // INPUT KE JSON BESAR
                // ======================
                $dataProdukProduk[$cab->nama_cabang][$ym] = $kategoriData;
            }
        }

        // ===============================================================

        // HASIL AKHIR
        $dataPengeluaran = [];

        // =========================
        // LOOP PER CABANG PENGELUARAN
        // =========================
        foreach ($cabang as $cab) {


            // Loop per bulan
            foreach ($listMonths as $ym) {
                [$year, $month] = explode('-', $ym);
                $pengeluaran_data = Expense::whereMonth('tgl_disetujui',$month)
                    ->whereYear('tgl_disetujui',$year)
                    ->where('cabang_id',$cab->id)
                    ->where('is_approve','Setuju')
                    ->get()->sum('price');
                // push ke JSON:
                $dataPengeluaran[$cab->nama_cabang][$ym] = $pengeluaran_data;
            }
        }

        // HASIL AKHIR
        $dataInsiden = [];

        // =========================
        // LOOP PER CABANG PENGELUARAN
        // =========================
        foreach ($cabang as $cab) {


            // Loop per bulan
            foreach ($listMonths as $ym) {
                [$year, $month] = explode('-', $ym);
                $data = Incident::whereMonth('created_at',$month)
                    ->whereYear('created_at',$year)
                    ->where('cabang_id',$cab->id)
                    ->get()->sum('price');
                // push ke JSON:
                $dataInsiden[$cab->nama_cabang][$ym] = $data;
            }
        }

        // HASIL AKHIR
        $dataRefund = [];

        // =========================
        // LOOP PER CABANG PENGELUARAN
        // =========================
        foreach ($cabang as $cab) {


            // Loop per bulan
            foreach ($listMonths as $ym) {
                [$year, $month] = explode('-', $ym);
                $data = Refund::whereMonth('created_at',$month)
                    ->whereYear('created_at',$year)
                    ->where('cabang_id',$cab->id)
                    ->get()->sum('nominal_servis');
                // push ke JSON:
                $dataRefund[$cab->nama_cabang][$ym] = $data;
            }
        }

        return response()->json([
            'range' => $listMonths,
            'rangeIndo' => $listMonthsIndo,
            'dataPencapaian'  => $dataPencapaian,
            'dataAnggaran'  => $dataAnggaran,
            'dataOmset'  => $dataOmset,
            'dataOmsetService'  => $dataOmsetService,
            'dataOmsetProduk'  => $dataOmsetProduk,
            'dataProfit'  => $dataProfit,
            'dataProfitService'  => $dataProfitService,
            'dataProdukProduk'  => $dataProdukProduk,
            'dataPengeluaran'  => $dataPengeluaran,
            'dataInsiden'  => $dataInsiden,
            'dataRefund'  => $dataRefund,
        ]);

    }
}
