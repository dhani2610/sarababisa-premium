<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Order;
use App\Models\Budget;
use App\Models\Target;
use App\Models\SalesTarget;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TargetSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/target-sales/index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        SalesTarget::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data target sales berhasil dihapus.']);
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
        $sales_name = User::find($request->users_id);

        SalesTarget::create([
            'users_id' => $request->users_id,
            'item' => $request->item,
            'sales_name' => $sales_name->name,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('target-sales.index');
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
        $item = SalesTarget::findOrFail($id);
        $sales = User::forCabang()->where('role', 'Sales')->get();

        return view('pages.kepalatoko.target-sales.edit', [
            'item' => $item,
            'sales' => $sales,
        ]);
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
        $item = SalesTarget::findOrFail($id);

        $sales_name = User::find($request->users_id);

        $item->update([
            'users_id' => $request->users_id,
            'item' => $request->item,
            'sales_name' => $sales_name->name,
            'created_at' => $request->created_at,
        ]);

        return redirect()->route('target-sales.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = SalesTarget::findOrFail($id);

        $item->delete();

        return redirect()->route('target-sales.index');
    }
}
