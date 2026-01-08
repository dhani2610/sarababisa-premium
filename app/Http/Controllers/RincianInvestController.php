<?php

namespace App\Http\Controllers;

use App\Models\RincianInvest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RincianInvestController extends Controller
{
    public function index(Request $request)
    {
        // === LOGIC DATATABLES ===
        if ($request->ajax()) {
            $query = RincianInvest::with('investor')
                ->where('cabang_id', getCabangId())
                ->latest();

            // 1. Filter Tipe (Tab)
            if ($request->has('filter_tipe') && $request->filter_tipe != 0) {
                $query->where('tipe', $request->filter_tipe);
            }

            // 2. Filter Investor (Dropdown)
            if ($request->has('filter_investor') && $request->filter_investor != 0) {
                $query->where('id_investor', $request->filter_investor);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    if (Auth::user()->role != 'Investor') {
                        return '<input type="checkbox" class="form-checkbox table-item" value="' . $row->id . '">';
                    }
                    return '';
                })
                ->addColumn('investor_name', function ($row) {
                    return $row->investor ? $row->investor->name : '-';
                })
                ->editColumn('tanggal', function ($row) {
                    return $row->tanggal ? date('d/m/Y', strtotime($row->tanggal)) : '-';
                })
                ->editColumn('tipe', function ($row) {
                    if ($row->tipe == 1) {
                        return '<span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">Masuk</span>';
                    } elseif ($row->tipe == 2) {
                        return '<span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">Pembagian</span>';
                    } else {
                        return '<span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">Lain-lain</span>';
                    }
                })
                ->editColumn('nominal', function ($row) {
                    return 'Rp' . number_format($row->nominal, 0, ',', '.');
                })
                ->editColumn('upload_bukti_tf', function ($row) {
                    if ($row->upload_bukti_tf) {
                        $url = asset('storage/' . $row->upload_bukti_tf);
                        // Menggunakan fungsi showBukti yang sudah ada di frontend
                        return '<img src="' . $url . '" alt="Bukti" class="bukti-img" onclick="showBukti(\'' . $url . '\')">';
                    }
                    return '-';
                })
                ->addColumn('action', function ($row) {
                    if (Auth::user()->role == 'Investor') return '';

                    // Data untuk modal edit (dikirim ke JS)
                    $data = [
                        'id' => $row->id,
                        'id_investor' => $row->id_investor,
                        'tanggal' => date('Y-m-d', strtotime($row->tanggal)),
                        'tipe' => $row->tipe,
                        'nominal' => number_format($row->nominal, 0, ',', '.'),
                        'keterangan' => $row->keterangan,
                        'bukti_url' => asset('storage/' . $row->upload_bukti_tf)
                    ];
                    $jsonData = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');

                    $editBtn = '<button type="button" class="btn-sm bg-amber-500 hover:bg-amber-600 text-white mr-1" onclick="openEditModal('.$jsonData.')">Edit</button>';

                    $deleteUrl = route('rincian-invest.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    $deleteBtn = '
                        <form action="'.$deleteUrl.'" method="POST" class="inline-block" onsubmit="return confirm(\'Yakin hapus data ini?\')">
                            '.$csrf.'
                            '.$method.'
                            <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Hapus</button>
                        </form>
                    ';

                    return '<div class="flex justify-center items-center">'.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['checkbox', 'tipe', 'upload_bukti_tf', 'action'])
                ->make(true);
        }

        // === DATA AWAL VIEW ===
        $investors = User::where('role', 'Investor')->get(); // Sesuaikan query jika ada filter cabang di User

        // Hitung Total untuk Tabs (Masuk, Pembagian, Lain)
        $cabangId = getCabangId();
        $totals = [
            'masuk' => RincianInvest::where('cabang_id', $cabangId)->where('tipe', 1)->sum('nominal'),
            'pembagian' => RincianInvest::where('cabang_id', $cabangId)->where('tipe', 2)->sum('nominal'),
            'lain' => RincianInvest::where('cabang_id', $cabangId)->where('tipe', 3)->sum('nominal'),
        ];

        return view('pages.kepalatoko.rincian-invest.index', compact('investors', 'totals'));
    }

    // === STORE, UPDATE, DESTROY, BULK DELETE (TETAP SAMA) ===

    public function store(Request $request)
    {
        $request->merge([
            'nominal' => (int) str_replace('.', '', $request->nominal),
        ]);

        $request->validate([
            'tipe' => 'required|integer|in:1,2,3',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'upload_bukti_tf' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'id_investor' => 'nullable|integer|exists:users,id',
            'keterangan' => 'nullable|string',
        ]);

        $path = $request->file('upload_bukti_tf')->store('invest_bukti', 'public');

        RincianInvest::create([
            'tipe' => $request->tipe,
            'tanggal' => $request->tanggal,
            'nominal' => $request->nominal,
            'id_investor' => $request->id_investor,
            'upload_bukti_tf' => $path,
            'keterangan' => $request->keterangan,
            'cabang_id' => getCabangId(),
        ]);

        return redirect()->route('rincian-invest.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'nominal' => (int) str_replace('.', '', $request->nominal),
        ]);

        $invest = RincianInvest::findOrFail($id);

        $request->validate([
            'tipe' => 'required|integer|in:1,2,3',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'upload_bukti_tf' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'id_investor' => 'nullable|integer|exists:users,id',
            'keterangan' => 'nullable|string',
        ]);

        $path = $invest->upload_bukti_tf;

        if ($request->hasFile('upload_bukti_tf')) {
            if (file_exists(storage_path('app/public/' . $invest->upload_bukti_tf))) {
                unlink(storage_path('app/public/' . $invest->upload_bukti_tf));
            }
            $path = $request->file('upload_bukti_tf')->store('invest_bukti', 'public');
        }

        $invest->update([
            'tipe' => $request->tipe,
            'tanggal' => $request->tanggal,
            'nominal' => $request->nominal,
            'id_investor' => $request->id_investor,
            'upload_bukti_tf' => $path,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('rincian-invest.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $invest = RincianInvest::findOrFail($id);
        if ($invest->upload_bukti_tf && file_exists(storage_path('app/public/' . $invest->upload_bukti_tf))) {
            unlink(storage_path('app/public/' . $invest->upload_bukti_tf));
        }
        $invest->delete();

        return redirect()->route('rincian-invest.index')->with('success', 'Data berhasil dihapus');
    }

    public function bulkDelete(Request $request)
    {
        RincianInvest::whereIn('id', $request->selectedIds)->delete();
        return response()->json(['success' => true]);
    }
}
