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
        $serviceTransactions = ServiceTransaction::all();
        $products = Product::whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $serviceActions = ServiceAction::all();
        $users = User::all();

        return view('pages.kepalatoko.history.garansi', compact(
            'serviceTransactions',
            'products',
            'serviceActions',
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
        $data->total_biaya = $request->total_biaya;
        $data->catatan     = $request->catatan;
        $data->status     = 1;
        $data->save();

        if (!empty($request->sparepart)) {
            foreach ($request->sparepart as $row) {
                $spareparts = Product::find($row['id']);
                if ($spareparts) {
                    $spareparts->stok -= (int)$row['qty'];
                    $spareparts->save();
                }
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
