<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Expense;
use App\Models\ServiceTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables; // Tambahkan ini

class RefundController extends Controller
{
    public function index(Request $request)
    {
        // === LOGIC DATA TABLES ===
        if ($request->ajax()) {
            $query = Refund::where('cabang_id', getCabangId())
                ->with(['ServiceTransaction', 'teknisi'])
                ->orderBy('created_at', 'desc');

            // Filter Role Teknisi
            if (auth()->user()->role === 'Teknisi') {
                $query->where('teknisi_id', auth()->id());
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko') {
                        return '<input class="table-item form-checkbox" type="checkbox" value="' . $row->id . '" />';
                    }
                    return '';
                })
                ->addColumn('nomor_servis', function ($row) {
                    return '#' . ($row->ServiceTransaction->nomor_servis ?? '-');
                })
                ->editColumn('nominal', function ($row) {
                    return 'Rp ' . number_format($row->nominal, 2, ',', '.');
                })
                ->editColumn('nominal_servis', function ($row) {
                    return 'Rp ' . number_format($row->nominal_servis, 2, ',', '.');
                })
                ->addColumn('teknisi_name', function ($row) {
                    return optional($row->teknisi)->name ?? '-';
                })
                ->editColumn('period', function ($row) {
                    return $row->period ? Carbon::parse($row->period)->format('F Y') : '-';
                })
                ->addColumn('action', function ($row) {
                    if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko') {
                        $editUrl = route('refund.edit', $row->id);
                        $deleteUrl = route('refund.destroy', $row->id);
                        $csrf = csrf_field();
                        $method = method_field('delete');

                        return '
                        <div class="flex space-x-2 justify-center">
                            <a href="' . $editUrl . '" class="text-slate-400 hover:text-slate-500">
                                Edit
                            </a>
                            <form action="' . $deleteUrl . '" method="post" onsubmit="return confirm(\'Yakin ingin hapus?\')">
                                ' . $csrf . '
                                ' . $method . '
                                <button class="text-rose-500">Hapus</button>
                            </form>
                        </div>';
                    }
                    return '';
                })
                ->rawColumns(['checkbox', 'action']) // Render HTML
                ->make(true);
        }

        // === VIEW NORMAL ===
        // Untuk dropdown servis di Modal Tambah
        $servis = ServiceTransaction::where('cabang_id', getCabangId())
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        return view('pages.kepalatoko.master.refund', [
            'servis' => $servis,
        ]);
    }

    // ... (Method store, update, destroy, cetak, deleteSelected, serviceDetail TETAP SAMA seperti kode asli Anda) ...
    public function cetak(Request $request)
    {
        $users = User::find(1);
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $query = Refund::where('cabang_id', getCabangId())->with(['ServiceTransaction.user', 'teknisi'])
            ->whereBetween('created_at', [$start_date, Carbon::parse($end_date)->endOfDay()])
            ->where('cabang_id', getCabangId())
            ->orderBy('created_at', 'desc');

        if (auth()->user()->role === 'Teknisi') {
            $query->where('teknisi_id', auth()->id());
        }

        $refunds = $query->get();

        $totalRefund = $refunds->sum('nominal');
        $totalRefundServis = $refunds->sum('nominal_servis');
        $totalData = $refunds->count();

        $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-refund', [
            'users' => $users,
            'imagePath' => $imagePath,
            'refunds' => $refunds,
            'totalRefund' => $totalRefund,
            'totalData' => $totalData,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'totalRefundServis' => $totalRefundServis,
        ]);

        $filename = 'Laporan Pengembalian Dana ' . $start_date . ' sd ' . $end_date . '.pdf';
        return $pdf->stream($filename);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Not implemented'], 404);
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        if (!is_array($selectedIds) || empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 422);
        }

        Refund::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data pengembalian dana berhasil dihapus.']);
    }

    public function store(Request $request)
    {
        $request->merge([
            'nominal_servis' => str_replace('.', '', $request->nominal_servis),
        ]);
        $data = $request->all();

        if (!empty($data['period'])) {
            $data['period'] = $data['period'] . '-01';
        }

        $servis = ServiceTransaction::find($data['servis_transaction_id']);
        if ($servis) {
            $data['teknisi_id'] = $servis->users_id ?? $servis->user_id ?? null;
        }
        $data['cabang_id'] = getCabangId();
        Refund::create($data);

        if ($servis->biaya > 0) {
            Expense::create([
                'name' => 'Refund #'. $servis->nomor_servis,
                'price' => $servis->biaya,
                'tipe' => 1,
                'users_id' => auth()->user()->id,
                'cabang_id' => getCabangId()
            ]);
        }

        toast('Refund berhasil ditambahkan.', 'success');
        return redirect()->route('refund.index');
    }

    public function edit($id)
    {
        $item = Refund::findOrFail($id);
        $servis = ServiceTransaction::with('user')->orderBy('id', 'desc')->get();

        return view('pages.kepalatoko.master.refund-edit', [
            'item' => $item,
            'servis' => $servis,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'nominal_servis' => str_replace('.', '', $request->nominal_servis),
        ]);
        $data = $request->all();

        if (!empty($data['period'])) {
            $data['period'] = $data['period'] . '-01';
        }

        $servis = ServiceTransaction::find($data['servis_transaction_id']);
        if ($servis) {
            $data['teknisi_id'] = $servis->users_id ?? $servis->user_id ?? null;
        }

        $item = Refund::findOrFail($id);
        $item->update($data);

        toast('Refund berhasil diupdate.', 'success');
        return redirect()->route('refund.index');
    }

    public function destroy($id)
    {
        $item = Refund::findOrFail($id);
        $item->delete();

        toast('Refund berhasil dihapus.', 'success');
        return redirect()->route('refund.index');
    }

    public function serviceDetail($id)
    {
        $servis = ServiceTransaction::with('user')->find($id);
        if (!$servis) {
            return response()->json(['message' => 'Servis tidak ditemukan'], 404);
        }

        if ($servis->tipe == 'Interface') {
            $bonus = $servis->bonus_interface;
        } else {
            $bonus = $servis->profit / 100;
            $bonus *= $servis->persen_teknisi;
        }

        return response()->json([
            'id' => $servis->id,
            'teknisi_id' => $servis->users_id ?? $servis->user_id ?? null,
            'teknisi_name' => optional($servis->user)->name ?? null,
            'nominal' => $bonus,
            'nominal_servis' => $servis->biaya ?? $servis->pay ?? null,
        ]);
    }
}
