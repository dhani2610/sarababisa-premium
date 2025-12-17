<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Overtime;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MasterOvertimeController extends Controller
{
    public function index()
    {
        $overtimes = Overtime::where('cabang_id',getCabangId())->with('user')->latest()->paginate(10);
        $stats = $this->getStats();

        return view('pages.kepalatoko.master.overtime', compact('overtimes', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'required|after:waktu_start',
            'keterangan' => 'nullable|string',
        ]);

        // dd($request->all());

        $user = Auth::user();
        $store = StoreSetting::where('cabang_id',getCabangId())->first();
        $ratePerHour = $store->nominal_overtime ?? 0;

        $start = Carbon::parse($request->waktu_start);
        $end = Carbon::parse($request->waktu_end);
        $hours = $end->diffInMinutes($start) / 60;
        $nominal = round($hours * $ratePerHour);

        $data = Overtime::create([
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

        if (empty($raw)) {
            return [];
        }

        // 1) coba json decode (kita kirim JSON.stringify(selected))
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_map('intval', $decoded);
        }

        // 2) fallback: "1,2,3"
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
