<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;

class TrackingController extends Controller
{
    public function index()
    {
        return view('pages.tracking');
    }

    public function data(Request $request)
    {
        $nomor_hp = $request->input('nomor_hp');

        if (!$nomor_hp) {
            return redirect()->back()->with('error', 'Nomor HP wajib diisi');
        }

        $customers = Customer::where('nomor_hp', $nomor_hp)->first();

        // $services = ServiceTransaction::with(['customer', 'type', 'brand', 'modelserie', 'capacity', 'user'])
        $services = ServiceTransaction::with(['customer', 'type', 'brand', 'modelserie', 'capacity', 'user'])
            ->whereHas('customer', function ($query) use ($nomor_hp) {
                $query->where('nomor_hp', $nomor_hp);
            })
            ->orderByDesc('created_at')
            ->get();
        // dd($services);

        $totalbiaya = ServiceTransaction::whereHas('customer', function ($query) use ($nomor_hp) {
            $query->where('nomor_hp', $nomor_hp);
        })->sum('biaya');

        $users = User::find(1);

        return view('pages.tracking-data', compact('services', 'totalbiaya', 'customers', 'users'));
    }
}
