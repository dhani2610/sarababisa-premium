<?php

namespace App\Http\Controllers\Teknisi;

use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expenses = Expense::where('users_id', Auth::user()->id)->latest()->paginate(10);
        $expenses_count = Expense::where('users_id', Auth::user()->id)->count();
        return view('pages/teknisi/pengeluaran/index', compact('expenses', 'expenses_count'));
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
        // Transaction create
        Expense::create([
            'created_by'          => auth()->user()->id,
            'name' => $request->name,
            'price' => $request->price,
            'users_id' => Auth::user()->id,
        ]);

        return redirect()->route('teknisi-pengeluaran.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Expense::findOrFail($id);

        return view('pages.teknisi.pengeluaran.edit', [
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = Expense::findOrFail($id);

        // Transaction update
        $item->update([
            'name' => $request->name,
            'price' => $request->price,
            'created_at' => $request->created_at,
        ]);

        return redirect()->route('teknisi-pengeluaran.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Expense::findOrFail($id);

        $item->delete();

        return redirect()->route('teknisi-pengeluaran.index');
    }
}
