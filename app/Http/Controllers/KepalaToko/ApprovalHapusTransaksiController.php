<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use App\Models\TransactionDeleteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalHapusTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = TransactionDeleteRequest::with(['user', 'approver'])
            ->where('cabang_id', getCabangId())
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15);
        $pending_count = TransactionDeleteRequest::where('cabang_id', getCabangId())->where('status', 'pending')->count();

        return view('pages.kepalatoko.approval-hapus.index', compact('requests', 'pending_count', 'status'));
    }

    public function approve($id)
    {
        $item = TransactionDeleteRequest::where('cabang_id', getCabangId())->findOrFail($id);

        if ($item->transaksi_type === 'servis') {
            $trans = ServiceTransaction::find($item->transaksi_id);
            if ($trans) {
                $trans->delete();
            }
        } elseif ($item->transaksi_type === 'pos') {
            $order = Order::find($item->transaksi_id);
            if ($order) {
                $order->delete();
                OrderDetail::where('orders_id', $order->id)->delete();
            }
        }

        $item->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        toast('Permintaan hapus disetujui. Transaksi telah dihapus.', 'success');
        return back();
    }

    public function reject($id)
    {
        $item = TransactionDeleteRequest::where('cabang_id', getCabangId())->findOrFail($id);

        $item->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        toast('Permintaan hapus transaksi telah ditolak.', 'info');
        return back();
    }
}
