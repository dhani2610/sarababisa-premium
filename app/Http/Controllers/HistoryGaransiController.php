<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\HistoryGaransi;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HistoryGaransiController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('pages.kepalatoko.history.garansi', compact(
            'users'
        ));
    }
    public function edit($id)
    {
        $serviceTransactions = ServiceTransaction::all();
        $products = Product::whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::all();
        $users = User::all();
        $item = HistoryGaransi::find($id);

        return view('pages.kepalatoko.history.edit', compact(
            'item',
            'serviceTransactions',
            'products',
            'serviceActions',
            'users'
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'date'          => 'required|date',
            'service_id'    => 'required|exists:service_transactions,id',
            'penerima_id'   => 'required|exists:users,id',
            'teknisi_id'    => 'required|exists:users,id',
            'tindakan'      => 'required|array',
            'sparepart'     => 'nullable|array',
            'total_biaya'   => 'required|numeric',
            'catatan'       => 'nullable|string',
        ]);

        $data = new HistoryGaransi();
        $data->date        = $request->date;
        $data->service_id  = $request->service_id;
        $data->penerima_id = $request->penerima_id;
        $data->teknisi_id  = $request->teknisi_id;
        $data->tindakan    = json_encode($request->tindakan);
        $data->sparepart   =  !empty($request->sparepart) ? json_encode($request->sparepart) : null;
        $data->modal_sparepart = $request->modal_sparepart;
        $data->total_biaya_tindakan = $request->total_biaya_tindakan;
        $data->total_biaya = $request->total_biaya;
        $data->catatan     = $request->catatan;
        $data->status     = 1;
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

                // Harga jual
                $harga_jual = $spareparts->harga_jual ?? 0;

                // Buat Order
                $order = new Order();
                $order->customers_id   = $service->customers_id;
                $order->users_id       = $request->teknisi_id ?? null;
                $order->order_date     = Carbon::today()->locale('id')->translatedFormat('d F Y');
                $order->total_products = 1;
                $order->sub_total      = $harga_jual;
                $order->invoice_no     = '' . mt_rand(date('Ymd00'), date('Ymd99'));
                $order->nama_pelanggan = $customer->nama;
                $order->payment_method = "Tunai";
                $order->pay            = $harga_jual;
                $order->due            = 0;
                $order->is_approve     = 'Setuju';
                $order->tgl_disetujui  = Carbon::today();
                $order->save();

                // Hitung persen sales (cek dulu user nya ada/tidak)
                $persen_sales = null;
                if (!empty($request->teknisi_id) && $request->teknisi_id != 1) {
                    $user = User::find($request->teknisi_id);
                    if ($user) {
                        $persen_sales = $user->persen;
                    }
                }

                // Buat Order Detail
                $orderDetail = new OrderDetail();
                $orderDetail->orders_id   = $order->id;
                $orderDetail->users_id    = $request->teknisi_id ?? null;
                $orderDetail->products_id = $spareparts->id;
                $orderDetail->product_name= $spareparts->product_name;
                $orderDetail->quantity    = (int)$row['qty'];
                $orderDetail->price       = $harga_jual;
                $orderDetail->total       = $harga_jual;
                $orderDetail->sub_total   = $harga_jual;
                $orderDetail->modal       = $spareparts->harga_modal;
                $orderDetail->profit      = $harga_jual - $spareparts->harga_modal;
                $orderDetail->persen_sales= $persen_sales;
                $orderDetail->profit_toko = $persen_sales
                    ? ($harga_jual - $spareparts->harga_modal) - (($spareparts->harga_jual - $spareparts->harga_modal) / 100 * $persen_sales)
                    : $harga_jual - $spareparts->harga_modal;
                $orderDetail->garansi     = date('Y-m-d');
                $orderDetail->product_discount_amount = 0;
                $orderDetail->save();
            }
        }


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


    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required',
            'penerima_id' => 'required',
            'teknisi_id' => 'required',
            'status' => 'required|in:1,2,3',
        ]);

        $item = HistoryGaransi::findOrFail($id);

        $item->update([
            'date' => $request->date,
            'service_id' => $request->service_id,
            'penerima_id' => $request->penerima_id,
            'teknisi_id' => $request->teknisi_id,
            'tindakan' => json_encode($request->tindakan ?? []),
            'sparepart' => json_encode($request->sparepart ?? []),
            'total_biaya' => $request->total_biaya ?? 0,
            'catatan' => $request->catatan,
            'status' => $request->status,
        ]);

        return redirect()->route('history-garansi.index')->with('success', 'History Garansi berhasil diupdate!');
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

        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil dihapus & stok sparepart dikembalikan');
    }
}
