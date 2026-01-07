<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\ServiceTransaction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Incident;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class InvestorController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.investor.index');
    }

    public function getData(Request $request)
    {
        $start = $request->start_month ? Carbon::parse($request->start_month . '-01') : Carbon::now()->startOfYear();
        $end = $request->end_month ? Carbon::parse($request->end_month . '-01')->endOfMonth() : Carbon::now()->endOfMonth();

        // Buat periode bulanan untuk looping
        $period = CarbonPeriod::create($start, '1 month', $end);
        $dataCollection = collect([]);

        // 2. Looping per bulan dalam range
        foreach ($period as $date) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $cabangId = getCabangId(); // Helper function

            // --- A. DATA SERVIS (Status: Setuju) ---
            $serviceQuery = ServiceTransaction::where('cabang_id', $cabangId)
                ->where('is_approve', 'Setuju')
                ->whereBetween('tgl_disetujui', [$monthStart, $monthEnd]);

            $serviceCount  = (clone $serviceQuery)->count();
            $serviceOmset  = (clone $serviceQuery)->sum('biaya');
            $serviceModal  = (clone $serviceQuery)->sum('modal_sparepart');
            $serviceProfit = (clone $serviceQuery)->sum('profittoko');

            // --- B. DATA PENJUALAN PRODUK (Status: Setuju) ---
            // 1. Hitung Transaksi (Jumlah Order)
            $productCount = Order::where('cabang_id', $cabangId)
                ->where('is_approve', 'Setuju')
                ->whereBetween('tgl_disetujui', [$monthStart, $monthEnd])
                ->count();

            // 2. Hitung Nominal (Dari Order Detail)
            $productDetailsQuery = OrderDetail::where('cabang_id', $cabangId)
                ->whereHas('order', function ($q) use ($monthStart, $monthEnd) {
                    $q->where('is_approve', 'Setuju')
                    ->whereBetween('tgl_disetujui', [$monthStart, $monthEnd]);
                });

            $productOmset  = (clone $productDetailsQuery)->sum('total');
            $productModal  = (clone $productDetailsQuery)->sum('modal');
            $productProfit = (clone $productDetailsQuery)->sum('profit');

            // --- C. PENGELUARAN (Expense & Insiden) ---
            // Menggunakan created_at sesuai referensi cetak
            $operasional = Expense::where('cabang_id', $cabangId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('price');

            $insiden = Incident::where('cabang_id', $cabangId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('biaya_toko');

            // --- D. AGREGASI DATA ---
            $totalOmset   = $serviceOmset + $productOmset;
            $totalModal   = $serviceModal + $productModal;
            $grossProfit  = $serviceProfit + $productProfit; // Profit Kotor

            // Sisa Profit (Net Profit) = Profit Kotor - Operasional - Insiden
            $sisaProfit   = $grossProfit - $operasional - $insiden;

            // Target (Dummy 10jt)

            $target = Budget::all()->sum('total');

            // Hitung Result Persentase
            $resultPercentage = $target > 0 ? ($sisaProfit / $target) * 100 : 0;

            // Push ke collection
            $dataCollection->push([
                'bulan_raw'   => $date->format('Y-m'),
                'bulan'       => $date->translatedFormat('M Y'), // Contoh: Sep 2023
                'transaksi'   => $serviceCount + $productCount,
                'omset'       => $totalOmset,
                'modal'       => $totalModal,
                'profit'      => $grossProfit,
                'operasional' => $operasional,
                'insiden'     => $insiden,
                'sisa_profit' => $sisaProfit,
                'target'      => $target,
                'result'      => round($resultPercentage, 1) // 1 desimal
            ]);
        }

        // 3. Hitung Total Keseluruhan untuk Footer
        $totalSisaProfit = $dataCollection->sum('sisa_profit');

        $userInvestor = User::where('cabang_id', getCabangId())->where('role', 'Investor')->first();
        if ($userInvestor) {
            $persenInvestor = $userInvestor->persen_investor ?? 0;
        } else {
            $persenInvestor = 0;
        }

        $pembagiPersen = 100 - $persenInvestor;

        $shareHF       = $totalSisaProfit * ($pembagiPersen / 100);
        $shareInvestor = $totalSisaProfit * ($persenInvestor / 100);

        // 4. Return Yajra DataTables
        return DataTables::of($dataCollection)
            ->addIndexColumn()
            ->editColumn('omset', fn($row) => 'Rp ' . number_format($row['omset'], 0, ',', '.'))
            ->editColumn('modal', fn($row) => 'Rp ' . number_format($row['modal'], 0, ',', '.'))
            ->editColumn('profit', fn($row) => 'Rp ' . number_format($row['profit'], 0, ',', '.'))
            ->editColumn('operasional', fn($row) => 'Rp ' . number_format($row['operasional'], 0, ',', '.'))
            ->editColumn('insiden', fn($row) => $row['insiden'] == 0 ? '-' : 'Rp ' . number_format($row['insiden'], 0, ',', '.'))
            ->editColumn('sisa_profit', fn($row) => 'Rp ' . number_format($row['sisa_profit'], 0, ',', '.'))
            ->editColumn('target', fn($row) => 'Rp ' . number_format($row['target'], 0, ',', '.'))
            ->editColumn('result', fn($row) => $row['result'] . '%')

            // Kirim data totalan ke JSON response agar bisa diambil JS Footer


            ->with([
                'total_transaksi'   => $dataCollection->sum('transaksi'),
                'total_omset'       => number_format($dataCollection->sum('omset'), 0, ',', '.'),
                'total_modal'       => number_format($dataCollection->sum('modal'), 0, ',', '.'),
                'total_profit'      => number_format($dataCollection->sum('profit'), 0, ',', '.'),
                'total_operasional' => number_format($dataCollection->sum('operasional'), 0, ',', '.'),
                'total_insiden'     => number_format($dataCollection->sum('insiden'), 0, ',', '.'),
                'total_sisa_profit' => number_format($totalSisaProfit, 0, ',', '.'),
                // Hitung bagi hasil
                'share_hf'          => number_format($shareHF, 0, ',', '.'), // 60%
                'share_investor'    => number_format($shareInvestor, 0, ',', '.'), // 40%
                'pembagiPersen'     => $pembagiPersen, // 40%
                'persenInvestor'     => $persenInvestor, // 40%
            ])
            ->make(true);
    }


}
