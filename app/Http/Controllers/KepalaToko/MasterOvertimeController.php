<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Overtime;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MasterOvertimeController extends Controller
{
    public function index(Request $request)
    {
        // === LOGIC YAJRA DATATABLES ===
        if ($request->ajax()) {
            $user = Auth::user();
            $query = Overtime::where('cabang_id', getCabangId())->with('user');

             if ($user->role !== 'Kepala Toko') {
                $query->where('id_user', $user->id);
            }

            $query->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) use ($user) {
                    if ($user->role == 'Kepala Toko') {
                        return '<input type="checkbox" value="' . $row->id . '" class="form-checkbox table-item">';
                    }
                    return '';
                })
                ->addColumn('nama', function ($row) {
                    return $row->user->name ?? '-';
                })
                ->editColumn('tanggal', function ($row) {
                    return $row->tanggal; // Format Y-m-d bawaan DB, bisa diubah jika perlu
                })
                ->editColumn('nominal_overtime', function ($row) {
                    return 'Rp ' . number_format($row->nominal_overtime, 0, ',', '.');
                })
                ->addColumn('status_label', function ($row) {
                    $color = 'bg-yellow-100 text-yellow-700';
                    if ($row->status === 'approved') $color = 'bg-green-100 text-green-700';
                    if ($row->status === 'rejected') $color = 'bg-red-100 text-red-700';

                    return '<span class="px-2 py-1 rounded text-xs ' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('aksi', function ($row) use ($user) {
                    // Siapkan data JSON untuk tombol Edit
                    $editData = [
                        'id' => $row->id,
                        'tanggal' => $row->tanggal,
                        'waktu_start' => $row->waktu_start,
                        'waktu_end' => $row->waktu_end,
                        'keterangan' => $row->keterangan,
                    ];
                    $json = htmlspecialchars(json_encode($editData), ENT_QUOTES, 'UTF-8');

                    $btnEdit = '';
                    $btnDelete = '';

                    // Logic tombol Edit/Delete (Pending atau Kepala Toko)
                    if ($row->status == 'pending' || $user->role == 'Kepala Toko') {
                        $btnEdit = '<button type="button" class="text-blue-500 hover:underline mr-2" onclick="openEditModal('.$json.')">Edit</button>';

                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $urlDestroy = route('master-overtime.destroy', $row->id);

                        $btnDelete = '
                            <form action="'.$urlDestroy.'" method="POST" class="inline-block" onsubmit="return confirm(\'Hapus data ini?\')">
                                '.$csrf . $method.'
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        ';
                    }

                    return '<div class="flex justify-center gap-2">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['checkbox', 'status_label', 'aksi'])
                ->make(true);
        }

        // Non-ajax: return view with stats
        $stats = $this->getStats();
        return view('pages.kepalatoko.master.overtime', compact('stats'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tanggal' => 'required|date',
                'waktu_start' => 'required',
                'waktu_end' => 'required',
                'keterangan' => 'nullable|string',
            ]);

            $user = Auth::user();
            $store = StoreSetting::where('cabang_id',getCabangId())->first();
            $ratePerHour = $store->nominal_overtime ?? 0;

            $start = Carbon::parse($request->waktu_start);
            $end = Carbon::parse($request->waktu_end);
            $hours = $end->diffInMinutes($start) / 60;
            $nominal = round($hours * $ratePerHour);

            Overtime::create([
                'id_user' => $user->id,
                'tanggal' => $request->tanggal,
                'waktu_start' => $request->waktu_start,
                'waktu_end' => $request->waktu_end,
                'nominal_overtime' => $nominal,
                'keterangan' => $request->keterangan,
                'status' => 'pending',
                'cabang_id' => getCabangId(),
            ]);

            toast('Data Lembur berhasil disimpan.', 'success');
            return back();
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            toast('Data Lembur gagal disimpan.', 'error');
            return back();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'required|after:waktu_start',
            'keterangan' => 'nullable|string',
        ]);

        $store = StoreSetting::where('cabang_id',getCabangId())->first();
        $ratePerHour = $store->nominal_overtime ?? 0;

        $start = Carbon::parse($request->waktu_start);
        $end = Carbon::parse($request->waktu_end);
        $hours = $end->diffInMinutes($start) / 60;
        $nominal = round($hours * $ratePerHour);

        $overtime = Overtime::findOrFail($id);
        $overtime->update([
            'tanggal' => $request->tanggal,
            'waktu_start' => $request->waktu_start,
            'waktu_end' => $request->waktu_end,
            'nominal_overtime' => $nominal,
            'keterangan' => $request->keterangan,
        ]);

        toast('Data Lembur berhasil diupdate.', 'success');
        return back();
    }

    public function destroy($id)
    {
        Overtime::findOrFail($id)->delete();
        toast('Data Lembur berhasil dihapus.', 'success');
        return back();
    }

    // Method approve, getStats, parseIdsFromRequest, deleteSelected, approveSelected, rejectSelected
    // ... (TETAP SAMA SEPERTI KODE ASLI ANDA, TIDAK ADA PERUBAHAN LOGIC) ...
    // Copy paste method sisanya di sini.

    public function approve($id)
    {
        if (Auth::user()->role !== 'Kepala Toko') {
            abort(403, 'Anda tidak memiliki akses untuk melakukan approve.');
        }

        $overtime = Overtime::findOrFail($id);
        $overtime->update([
            'status' => 'approved',
            'approve_by' => Auth::user()->id
        ]);

        toast('Overtime berhasil diapprove.', 'success');
        return back();
    }

    private function getStats()
    {
        $hariIni = Carbon::today();
        $bulanIni = Carbon::now()->month;

        return [
            'hariIni' => [
                'total' => Overtime::whereDate('tanggal', $hariIni)->count(),
                'total_nominal' => Overtime::whereDate('tanggal', $hariIni)->sum('nominal_overtime'),
            ],
            'bulanIni' => [
                'total' => Overtime::whereMonth('tanggal', $bulanIni)->count(),
                'total_nominal' => Overtime::whereMonth('tanggal', $bulanIni)->sum('nominal_overtime'),
            ],
        ];
    }

    protected function parseIdsFromRequest(\Illuminate\Http\Request $request): array
    {
        $raw = $request->input('ids');
        if (empty($raw)) return [];
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) return array_map('intval', $decoded);
        if (is_string($raw)) {
            $arr = array_filter(array_map('trim', explode(',', $raw)));
            return array_map('intval', $arr);
        }
        return [];
    }

    public function deleteSelected(Request $request)
    {
        $ids = $this->parseIdsFromRequest($request);
        if (!$ids) {
            toast('Tidak ada data yang dipilih.', 'warning');
            return back();
        }
        Overtime::whereIn('id', $ids)->delete();
        toast(count($ids) . ' data berhasil dihapus.', 'success');
        return back();
    }

    public function approveSelected(Request $request)
    {
        $ids = $this->parseIdsFromRequest($request);
        if (!$ids) {
            toast('Tidak ada data yang dipilih.', 'warning');
            return back();
        }
        Overtime::whereIn('id', $ids)->update([
            'status' => 'approved',
            'approve_by' => Auth::user()->id
        ]);
        toast(count($ids) . ' data berhasil diapprove.', 'success');
        return back();
    }

    public function rejectSelected(Request $request)
    {
        $ids = $this->parseIdsFromRequest($request);
        if (!$ids) {
            toast('Tidak ada data yang dipilih.', 'warning');
            return back();
        }
        Overtime::whereIn('id', $ids)->update([
            'status' => 'rejected',
            'approve_by' => Auth::user()->id
        ]);
        toast(count($ids) . ' data berhasil direject.', 'success');
        return back();
    }
}
