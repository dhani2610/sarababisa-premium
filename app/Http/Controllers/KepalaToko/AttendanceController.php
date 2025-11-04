<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\AttendanceMatrixExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{

    public function index()
    {
        return view('pages.kepalatoko.attendance');
    }
    public function export(Request $request)
    {
        $bulan = $request->get('bulan');
        if (!$bulan) {
            return back()->with('error', 'Pilih bulan terlebih dahulu.');
        }

        $fileName = 'Laporan_Absensi_' . date('F_Y', strtotime($bulan)) . '.xlsx';
        return Excel::download(new AttendanceMatrixExport($bulan), $fileName);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:masuk,pulang',
            'tanggal' => 'required|date',
            'waktu' => 'required|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'photo' => 'nullable|string', // base64 from client or filename from file input
            'photo_file' => 'nullable|image|max:2048' // fallback
        ]);

        $user = Auth::user();
        $tanggal = Carbon::parse($request->tanggal)->toDateString();

        // Cek apakah sudah absen masuk hari ini
        $sudahMasuk = Attendance::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->where('type', 'masuk')
            ->exists();

        // Cek apakah sudah absen pulang hari ini
        $sudahPulang = Attendance::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->where('type', 'pulang')
            ->exists();

        // Validasi logika absensi
        if ($request->type === 'masuk' && $sudahMasuk) {
            return redirect()->back()->with('error', 'Anda sudah absen masuk hari ini.');
        }

        if ($request->type === 'pulang') {
            if (!$sudahMasuk) {
                return redirect()->back()->with('error', 'Anda belum absen masuk, tidak bisa absen pulang.');
            }

            if ($sudahPulang) {
                return redirect()->back()->with('error', 'Anda sudah absen pulang hari ini.');
            }
        }

        // handle file upload if photo_file provided
        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $photoPath = $request->file('photo_file')->store('attendances', 'public');
        } elseif ($request->photo) {
            // photo is base64 data URL: save
            if (preg_match('/^data:image\/(\w+);base64,/', $request->photo, $type)) {
                $data = substr($request->photo, strpos($request->photo, ',') + 1);
                $data = base64_decode($data);
                $ext = $type[1];
                $filename = 'att_' . time() . '_' . $user->id . '.' . $ext;
                Storage::disk('public')->put('attendances/' . $filename, $data);
                $photoPath = 'attendances/' . $filename;
            }
        }

        Attendance::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'tanggal' => $request->tanggal,
            'waktu' => Carbon::parse($request->waktu),
            'lat' => $request->lat,
            'lng' => $request->lng,
            'photo' => $photoPath,
            'note' => $request->note,
        ]);

        return redirect()->route('master-absensi.index')->with('success', 'Absensi tersimpan.');
    }

    public function destroy($id)
    {
        $item = Attendance::findOrFail($id);
        if ($item->photo) Storage::disk('public')->delete($item->photo);
        $item->delete();
        return back()->with('success', 'Data absensi dihapus.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('selectedIds', []);
        $items = Attendance::whereIn('id', $ids)->get();
        foreach ($items as $it) {
            if ($it->photo) Storage::disk('public')->delete($it->photo);
            $it->delete();
        }
        return response()->json(['message' => 'Data absensi berhasil dihapus.']);
    }
}
