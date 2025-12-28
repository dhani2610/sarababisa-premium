<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\PelangganExport;
use App\Imports\PelangganImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\CustomerRequest;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::where('cabang_id',getCabangId())->paginate(10);
        $customers_count = Customer::where('cabang_id',getCabangId())->get()->count();
        return view('pages/kepalatoko/pelanggan', compact('customers', 'customers_count'));
    }

    public function getData(Request $request)
    {
        // Query Dasar
        $query = Customer::where('cabang_id', getCabangId())->latest();

        return DataTables::of($query)
            ->addIndexColumn() // Untuk nomor urut (DT_RowIndex)

            // Kolom Checkbox untuk Bulk Delete
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })

            // Kolom Nama
            ->editColumn('nama', function ($row) {
                return '<div class="font-medium text-slate-800">' . e($row->nama) . '</div>';
            })

            // Kolom Aksi
            ->addColumn('aksi', function ($row) {
                $editUrl = route('pelanggan.edit', $row->id);
                $deleteUrl = route('pelanggan.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="flex space-x-1">
                        <a href="' . $editUrl . '" class="text-slate-400 hover:text-slate-500 rounded-full">
                            <span class="sr-only">Edit</span>
                            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                            </svg>
                        </a>

                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'Apakah anda yakin ingin menghapus data ini?\');">
                            ' . $csrf . $method . '
                            <button type="submit" class="text-rose-500 hover:text-rose-600 rounded-full">
                                <span class="sr-only">Delete</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                </svg>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'nama', 'aksi'])
            ->make(true);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Customer::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('servicetransaction')
                    ->orWhereHas('sale');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Pelanggan yang memiliki riwayat transaksi servis/penjualan tidak bisa dihapus.']);
        }

        Customer::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Pelanggan berhasil dihapus.']);
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
        $data = $request->all();

        $data = $data['cabang_id'] = getCabangId();
        Customer::create($data);

        return redirect()->route('pelanggan.index');
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('PelangganData', $namafile);
        Excel::import(new PelangganImport, \public_path('/PelangganData/' . $namafile));
        return redirect()->route('pelanggan.index')->with('success', 'All good!');
    }


    public function importChunk(Request $request)
    {
        try {
            $rows = $request->input('rows');
            $cabangId = getCabangId(); // Pastikan helper ini jalan

            // Ambil semua nomor HP dari chunk ini
            $chunkHps = collect($rows)->pluck('Nomor HP')->filter()->toArray();

            // Ambil data yang sudah ada di DB (Global check)
            $existingCustomers = Customer::whereIn('nomor_hp', $chunkHps)
                ->get(['id', 'nomor_hp', 'cabang_id']);

            $insertData = [];
            $updatedCount = 0;

            foreach ($rows as $row) {
                // Validasi data kosong
                if (empty($row['Nama Pelanggan']) && empty($row['Nomor HP'])) {
                    continue;
                }

                // Pastikan HP jadi string & Trim spasi
                $hpAsli = trim((string) $row['Nomor HP']);

                // Cek existing
                $existing = $existingCustomers->where('nomor_hp', $hpAsli)->first();

                // 1. LOGIKA UPDATE (Jika Cabang Sama)
                if ($existing && $existing->cabang_id == $cabangId) {
                    Customer::where('id', $existing->id)->update([
                        'nama'     => $row['Nama Pelanggan'],
                        'kategori' => $row['Kategori Pelanggan'] ?? 'Umum',
                        'alamat'   => $row['Alamat'] ?? '-',
                    ]);
                    $updatedCount++;
                    continue;
                }

                // 2. LOGIKA CREATE
                $finalHp = $hpAsli;

                // Jika HP sudah dipakai cabang lain, kita rename
                if ($existing) {
                    $counter = 1; // Mulai dari 1
                    // Cek terus sampai nemu yang belum ada
                    while (Customer::where('nomor_hp', $finalHp)->exists()) {
                        // PERBAIKAN DISINI: Gunakan str_repeat agar titiknya nambah terus
                        // Loop 1: 0812.
                        // Loop 2: 0812..
                        // Loop 3: 0812...
                        $finalHp = $hpAsli . str_repeat('.', $counter);
                        $counter++;
                    }
                }

                $insertData[] = [
                    'nama'       => $row['Nama Pelanggan'],
                    'nomor_hp'   => $finalHp,
                    'kategori'   => $row['Kategori Pelanggan'] ?? 'Umum',
                    'alamat'     => $row['Alamat'] ?? '-',
                    'cabang_id'  => $cabangId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert Batch
            if (!empty($insertData)) {
                Customer::insert($insertData);
            }

            return response()->json([
                'status' => 'success',
                'inserted' => count($insertData),
                'updated' => $updatedCount
            ]);

        } catch (\Throwable $th) {
            // Log error biar tau di baris mana
            \Log::error('Import Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'msg' => $th->getMessage(),
            ], 500);
        }
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

        return view('pages.kepalatoko.pelanggan-edit', [
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

        return redirect()->route('pelanggan.index');
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

        return redirect()->route('pelanggan.index');
    }

    public function broadcast(Request $request)
    {
        $message = $request->input('message');

        if ($request->has('customers')) {
            $numbers = $request->input('customers', []);
        } else {
            // Kalau select kosong karena "Pilih Semua" dicentang
            $numbers = Customer::where('cabang_id',getCabangId())->pluck('nomor_hp')->toArray();
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
        $token = getStoreSettingByCabang()->fonnte ?? null;
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
