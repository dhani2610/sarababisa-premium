<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Product;
use App\Models\Category;
use App\Exports\ToolExport;
use App\Imports\ToolImport;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\ProductRequest;
use Maatwebsite\Excel\Facades\Excel;

class ProdukToolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/kepalatoko/produk/tool',compact('toko'));
    }

    public function importChunk(Request $request)
    {
        try {
            $rows = $request->input('rows', []);
            $cabangId = getCabangId();

            $insertedCount = 0;
            $updatedCount  = 0;
            $skippedCount  = 0;

            foreach ($rows as $row) {

                // ===============================
                // Validasi baris kosong
                // ===============================
                if (empty($row['Nama Produk'])) {
                    $skippedCount++;
                    continue;
                }

                $namaProduk = trim($row['Nama Produk']);

                // ===============================
                // Cek Tool di cabang ini
                // ===============================
                $existing = Product::where('cabang_id', $cabangId)
                    ->where('product_name', $namaProduk)
                    ->first();

                $data = [
                    'categories_id'     => 4, // Tools
                    'category_name'     => 'Sparepart', // atau ganti "Tools" / "Alat" jika mau
                    'sub_categories_id' => $row['ID Sub Kategori'] ?? null,
                    'product_code'      => $row['Kode Produk'] ?? null,
                    'stok'              => $row['Stok'] ?? 0,
                    'stok_minimal'      => $row['Stok Minimal'] ?? 0,
                    'harga_modal'       => $row['Harga Modal'] ?? 0,
                    'harga_jual_toko'   => $row['Harga Jual Toko'] ?? 0,
                    'harga_jual'        => $row['Harga Jual Pelanggan'] ?? 0,
                    'keterangan'        => $row['Keterangan'] ?? null,
                    'garansi'           => $row['Garansi Produk (Hari)'] ?? 0,
                    'ppn'               => $row['PPN 11%'] ?? 0,
                    'updated_at'        => now(),
                ];

                // ===============================
                // UPDATE jika sudah ada
                // ===============================
                if ($existing) {
                    $existing->update($data);
                    $updatedCount++;
                    continue;
                }

                // ===============================
                // CREATE jika belum ada
                // ===============================
                $data['product_name'] = $namaProduk;
                $data['cabang_id']    = $cabangId;
                $data['created_at']  = now();

                Product::create($data);
                $insertedCount++;
            }

            return response()->json([
                'status'   => 'success',
                'inserted'=> $insertedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'msg'    => $th->getMessage(),
            ], 500);
        }
    }


    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = Product::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiOrder');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data produk yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        Product::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data produk berhasil dihapus.']);
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
    public function store(ProductRequest $request)
    {
        $namakategori = Category::find($request->categories_id);

        // Create product
        Product::create([
            'product_name' => $request->product_name,
            'product_code' => $request->product_code,
            'categories_id' => $request->categories_id,
            'sub_categories_id' => $request->sub_categories_id,
            'category_name' => $namakategori->category_name,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'harga_jual_toko' => $request->harga_jual_toko,
            'keterangan' => $request->keterangan,
            'garansi' => $request->garansi,
            'ppn' => $request->ppn,
            'cabang_id' => getCabangId(),
        ]);

        // return redirect()->route('tool.index');
        toast('Data berhasil disimpan.', 'success');
        return redirect()->back();

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

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ProdukData', $namafile);
        Excel::import(new ToolImport, \public_path('/ProdukData/' . $namafile));
        return redirect()->route('tool.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new ToolExport, 'tool.xlsx');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Product::where('cabang_id',getCabangId())->findOrFail($id);
        $spareparts = SubCategory::where('cabang_id',getCabangId())->where('categories_id', '=', '4')->get();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages.kepalatoko.produk.tool-edit', [
            'item' => $item,
            'spareparts' => $spareparts,
            'toko' => $toko
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
        $item = Product::findOrFail($id);
        $namakategori = Category::find($request->categories_id);
        // Create product
        $item->update([
            'product_name' => $request->product_name,
            'product_code' => $request->product_code,
            'categories_id' => $request->categories_id,
            'sub_categories_id' => $request->sub_categories_id,
            'category_name' => $namakategori->category_name,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'harga_jual_toko' => $request->harga_jual_toko,
            'keterangan' => $request->keterangan,
            'garansi' => $request->garansi,
            'ppn' => $request->ppn
        ]);

        return redirect()->route('tool.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Product::findOrFail($id);

        if (
            $item->relasiOrder()->exists()
        ) {
            toast('Data Produk yang memiliki riwayat transaksi tidak bisa dihapus.', 'error');
            return redirect()->back();
        }

        $item->delete();

        toast('Data Produk berhasil dihapus.', 'success');

        return redirect()->route('tool.index');
    }
}
