<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\HistoryGaransi;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoryGaransiController extends Controller
{
    public function index()
    {
        $users = User::where('cabang_id',getCabangId())->get();
        return view('pages.kepalatoko.history.garansi', compact(
            'users'
        ));
    }

    public function cetak(Request $request)
    {
        // Ambil info toko
        $users = User::find(1);
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Ambil data history garansi berdasarkan periode
        $data = HistoryGaransi::where('cabang_id',getCabangId())->with(['service', 'teknisi', 'penerima'])
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date', 'desc')
            ->get();

        // Hitung ringkasan
        $totalData = $data->count();
        $totalSelesai = $data->where('status', 2)->count();
        $totalProses = $data->where('status', 1)->count();
        $totalBatal = $data->where('status', 3)->count();

        // Buat PDF
        // return View('pages.kepalatoko.cetak-laporan-history-garansi', [
        $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-history-garansi', [
            'users' => $users,
            'imagePath' => $imagePath,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'data' => $data,
            'totalData' => $totalData,
            'totalSelesai' => $totalSelesai,
            'totalProses' => $totalProses,
            'totalBatal' => $totalBatal,
        ]);

        $filename = 'Laporan History Garansi ' . $start_date . ' sd ' . $end_date . '.pdf';
        return $pdf->stream($filename);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
        }

        try {
            HistoryGaransi::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data']);
        }
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'date'          => 'required|date',
            'service_id'    => 'required|exists:service_transactions,id',
            'penerima_id'   => 'required|exists:users,id',
            'keluhan'    => 'required|string',
        ]);

        $data = new HistoryGaransi();
        $data->date        = $request->date;
        $data->service_id  = $request->service_id;
        $data->penerima_id = $request->penerima_id;
        $data->teknisi_id  = 0;
        $data->keluhan  = $request->keluhan;
        $data->tindakan    = [];
        $data->sparepart   =  [];
        $data->modal_sparepart = 0;
        $data->total_biaya_tindakan = 0;
        $data->total_biaya = 0;
        $data->catatan     = '-';
        $data->status     = 1;
        $data->cabang_id     = getCabangId();
        $data->save();

        toast('Data berhasil disimpan.', 'success');
        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil disimpan');
    }
    public function update(Request $request,$id)
    {
        // dd($request->all());
        $request->validate([
            'date'          => 'required|date',
            'service_id'    => 'required|exists:service_transactions,id',
            'penerima_id'   => 'required|exists:users,id',
            'keluhan'       => 'required|string',
        ]);

        $data = HistoryGaransi::find($id);
        $data->date        = $request->date;
        if ($request->status == 2) {
            $data->tgl_selesai = date('Y-m-d');
        }else{
            $data->tgl_selesai = null;
        }
        $data->service_id  = $request->service_id;
        $data->penerima_id = $request->penerima_id;
        $data->teknisi_id  = $request->teknisi_id;
        $data->tindakan   =  !empty($request->tindakan) ? json_encode($request->tindakan) : [];
        $data->sparepart   =  !empty($request->sparepart) ? json_encode($request->sparepart) : [];
        $data->modal_sparepart = $request->modal_sparepart ?? 0;
        $data->total_biaya_tindakan = $request->total_biaya_tindakan;
        $data->total_biaya = $request->total_biaya;
        $data->catatan     = $request->catatan;
        $data->keluhan     = $request->keluhan;
        $data->status     = $request->status;
        $data->save();

        if (!empty($request->sparepart)) {
            foreach ($request->sparepart as $row) {

                // Pastikan ada id & qty
                if (empty($row['id']) || empty($row['qty'])) {
                    continue; // skip jika data tidak valid
                }

                // Cek produk
                $spareparts = Product::find($row['id']);
                if (!$spareparts) {
                    continue; // skip kalau produk tidak ada
                }

                // Cek service
                $service = ServiceTransaction::find($request->service_id);
                if (!$service) {
                    continue; // skip kalau service tidak ditemukan
                }

                // Cek customer
                $customer = Customer::find($service->customers_id);
                if (!$customer) {
                    continue; // skip kalau customer tidak ada
                }

                // Update stok
                if ($spareparts->stok >= (int)$row['qty']) {
                    $spareparts->stok -= (int)$row['qty'];
                    $spareparts->save();
                } else {
                    continue; // skip kalau stok tidak cukup
                }

            }
        }

        if ($request->total_biaya > 0) {
            Expense::create([
                'name' => $request->catatan,
                'price' => $request->total_biaya,
                'users_id' => auth()->user()->id
            ]);
        }
        toast('Data berhasil disimpan.', 'success');

        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil disimpan');
    }

    public function toggleStatus($id)
    {
        $item = HistoryGaransi::findOrFail($id);

        // Logic toggle (misalnya siklus 1 -> 2 -> 3 -> balik ke 1)
        if ($item->status == 1) {
            $item->status = 2; // dari Diproses ke Selesai
        } elseif ($item->status == 2) {
            $item->status = 3; // dari Selesai ke Dibatalkan
        } else {
            $item->status = 1; // dari Dibatalkan ke Diproses
        }

        $item->save();

        return response()->json([
            'success' => true,
            'status' => $item->status,
            'label' => $item->status == 1 ? 'Diproses' : ($item->status == 2 ? 'Selesai' : 'Dibatalkan')
        ]);
    }


    public function destroy($id)
    {
        $history = HistoryGaransi::findOrFail($id);

        // balikin stok sparepart
        if (!empty($history->sparepart)) {
            $spareparts = json_decode($history->sparepart, true);

            foreach ($spareparts as $row) {
                $product = Product::find($row['id']);
                if ($product) {
                    $product->stok += (int)$row['qty']; // balikin stok
                    $product->save();
                }
            }
        }

        // hapus data history garansi
        $history->delete();
        toast('Data berhasil dihapus & stok sparepart dikembalikan.', 'success');

        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil dihapus & stok sparepart dikembalikan');
    }


    public function edit($id)
    {
        $historyGaransi = HistoryGaransi::findOrFail($id);
        $serviceTransactions = ServiceTransaction::where('cabang_id',getCabangId())->orderBy('created_at', 'desc')->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::where('cabang_id',getCabangId())->get();
        $users = User::where('cabang_id',getCabangId())->get();
        return view('pages.kepalatoko.history.edit', compact(
            'users','historyGaransi','serviceTransactions','products','serviceActions'
        ));
    }

}
