<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Requests\TransferStokRequest;
use App\Models\Product;
use App\Models\TransferStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class TransferStokController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);

        $query = TransferStok::with(['dariProduk', 'keProduk'])
            ->where(function($q){
                // jika ingin filter default cabang, sesuaikan atau hapus
            })
            ->orderBy('created_at', 'desc');

        // filter tanggal (opsional)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal', [$start, $end]);
        }

        $transfers = $query->paginate($perPage);

        // produk dropdown: produk di cabang saat ini
        $products = Product::where('cabang_id', getCabangId())->orderBy('product_name')->get();

        return view('pages.kepalatoko.master.transfer_stok', [
            'transfers' => $transfers,
            'products' => $products,
        ]);
    }

    public function productsByCabang($cabangId,$kategori)
    {
        $products = Product::with('capacity')->where('cabang_id', $cabangId)
                    ->where('categories_id', $kategori)
                    ->orderBy('product_name')
                    ->get();

        return response()->json($products);
    }

    


    public function store(Request $request)
    {
        // $data = $request->validated();

        // hanya simpan transfer, status = 0 (menunggu approve)
        $transfer = TransferStok::create([
            'dari_cabang_id' => $request->dari_cabang_id,
            'ke_cabang_id' => $request->ke_cabang_id,
            'dari_produk_id' => $request->dari_produk_id,
            'ke_produk_id' => $request->ke_produk_id ?? 0,
            'stok' => $request->stok,
            'tanggal' => $request->tanggal,
            'created_by' => auth()->id(),
            'status' => 0 // waiting approval
        ]);

        toast('Transfer stok berhasil dibuat dan menunggu approval.', 'success');
        return redirect()->route('transfer-stok.index');
    }

    public function approve($id)
    {
        $transfer = TransferStok::findOrFail($id);

        if ($transfer->status != 0) {
            return back()->withErrors(['msg' => 'Transfer sudah diproses.']);
        }

        DB::beginTransaction();
        try {
            $dariProduk = Product::findOrFail($transfer->dari_produk_id);

            // cek stok cukup
            if ($transfer->stok > $dariProduk->stok) {
                return back()->withErrors(['msg' => 'Stok produk asal tidak mencukupi.']);
            }

            /** ================================
             * 1. Kurangi stok produk asal
             * ================================ */
            $dariProduk->stok -= $transfer->stok;
            $dariProduk->save();

            /** ================================
             * 2. Proses ke produk tujuan
             * ================================ */
            $keProduk = Product::where('cabang_id', $transfer->ke_cabang_id)
                ->where('id', $transfer->ke_produk_id)
                ->first();

            $needClone = true;
            if ($keProduk) {
                $sameHarga = (
                    intval($keProduk->harga_modal) === intval($dariProduk->harga_modal) &&
                    intval($keProduk->harga_jual) === intval($dariProduk->harga_jual) &&
                    intval($keProduk->harga_jual_toko) === intval($dariProduk->harga_jual_toko)
                );

                if ($sameHarga)
                    $needClone = false;
            }



            /** ================================
             * 3. Clone jika perlu
             * ================================ */
            if ($needClone) {
                $baseName = $dariProduk->product_name;
                $newName = $baseName . ' #1';

                $i = 1;
                while (Product::where('cabang_id', $transfer->ke_cabang_id)
                    ->where('product_name', $newName)
                    ->exists()
                ) {
                    $i++;
                    $newName = $baseName . ' #' . $i;
                }



                $cloned = $dariProduk->replicate();
                $cloned->product_name = $newName;
                // khusus kategori 1 (handphone) → IMEI harus unik
                if ($dariProduk->categories_id == 1) {

                    $baseImei = $dariProduk->nomor_seri;
                    $newImei = $baseImei . ' #1';

                    $j = 1;
                    while (Product::where('nomor_seri', $newImei)->exists()) {
                        $j++;
                        $newImei = $baseImei . ' #' . $j;
                    }

                    $cloned->nomor_seri = $newImei;

                } else {
                    // kategori NON-IMEI → jangan disalin biar tidak duplicate
                    $cloned->nomor_seri = null; 
                    // atau hapus saja: unset($cloned->nomor_seri);
                }
                $cloned->cabang_id = $transfer->ke_cabang_id;
                $cloned->stok = $transfer->stok;
                $cloned->push();
                // return response()->json(['needClone'=>$needClone, 'newName'=>$newName, 'cloned' => $cloned]);

                // dd($needClone,$newName,$cloned);


                $transfer->ke_produk_id = $cloned->id;
            } else {
                /** ================================
                 * 4. Tambah stok di produk tujuan
                 * ================================ */
                $keProduk->stok += $transfer->stok;
                $keProduk->save();
            }

            /** ================================
             * 5. Update status transfer
             * ================================ */
            $transfer->status = 1; // approved
            $transfer->approve_by = auth()->user()->id;
            $transfer->datetime_approve = date('Y-m-d H:i:s');
            $transfer->save();

            DB::commit();
            toast('Transfer stok berhasil di-setujui dan di proses.', 'success');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();
            // dd($e->getMessage());
            \Log::error("APPROVE ERROR: " . $e->getMessage());
            return back()->withErrors(['msg' => 'Terjadi error saat approve.']);
        }
    }


    // public function store(TransferStokRequest $request)
    // {
    //     $data = $request->validated();

    //     // pastikan stok kirim <= stok produk asal
    //     $dariProduk = Product::findOrFail($data['dari_produk_id']);

    //     if ($data['stok'] > $dariProduk->stok) {
    //         return back()->withErrors(['stok' => 'Stok yang dikirim tidak boleh lebih besar dari stok produk asal.'])->withInput();
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // kurangi stok di produk asal
    //         $dariProduk->stok = $dariProduk->stok - $data['stok'];
    //         $dariProduk->save();

    //         // cari produk tujuan di cabang tujuan (bisa jadi sama produk id atau berbeda)
    //         $keProduk = Product::where('cabang_id', $data['ke_cabang_id'])
    //                             ->where('id', $data['ke_produk_id'] ?? null)
    //                             ->first();

    //         // jika produk tujuan tidak ditemukan atau harga berbeda -> clonning
    //         $needClone = true;
    //         if ($keProduk) {
    //             $sameHarga = (
    //                 intval($keProduk->harga_modal) === intval($dariProduk->harga_modal)
    //                 && intval($keProduk->harga_jual) === intval($dariProduk->harga_jual)
    //                 && intval($keProduk->harga_jual_toko) === intval($dariProduk->harga_jual_toko)
    //             );
    //             if ($sameHarga) $needClone = false;
    //         }

    //         if ($needClone) {
    //             // buat nama baru unik: "nama asli #1" (jika sudah ada #n, increment)
    //             $baseName = $dariProduk->product_name;
    //             $newName = $baseName . ' #1';
    //             // cek clash dan increment jika perlu
    //             $i = 1;
    //             while (Product::where('cabang_id', $data['ke_cabang_id'])
    //                           ->where('product_name', $newName)
    //                           ->exists()) {
    //                 $i++;
    //                 $newName = $baseName . ' #' . $i;
    //             }

    //             $cloned = $dariProduk->replicate();
    //             $cloned->product_name = $newName;
    //             $cloned->cabang_id = $data['ke_cabang_id'];
    //             // atur stok ke jumlah yg dikirim
    //             $cloned->stok = $data['stok'];
    //             $cloned->push(); // simpan
    //             $keProdukId = $cloned->id;
    //         } else {
    //             // cukup tambah stok di produk tujuan
    //             $keProduk->stok = $keProduk->stok + $data['stok'];
    //             $keProduk->save();
    //             $keProdukId = $keProduk->id;
    //         }

    //         // simpan record transfer
    //         $transfer = TransferStok::create([
    //             'dari_cabang_id' => $data['dari_cabang_id'],
    //             'ke_cabang_id' => $data['ke_cabang_id'],
    //             'dari_produk_id' => $dariProduk->id,
    //             'ke_produk_id' => $keProdukId,
    //             'stok' => $data['stok'],
    //             'tanggal' => $data['tanggal'],
    //             'created_by' => auth()->user()->id ?? 0,
    //             'status' => 1, // bisa atur default. 0 jika butuh approval
    //         ]);

    //         DB::commit();
    //         toast('Transfer stok berhasil.', 'success');
    //         return redirect()->route('transfer-stok.index');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         \Log::error('TransferStok::store error: '.$e->getMessage());
    //         return back()->withErrors(['message' => 'Terjadi kesalahan saat memproses transfer.'])->withInput();
    //     }
    // }


    public function destroy($id)
    {
        $item = TransferStok::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($item->status == 1) {
                // rollback stok: kembalikan stok ke produk asal, kurangi di produk tujuan
                $dari = Product::findOrFail($item->dari_produk_id);
                $ke = Product::findOrFail($item->ke_produk_id);

                // kembalikan ke dari
                $dari->stok = $dari->stok + $item->stok;
                $dari->save();

                // kurangi di ke
                if ($ke->stok >= $item->stok) {
                    $ke->stok = $ke->stok - $item->stok;
                    $ke->save();
                } else {
                    // jika stok tujuan tidak cukup (mungkin sudah terpakai), skip pengurangan dan log
                    \Log::warning("Destroy transfer stok: stok tujuan kurang dari transfer awal id={$item->id}");
                }
            }

            $item->delete();

            DB::commit();
            toast('Transfer stok berhasil dihapus.', 'success');
            return redirect()->route('transfer-stok.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('TransferStok::destroy error: '.$e->getMessage());
            return back()->withErrors(['message' => 'Gagal menghapus transfer stok.']);
        }
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        if (!is_array($selectedIds) || empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 422);
        }

        DB::beginTransaction();
        try {
            $items = TransferStok::whereIn('id', $selectedIds)->get();
            foreach ($items as $item) {
                if ($item->status == 1) {
                    $dari = Product::find($item->dari_produk_id);
                    $ke = Product::find($item->ke_produk_id);

                    if ($dari) {
                        $dari->stok = $dari->stok + $item->stok;
                        $dari->save();
                    }
                    if ($ke && $ke->stok >= $item->stok) {
                        $ke->stok = $ke->stok - $item->stok;
                        $ke->save();
                    }
                }

                $item->delete();
            }
            DB::commit();
            return response()->json(['message' => 'Data transfer stok berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('TransferStok::deleteSelected error: '.$e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data.'], 500);
        }
    }

    public function cetak(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $query = TransferStok::with(['dariProduk', 'keProduk'])
            ->whereBetween('tanggal', [$start_date, Carbon::parse($end_date)->endOfDay()])
            ->orderBy('tanggal', 'desc');

        $transfers = $query->get();

        $users = auth()->user(); // atau toko info
        $logo = $users->profile_photo_path ?? null;
        $imagePath = $logo ? public_path('storage/' . $logo) : null;

        $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-transfer-stok', [
            'transfers' => $transfers,
            'users' => $users,
            'imagePath' => $imagePath,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        $filename = 'Laporan Transfer Stok ' . $start_date . ' sd ' . $end_date . '.pdf';
        return $pdf->stream($filename);
    }
}
