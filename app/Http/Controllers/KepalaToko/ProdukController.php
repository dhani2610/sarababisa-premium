<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Exports\ProdukExport;
use App\Imports\ProdukImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages/kepalatoko/produk/index',compact('toko'));
    }

    public function getData(Request $request)
    {

        $limit = $request->get('limit', 200);
        $offset = $request->get('offset', 0);

        $idCat = $request->cat;
        $cabangId = getCabangId();
        $userRole = Auth::user()->role;
        $tokoSetting = StoreSetting::where('cabang_id', $cabangId)->first();

        // Eager load relationships agar performa cepat
        if (empty($idCat)) {
            $query = Product::where('cabang_id', $cabangId)
                ->with(['capacity', 'category','model'])
                ->latest()
                ->skip($offset)
                ->take($limit)
                ->get();

        }else{
            $query = Product::where('cabang_id', $cabangId)
                ->where('categories_id', $idCat)
                ->with(['capacity', 'category','model'])
                ->latest()
                ->skip($offset)
                ->take($limit)
                ->get();
        }

        return DataTables::of($query)
                ->addIndexColumn()
                // Kolom Checkbox
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
                })
                // Nama Produk (Custom HTML berdasarkan kategori)
                ->addColumn('nama_produk', function ($row) {
                    if ($row->categories_id == 1) {
                        // Kategori Handphone
                        $cap = $row->capacity ? $row->capacity->name : '-';
                        return '<div class="font-medium">' .
                            e($row->product_name) . ' ' .
                            e($row->kondisi) . ' ' .
                            e($row->warna) . ' ' .
                            e($row->ram) . ' / ' .
                            e($cap) .
                            ' (IMEI ' . e($row->nomor_seri) . ')</div>';
                    } else {
                        // Kategori Lain (Sparepart/Aksesoris/Tool)
                        $text = e($row->product_name);
                        return '<div class="font-medium">' . $text . '</div>';
                    }
                })
                // Kategori
                ->addColumn('category_name', function ($row) {
                    return '<div class="font-medium">' . e($row->category->category_name ?? '-') . '</div>';
                })
                // Kode Produk
                ->addColumn('product_code', function ($row) {
                    return '<div class="font-medium">' . e($row->product_code ?? '-') . '</div>';
                })
                // Keterangan
                ->addColumn('keterangan', function ($row) {
                    return '<div class="font-medium">' . e($row->keterangan ?? '-') . '</div>';
                })
                // Stok
                ->addColumn('stok', function ($row) {
                    return '<div class="font-medium">' . e($row->stok) . '</div>';
                })
                // Stok Minimal
                ->addColumn('stok_minimal', function ($row) {
                    return '<div class="font-medium">' . e($row->stok_minimal ?? '-') . '</div>';
                })
                // Harga Modal (Cek Hak Akses)
                ->addColumn('harga_modal', function ($row) use ($userRole, $tokoSetting) {
                    if ($userRole == 'Kepala Toko' || ($tokoSetting->is_modal_produk ?? 0) == 1) {
                        return '<div class="font-medium">Rp. ' . number_format($row->harga_modal) . '</div>';
                    }
                    return ''; // Kosong jika tidak ada akses
                })
                // Harga Jual Toko
                ->addColumn('harga_jual_toko', function ($row) {
                    return '<div class="font-medium">Rp. ' . number_format($row->harga_jual_toko ?? 0) . '</div>';
                })
                // Harga Jual Pelanggan
                ->addColumn('harga_jual', function ($row) {
                    return '<div class="font-medium">Rp. ' . number_format($row->harga_jual) . '</div>';
                })
                // Garansi
                ->addColumn('garansi', function ($row) {
                    $text = 'Tidak ada';
                    if ($row->garansi && $row->garansi_imei) {
                        $text = $row->garansi . ' hari / ' . $row->garansi_imei . ' hari';
                    } elseif ($row->garansi) {
                        $text = $row->garansi . ' hari / -';
                    } elseif ($row->garansi_imei) {
                        $text = '- / ' . $row->garansi_imei . ' hari';
                    }
                    return '<div class="font-medium">' . $text . '</div>';
                })
                // Portal Status
                ->addColumn('is_portal', function ($row) {
                    $status = $row->is_portal == 1 ? 'Show' : 'Not Show';
                    return '<div class="font-medium">' . $status . '</div>';
                })
                // Aksi
                ->addColumn('aksi', function ($row) use ($userRole, $tokoSetting) {
            $html = '<div class="space-x-1 flex">';

            $fotoUrl = $row->foto ? asset('storage/'.$row->foto) : '';
            // Tombol Upload Foto (Trigger JS Global -> Livewire)
            $html .= '<button onclick="openFotoModal('.$row->id.', \''.$fotoUrl.'\')" class="text-blue-500 hover:text-blue-700 rounded-full" title="Upload Foto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#1d4ed8" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <line x1="15" y1="8" x2="15.01" y2="8" />
                                    <rect x="4" y="4" width="16" height="16" rx="3" />
                                    <path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" />
                                    <path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" />
                                </svg>
                            </button>';

            // Tombol Barcode
            $barcodeUrl = route('download-barcode', $row->id);
            $html .= '<a href="'.$barcodeUrl.'">
                        <button class="text-slate-400 hover:text-slate-500 mt-2 rounded-full" title="Print Barcode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                <rect x="7" y="13" width="10" height="8" rx="2" />
                            </svg>
                        </button>
                        </a>';

            // Tombol Edit & Delete (Cek Hak Akses)
            if ((int) ($tokoSetting->is_edit_produk ?? 0) == 1 || $userRole == 'Kepala Toko') {
                $editUrl = '#';
                if ($row->categories_id === 1) $editUrl = route('handphone.edit', $row->id);
                elseif ($row->categories_id === 2) $editUrl = route('sparepart.edit', $row->id);
                elseif ($row->categories_id === 3) $editUrl = route('aksesoris.edit', $row->id);
                else $editUrl = route('tool.edit', $row->id);

                $html .= '<a href="'.$editUrl.'">
                            <button class="text-slate-400 hover:text-slate-500 rounded-full" title="Edit">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                            </button>
                            </a>';

                // Tombol Delete (Konfirmasi Biasa / Browser Native)
                        $deleteUrl = route('item.destroy', $row->id);

                        $html .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus produk ini?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="text-rose-500 hover:text-rose-600 rounded-full" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <line x1="4" y1="7" x2="20" y2="7" />
                                    <line x1="10" y1="11" x2="10" y2="17" />
                                    <line x1="14" y1="11" x2="14" y2="17" />
                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                </svg>
                            </button>
                        </form>';
            }

            $html .= '</div>';
            return $html;
        })
            // Definisikan kolom yang mengandung HTML agar tidak di-escape
            ->rawColumns(['checkbox', 'nama_produk', 'category_name', 'product_code', 'keterangan', 'stok', 'stok_minimal', 'harga_modal', 'harga_jual_toko', 'harga_jual', 'garansi', 'is_portal', 'aksi'])
            ->make(true);
    }

       public function uploadFotoAjax(Request $request)
        {
            $request->validate([
                'id' => 'required',
                'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:1024', // Max 1MB
            ]);

            $product = Product::findOrFail($request->id);

            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada, KECUALI jika nama filenya sama (overwrite)
                // Namun untuk aman, hapus saja jika path-nya beda atau force overwrite via storeAs
                if ($product->foto && Storage::disk('public')->exists($product->foto)) {
                    // Opsional: Hapus file lama jika extension berubah (misal dari .jpg ke .png)
                    // Tapi karena kita pakai storeAs, file dengan nama sama akan tertimpa otomatis.
                    // Storage::disk('public')->delete($product->foto);
                }

                $file = $request->file('foto');

                // Format nama file: produk_{id}.{ext}
                $filename = 'produk_' . $request->id . '.' . $file->getClientOriginalExtension();

                // Simpan ke storage (public/produk-foto)
                $path = $file->storeAs('produk-foto', $filename, 'public');

                // Update database (tambahkan timestamp agar browser tidak cache gambar lama jika nama file sama)
                $product->update(['foto' => $path]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Foto berhasil diupload!',
                    'new_image_url' => Storage::url($path) . '?t=' . time() // Cache busting
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan'], 400);
        }

        // Bulk Delete
        public function deleteBatch(Request $request)
        {
            $selectedIds = $request->input('ids');
            if (empty($selectedIds)) return response()->json(['message' => 'Tidak ada data dipilih'], 400);

            // Cek Relasi Order
            $hasRelation = Product::whereIn('id', $selectedIds)
                ->whereHas('relasiOrder')
                ->exists();

            if ($hasRelation) {
                return response()->json(['message' => 'Gagal: Beberapa produk memiliki riwayat transaksi.'], 422);
            }

            Product::whereIn('id', $selectedIds)->delete();
            return response()->json(['message' => 'Data produk berhasil dihapus.']);
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
    public function updatePortal(Request $request)
    {
        $selectedIds = $request->input('selectedIds');
        $action = $request->input('action');

        if ($action === 'show') {
            Product::whereIn('id', $selectedIds)->update(['is_portal' => 1]);
            return response()->json(['message' => 'Produk berhasil ditampilkan di portal.']);
        } elseif ($action === 'hide') {
            Product::whereIn('id', $selectedIds)->update(['is_portal' => 0]);
            return response()->json(['message' => 'Produk berhasil disembunyikan dari portal.']);
        }

        return response()->json(['message' => 'Aksi tidak valid.'], 400);
    }



    // public function downloadBarcode($id)
    // {
    //     $product = Product::findOrFail($id);

    //     if (!$product->product_code) {
    //         return redirect()->back()->with('error', 'Produk ini belum memiliki kode produk.');
    //     }

    //     // Buat barcode PNG
    //     $generator = new BarcodeGeneratorPNG();
    //     $barcodeData = $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128);

    //     // Simpan ke file sementara
    //     $fileName = 'barcode_' . $product->product_code .'-'.$product->product_name. '.png';
    //     $filePath = storage_path('app/public/' . $fileName);
    //     file_put_contents($filePath, $barcodeData);

    //     // Download file
    //     return response()->download($filePath)->deleteFileAfterSend(true);
    // }

    public function downloadBarcode($id)
    {
        $product = Product::findOrFail($id);

        if (!$product->product_code) {
            return redirect()->back()->with('error', 'Produk ini belum memiliki kode produk.');
        }

        $generator = new BarcodeGeneratorPNG();
        $barcodeData = base64_encode(
            $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128)
        );

        // Buat PDF dengan view
        $pdf = Pdf::loadView('pages.kepalatoko.cetak-barcode', [
        // return view('pages.kepalatoko.cetak-barcode', [
            'product' => $product,
            'barcodeData' => $barcodeData
        ])->setPaper([0, 0, 226.77, 141.73]); // ukuran kertas kecil (80x50mm)


        return $pdf->stream('barcode_' . $product->product_code . '.pdf');
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
        //
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
        $item = Product::where('cabang_id',getCabangId())->findOrFail($id);
        $categories = SubCategory::where('cabang_id',getCabangId())->all();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('pages.kepalatoko.produk.edit', [
            'item' => $item,
            'categories' => $categories,
            'toko' => $toko
        ]);
    }

    public function import(Request $request)
    {
        $data = $request->file('file');
        $namafile = $data->getClientOriginalName();
        $data->move('ProdukData', $namafile);
        Excel::import(new ProdukImport, \public_path('/ProdukData/' . $namafile));
        return redirect()->route('item.index')->with('success', 'All good!');
    }

    public function export()
    {
        return Excel::download(new ProdukExport, 'produk.xlsx');
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $pilihan = $request->stok;

        // Mengambil data produk habis
        $empty_products = Product::where('cabang_id',getCabangId())->where('stok', 0)->get();

        // Menghitung data produk habis
        $jumlah_item_habis = Product::where('cabang_id',getCabangId())->where('stok', 0)->count();

        // Mengambil data produk tersedia
        $available_products = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->get();

        // Menghitung data produk tersedia
        $jumlah_item_tersedia = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->count();

        $modal_stok_tersedia = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->sum(DB::raw('stok * harga_modal'));

        // Menghitung stok produk tersedia
        $jumlah_stok_tersedia = Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->sum('stok');

        if ($pilihan === "tersedia") {
            $products = $available_products;
        } else {
            $products = $empty_products;
        }

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-produk', [
        // return View('pages.kepalatoko.cetak-laporan-produk', [
            'users' => $users,
            'imagePath' => $imagePath,
            'products' => $products,
            'pilihan' => $pilihan,
            'jumlah_item_habis' => $jumlah_item_habis,
            'jumlah_item_tersedia' => $jumlah_item_tersedia,
            'jumlah_stok_tersedia' => $jumlah_stok_tersedia,
            'modal_stok_tersedia' => $modal_stok_tersedia,
        ]);

        $filename = 'Laporan Produk.pdf';

        return $pdf->download($filename);
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
            'sub_categories_id' => $request->sub_categories_id,
            'category_name' => $namakategori->category_name,
            'stok' => $request->stok,
            'stok_minimal' => $request->stok_minimal,
            'harga_modal' => $request->harga_modal,
            'harga_jual' => $request->harga_jual,
            'keterangan' => $request->keterangan,
            'nomor_seri' => $request->nomor_seri,
            'garansi' => $request->garansi,
            'garansi_imei' => $request->garansi_imei,
            'ppn' => $request->ppn
        ]);

        return redirect()->route('item.index');
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

        return redirect()->route('item.index');
    }
}
