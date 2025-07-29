<?php

namespace App\Http\Controllers\AdminToko;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\PelangganExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\CustomerRequest;
use App\Imports\PelangganImport;
use Illuminate\Support\Facades\Http;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::paginate(10);
        $customers_count = Customer::all()->count();
        return view('pages/admintoko/pelanggan', compact('customers', 'customers_count'));
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
    public function store(CustomerRequest $request)
    {
        //
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('PelangganData', $namafile);
        Excel::import(new PelangganImport, \public_path('/PelangganData/' . $namafile));
        return redirect()->route('admin-pelanggan.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new PelangganExport, 'data-pelanggan.xlsx');
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
        $item = Customer::findOrFail($id);

        return view('pages.admintoko.pelanggan-edit', [
            'item' => $item
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
        $data = $request->all();

        $item = Customer::findOrFail($id);

        $item->update($data);

        return redirect()->route('admin-pelanggan.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Customer::findOrFail($id);

        if (
            $item->servicetransaction()->exists() || $item->sale()->exists()
        ) {
            toast('Data Pelanggan yang memiliki riwayat transaksi servis/penjualan tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Pelanggan berhasil dihapus.', 'success');

        return redirect()->route('admin-pelanggan.index');
    }

    public function broadcast(Request $request)
    {
        $message = $request->input('message');

        if ($request->has('customers')) {
            $numbers = $request->input('customers', []);
        } else {
            // Kalau select kosong karena "Pilih Semua" dicentang
            $numbers = Customer::pluck('nomor_hp')->toArray();
        }

        foreach ($numbers as $phone) {
            // Convert ke format 62
            if (str_starts_with($phone, '08')) {
                $phone = '62' . substr($phone, 1);
            }

            $this->sendWhatsAppMessage($phone, $message);
        }

        return redirect()->back()->with('success', 'Pesan berhasil dikirim.');
    }

    private function sendWhatsAppMessage($phone, $message)
    {
        $token = env('TOKEN_FONNTE');
        $url = "https://api.fonnte.com/send";

        try {
            $response = Http::withHeaders([
                'Authorization' => "$token",
                'Content-Type' => 'application/json',
            ])->post($url, [
                'target' => $phone,
                'message' => $message,
            ]);

            $result = $response->json();
            if ($response->successful()) {
                \Log::info("Pesan WA berhasil dikirim ke $phone: " . json_encode($result));
            } else {
                \Log::error("Gagal mengirim WA ke $phone: " . json_encode($result));
            }
        } catch (\Exception $e) {
            \Log::error("Error mengirim WA ke $phone: " . $e->getMessage());
        }
    }
}
