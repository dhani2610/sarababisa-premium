<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Order;
use App\Models\Budget;
use App\Models\Target;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/target/index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        Target::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data target berhasil dihapus.']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $target = Budget::all()->sum('total');

        $bulanprofitbersihservis = ServiceTransaction::whereYear('tgl_ambil', now()->year)
            ->whereMonth('tgl_ambil', now()->month)
            ->where('cabang_id', getCabangId())
            ->where('is_approve', 'Setuju')
            ->get()
            ->sum('profittoko');

        $profitpenjualan = Order::whereHas('detailOrders', function ($query) {
            $query->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->where('cabang_id', getCabangId())
                ->where('is_approve', 'Setuju');
        })
            ->with(['detailOrders' => function ($query) {
                $query->where('cabang_id', getCabangId())->select('orders_id', DB::raw('SUM(profit_toko) as total_profit'))
                    ->groupBy('orders_id');
            }])
            ->select('id')
            ->get();

        $bulanprofitbersihpenjualan = $profitpenjualan->sum(function ($order) {
            return $order->detailOrders->sum('total_profit');
        });

        $bulantotalprofitbersih = ($bulanprofitbersihservis + $bulanprofitbersihpenjualan);

        $persen = round(($bulantotalprofitbersih / $target) * 100);

        Target::create([
            'target' => $target,
            'persen' => $persen,
            'nilai' => $bulantotalprofitbersih,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('target.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Target::findOrFail($id);

        $item->delete();

        return redirect()->route('target.index');
    }
}
