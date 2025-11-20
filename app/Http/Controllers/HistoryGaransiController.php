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
use App\Models\Refund;
use App\Models\Term;
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
        $data = HistoryGaransi::where('cabang_id',getCabangId())->with(['service', 'teknisi', 'penerima','pelanggan'])
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date', 'desc')
            ->get();

        // Hitung ringkasan
        $totalData = $data->count();
        $totalSelesai = $data->where('status', 2)->count();
        $totalProses = $data->where('status', 1)->count();
        $totalBatal = $data->where('status', 3)->count();
        $totalModal = $data->sum('total_biaya');

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
            'totalModal' => $totalModal,
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

    public function cetakinkjet($id)
    {
        $history = HistoryGaransi::where('id',$id)->with(['pelanggan'])->first();
        $items = ServiceTransaction::with('customer')->findOrFail($history->service_id);
        $users = User::find(1);
        if ($history->status == 1) {
            $terms = Term::find(1);
        }else{
            $terms = Term::find(2);

        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        // $pdf = PDF::loadView('pages.kepalatoko.servis.nota-garansi-cetak-inkjet', [
        return View('pages.kepalatoko.servis.nota-garansi-cetak-inkjet', [
            'users' => $users,
            'items' => $items,
            'terms' => $terms,
            'imagePath' => $imagePath,
            'history' => $history,
        ]);

        $filename = 'Nota Pengambilan ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
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
        $data->id_customer = $request->id_customer;
        $data->estimasi_pengerjaan = $request->estimasi_pengerjaan;
        $data->fungsi_masuk = $request->fungsi_masuk;
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

        $data = HistoryGaransi::find($id);
        if ($request->status == 2 || $request->status == 3) {
            $data->tgl_selesai = date('Y-m-d');
        }else{
            $data->tgl_selesai = null;
        }
        $data->id_customer  = $request->id_customer;
        $data->teknisi_id  = $request->teknisi_id;
        $data->tindakan   =  !empty($request->tindakan) ? json_encode($request->tindakan) : [];
        $data->sparepart   =  !empty($request->sparepart) ? json_encode($request->sparepart) : [];
        $data->modal_sparepart = $request->modal_sparepart ?? 0;
        $data->total_biaya_tindakan = $request->total_biaya_tindakan;
        $data->total_biaya = $request->total_biaya;
        $data->catatan     = $request->catatan;
        $data->status     = $request->status;
        $data->fungsi_keluar = $request->fungsi_keluar;
        $data->save();

        if ($data->status == 3) {
            $servis = ServiceTransaction::with('user')->find($data->service_id);

            // if (!$servis) {

                if ($servis->tipe == 'Interface') {
                    $bonus = $servis->bonus_interface;
                } else {
                    $bonus = $servis->profit / 100;
                    $bonus *= $servis->persen_teknisi;
                }

                // dd($servis,$bonus);

                $tambahrefund = new Refund();
                $tambahrefund->servis_transaction_id = $servis->id;
                $tambahrefund->nominal = $bonus;
                $tambahrefund->nominal_servis = $servis->biaya ?? $servis->pay;
                $tambahrefund->teknisi_id = $servis->users_id ?? 0;
                $tambahrefund->cabang_id = getCabangId();
                $tambahrefund->save();

                if ($servis->biaya > 0) {
                    Expense::create([
                        'name' => 'Refund #'. $servis->nomor_servis,
                        'price' => $servis->biaya,
                        'users_id' => auth()->user()->id
                    ]);
                }
            // }
        }

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

        $servis2 = ServiceTransaction::with('user')->find($data->service_id);

        if ($request->total_biaya > 0) {
            Expense::create([
                'name' => 'Klaim Garansi #'. $servis2->nomor_servis,
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

        $customer = Customer::get();

        $serviceActions = ServiceAction::where('cabang_id',getCabangId())->get();
        $users = User::where('cabang_id',getCabangId())->where('role','!=','Investor')->get();
        return view('pages.kepalatoko.history.edit', compact(
            'users','historyGaransi','serviceTransactions','products','serviceActions','customer'
        ));
    }

    public function getDetailHistory($id)
    {
        try {
            $data = HistoryGaransi::where('service_id', $id)
                ->with(['service', 'teknisi', 'penerima'])
                ->get();
            // return response()->json($data);

            // Tambahkan nama tindakan langsung di sini
            $data->transform(function ($item) {

                if (!empty($item->tindakan)) {
                    $tindakans = json_decode($item->tindakan, true) ?? [];

                    $listTindakan = [];
                    foreach ($tindakans as $t) {

                        $action = \App\Models\ServiceAction::find($t['id']);

                        $listTindakan[] = [
                            'nama' => $action->nama_tindakan ?? $t['id_manual'],
                            'harga' => $t['harga'],
                        ];
                    }

                    $item->tindakan_list = $listTindakan;
                    return $item;
                }else{
                    $item->tindakan_list = [];
                }
                return $item;
            });

            return response()->json($data);

        } catch (\Throwable $th) {
            return response()->json([]);
        }
    }


}
