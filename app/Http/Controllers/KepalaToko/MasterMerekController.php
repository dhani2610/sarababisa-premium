<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Brand;
use App\Exports\MerekExport;
use App\Imports\BrandImport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\KepalaToko\BrandRequest;
use Yajra\DataTables\Facades\DataTables;

class MasterMerekController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/kepalatoko/master/merek');
    }



    // Method DataTables AJAX
    public function getData(Request $request)
    {
        $query = Brand::where('cabang_id',getCabangId())->latest();

        return DataTables::of($query)
            // Kolom Checkbox
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            // Kolom Aksi
            ->addColumn('aksi', function ($row) {
                $editUrl = route('master-merek.edit', $row->id);
                $deleteUrl = route('master-merek.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="flex space-x-1">
                        <a href="' . $editUrl . '">
                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                <span class="sr-only">Edit</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                            </button>
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
            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
    }

    // Method Bulk Delete
    public function deleteBatch(Request $request)
    {
        $selectedIds = $request->input('ids');

        if (empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 400);
        }

        // Cek Relasi (Sesuai logic di controller lama bapak)
        $hasRelation = Brand::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService')
                    ->orWhereHas('relasiModelSerie');
            })
            ->exists();

        // Uncomment jika ingin mengaktifkan validasi relasi
        // if ($hasRelation) {
        //    return response()->json(['message' => 'Data Merek yang memiliki riwayat transaksi/model seri tidak bisa dihapus.'], 422);
        // }

        Brand::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data merek berhasil dihapus.']);
    }
    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        // Validasi apakah data memiliki relasi dengan Service atau ModelSerie
        $hasRelation = Brand::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService')
                    ->orWhereHas('relasiModelSerie');
            })
            ->exists();

        // if ($hasRelation) {
        //     return response()->json(['message' => 'Data Merek yang memiliki riwayat transaksi/model seri tidak bisa dihapus.']);
        // }

        Brand::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data merek berhasil dihapus.']);
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
    // public function store(BrandRequest $request)
    // {
    //     $data = $request->validated();

    //     $existing = Brand::withTrashed()
    //         ->where('name', $data['name'])
    //         ->first();

    //     if ($existing && $existing->trashed()) {
    //         // Kalau ada merek dengan nama sama tapi soft delete → restore
    //         $existing->restore();
    //         $existing->update($data);

    //         return redirect()->route('master-merek.index')
    //             ->with('success', 'Merek berhasil dipulihkan & diperbarui.');
    //     }
    //     $data['cabang_id'] = getCabangId();

    //     // Kalau belum ada → buat baru
    //     Brand::create($data);

    //     return redirect()->route('master-merek.index')
    //         ->with('success', 'Merek berhasil ditambahkan.');
    // }

public function store(BrandRequest $request)
{
    $data = $request->validated();
    $data['cabang_id'] = getCabangId();

    $finalName = $data['name'];

    // Loop Cek Duplikat
    // Jika "Samsung" ada, dia akan ngecek "Samsung."
    // Jika "Samsung." ada, dia akan ngecek "Samsung.."
    // Dan seterusnya...
    // while (Brand::withTrashed()->where('name', $finalName)->exists()) {

    //     $finalName = $finalName . '.';

    // }

    // Set nama final yang sudah unik
    $data['name'] = $finalName;

    Brand::create($data);

    return redirect()->route('master-merek.index')
        ->with('success', 'Merek berhasil ditambahkan.');
}

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('BrandData', $namafile);
        Excel::import(new BrandImport, \public_path('/BrandData/' . $namafile));
        return redirect()->route('master-merek.index')->with('success', 'All good!');
    }

    public function importChunk(Request $request)
    {
        try {
            $rows = $request->input('rows');
            $cabangId = getCabangId(); // Pastikan helper ini tersedia

            // Ambil semua nama merek dari chunk yang dikirim untuk optimasi query
            $chunkNames = collect($rows)->pluck('Nama Merek')->filter()->toArray();

            // Ambil data yang sudah ada di Database (berdasarkan nama & cabang)
            $existingBrands = Brand::whereIn('name', $chunkNames)
                ->where('cabang_id', $cabangId)
                ->pluck('name')
                ->toArray();

            // Ubah ke lowercase agar perbandingannya case-insensitive (opsional)
            $existingBrandsLower = array_map('strtolower', $existingBrands);

            $insertData = [];
            $skippedCount = 0;

            foreach ($rows as $row) {
                // Validasi nama kosong
                if (empty($row['Nama Merek'])) {
                    continue;
                }

                $namaMerek = trim($row['Nama Merek']);

                // Cek apakah merek sudah ada (Case insensitive check)
                if (in_array(strtolower($namaMerek), $existingBrandsLower)) {
                    $skippedCount++;
                    continue;
                }

                // Masukkan ke array insert
                $insertData[] = [
                    'name'       => $namaMerek,
                    'cabang_id'  => $cabangId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Lakukan Insert Batch
            if (!empty($insertData)) {
                Brand::insert($insertData);
            }

            return response()->json([
                'status'   => 'success',
                'inserted' => count($insertData),
                'skipped'  => $skippedCount
            ]);

        } catch (\Throwable $th) {
            Log::error('Import Merek Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'msg'    => $th->getMessage(),
            ], 500);
        }
    }

    public function export()
    {
        return Excel::download(new MerekExport, 'data-merek.xlsx');
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
        $item = Brand::findOrFail($id);

        return view('pages.kepalatoko.master.merek-edit', [
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
    public function update(BrandRequest $request, $id)
    {
        $data = $request->all();

        $item = Brand::findOrFail($id);

        $item->update($data);

        return redirect()->route('master-merek.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Brand::findOrFail($id);

        // if (
        //     $item->relasiService()->exists() || $item->relasiModelSerie()->exists()
        // ) {
        //     toast('Data Merek yang memiliki riwayat transaksi/model seri tidak bisa dihapus.', 'error');
        //     return redirect()->back();
        // }

        $item->delete();

        toast('Data Merek berhasil dihapus.', 'success');

        return redirect()->route('master-merek.index');
    }
}
