<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expenses = Expense::latest()->paginate(10);
        $expenses_count = Expense::all()->count();
        $users = User::all();
        return view('pages/admintoko/pengeluaran/index', compact('expenses', 'expenses_count', 'users'));
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
            'users_id' => $request->users_id,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('admin-pengeluaran.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function show()
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
        $users = User::where('cabang_id',getCabangId())->get();

        return view('pages.admintoko.pengeluaran.edit', [
            'item' => $item,
            'users' => $users
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
            'created_at' => $request->created_at,
            'name' => $request->name,
            'price' => $request->price,
            'users_id' => $request->users_id,
        ]);

        return redirect()->route('admin-pengeluaran.index');
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

        return redirect()->route('admin-pengeluaran.index');
    }
}
