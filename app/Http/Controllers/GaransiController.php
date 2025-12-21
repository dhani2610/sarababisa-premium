<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\Phone;
use App\Models\PhoneTransaction;
use App\Models\TeknisiServis;
use Illuminate\Http\Request;
use App\Models\ServiceTransaction;

class GaransiController extends Controller
{
    public function index()
    {
        return view('pages/garansi');
    }

    public function data()
    {
        $product_transactions = OrderDetail::with('order', 'product')->whereHas('order', function ($order) {
            $order->where('invoice_no', $_GET['invoice_no']);
        })->get();
        $users = User::find(1);

        return view('pages/garansi-data', compact('product_transactions', 'users'));
    }

    public function indexServis()
    {
        return view('pages.garansi-servis');
    }

    public function dataServis(Request $request)
    {
        $invoice_no = $request->input('no_invoice');

        if (!$invoice_no) {
            return redirect()->back()->with('error', 'Nomor HP wajib diisi');
        }


        // $services = ServiceTransaction::with(['customer', 'type', 'brand', 'modelserie', 'capacity', 'user'])
        $item = ServiceTransaction::with(['customer', 'type', 'brand', 'modelserie', 'capacity', 'user'])
            ->where('nomor_servis',$invoice_no)
            ->orderByDesc('created_at')
            ->first();
        // dd($services);

        $totalbiaya = ServiceTransaction::where('nomor_servis',$invoice_no)->sum('biaya');

        $customers = Customer::find($item->customers_id);

        $users = User::find(1);

        if ($item->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$items->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            return redirect()->back()->with('error', 'Tidak ditemukan');
        }

        return view('pages.garansi-servis-data', compact('item', 'totalbiaya', 'users','customers'));
    }
    public function dataServisTeknisi($id)
    {
        $invoice_no = $id;

        if (!$invoice_no) {
            return redirect()->back()->with('error', 'Nomor HP wajib diisi');
        }


        // $services = ServiceTransaction::with(['customer', 'type', 'brand', 'modelserie', 'capacity', 'user'])
        $item = ServiceTransaction::with(['customer', 'type', 'brand', 'modelserie', 'capacity', 'user'])
            ->where('id',$invoice_no)
            ->orderByDesc('created_at')
            ->first();
        // dd($services);

        $totalbiaya = ServiceTransaction::where('id',$invoice_no)->sum('biaya');

        $customers = Customer::find($item->customers_id);
        $teknisiServis = TeknisiServis::where('service_transactions_id', $item->id)->get();
        // dd($teknisiServis);
        $users = User::find(1);

        if ($item->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$items->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            return redirect()->back()->with('error', 'Tidak ditemukan');
        }

        return view('pages.servis-data', compact('item', 'totalbiaya', 'users','customers','teknisiServis'));
    }
}
