<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\AttendanceMatrixExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cabangId = getCabangId();

        // Logika User List untuk Filter
        $users = $user->role === 'Kepala Toko'
            ? User::where('cabang_id', $cabangId)->select('id', 'name')->whereIn('role', ['Teknisi', 'Sales', 'Admin Toko'])->get()
            : User::where('cabang_id', $cabangId)->where('id', $user->id)->select('id', 'name')->whereIn('role', ['Teknisi', 'Sales', 'Admin Toko'])->get();

        // Logika Statistik (Dipindahkan dari Livewire render)
        $today = Carbon::today();

        // Query Dasar Statistik
        $statsQuery = Attendance::where('cabang_id', $cabangId);

        // Jika bukan Kepala Toko, filter statistik hanya punya user login
        if ($user->role !== 'Kepala Toko') {
            $statsQuery->where('user_id', $user->id);
        }

        $hariIniMasuk = (clone $statsQuery)->where('type', 'masuk')->whereDate('created_at', $today)->count();
        $hariIniPulang = (clone $statsQuery)->where('type', 'pulang')->whereDate('created_at', $today)->count();
        $hariIniTotal = $hariIniMasuk + $hariIniPulang;

        return view('pages.kepalatoko.attendance', compact(
            'users',
            'hariIniMasuk',
            'hariIniPulang',
            'hariIniTotal'
        ));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::where('cabang_id', getCabangId())->with('user')->latest();

        // 1. Filter Role (Jika bukan Kepala Toko, hanya lihat punya sendiri)
        if ($user->role !== 'Kepala Toko') {
            $query->where('user_id', $user->id);
        } elseif ($request->filter_role_user_id) {
            // Filter dropdown user (khusus Kepala Toko)
            $query->where('user_id', $request->filter_role_user_id);
        }

        // 2. Filter Date Range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('tanggal', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '">';
            })
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                return $row->user->name ?? '-';
            })
            ->editColumn('type', function ($row) {
                return ucfirst($row->type);
            })
            ->editColumn('waktu', function ($row) {
                $formatted = Carbon::parse($row->created_at)->translatedFormat('l, d F Y H:i:s');
                $statusBadge = '';

                // Logika Badge Terlambat/Tepat Waktu
                if (ucfirst($row->type) == 'Masuk') {
                    if ($row->telat == 1) {
                        $statusBadge = '<span class="ml-1 text-red-500 font-semibold">| Terlambat</span>';
                    } else {
                        $statusBadge = '<span class="ml-1 text-green-500 font-semibold">| Tepat Waktu</span>';
                    }
                }
                return $formatted . $statusBadge;
            })
            ->editColumn('nominal_potongan', function ($row) {
                return 'Rp ' . number_format($row->nominal_potongan, 0, ',', '.');
            })
            ->addColumn('lokasi', function ($row) {
                if ($row->lat && $row->lng) {
                    // Panggil fungsi JS showMapModal
                    return '<button onclick="showMapModal(' . $row->lat . ', ' . $row->lng . ')" class="text-indigo-600 hover:underline">Lihat Lokasi</button>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            ->addColumn('foto', function ($row) {
                if ($row->photo) {
                    $url = asset('storage/' . $row->photo);
                    // Panggil fungsi JS showPhotoModal (perbaikan nama fungsi agar konsisten)
                    return '<button class="text-indigo-600" onclick="showPhotoModal(\'' . $url . '\')">Lihat Foto</button>';
                }
                return '<span class="text-sm text-slate-400">-</span>';
            })
            ->editColumn('note', function ($row) {
                return $row->note ? '<span class="text-slate-400">' . $row->note . '</span>' : '<span class="text-slate-400">-</span>';
            })
            ->addColumn('aksi', function ($row) {
                // Delete Button Form
                $url = route('master-absensi.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <form method="POST" action="' . $url . '" onsubmit="return confirm(\'Yakin hapus?\')">
                        ' . $csrf . $method . '
                        <button class="text-rose-500 hover:text-rose-700">Hapus</button>
                    </form>
                ';
            })
            ->rawColumns(['checkbox', 'waktu', 'lokasi', 'foto', 'note', 'aksi'])
            ->make(true);
    }

    // ... (Function store, destroy, deleteSelected, export SAMA PERSIS seperti kode awal Anda)
    // Saya copy paste agar lengkap contextnya
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
            'photo' => 'nullable|string',
            'photo_file' => 'nullable|image|max:2048',
            'shift_jam_selected' => 'required', // Wajib dipilih
        ]);

        $user = Auth::user();
        $tanggal = Carbon::parse($request->tanggal)->toDateString();

        // --- 1. Cek Double Input ---
        $sudahMasuk = Attendance::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->where('type', 'masuk')
            ->exists();

        $sudahPulang = Attendance::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->where('type', 'pulang')
            ->exists();

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

        // --- 2. Proses Upload Foto ---
        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $photoPath = $request->file('photo_file')->store('attendances', 'public');
        } elseif ($request->photo) {
            if (preg_match('/^data:image\/(\w+);base64,/', $request->photo, $type)) {
                $data = substr($request->photo, strpos($request->photo, ',') + 1);
                $data = base64_decode($data);
                $ext = $type[1];
                $filename = 'att_' . time() . '_' . $user->id . '.' . $ext;
                Storage::disk('public')->put('attendances/' . $filename, $data);
                $photoPath = 'attendances/' . $filename;
            }
        }

        $potonganTelat = 0;
        $telat = 0;

        $shift = Shift::where('worker_id', $user->workers_id)->first();

        if ($shift) {
            if ($request->type === 'masuk') {
                $jamJadwal = $request->shift_jam_selected . ':00';

                $waktuAktual = date('H:i:s');

                if ($waktuAktual > $jamJadwal) {
                    $potonganTelat = $shift->potongan_terlambat ?? 0;
                    $telat = 1;
                }
            }
        } else {
            return redirect()->back()->with('error', 'Anda belum memiliki shift kerja, hubungi admin.');
        }

        // --- 4. Simpan Data ---
        Attendance::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'tanggal' => $request->tanggal,
            'waktu' => Carbon::parse($request->waktu), // Ini jam dari Client/HP user (untuk display)
            'lat' => $request->lat,
            'lng' => $request->lng,
            'photo' => $photoPath,
            // Simpan info shift yang dipilih ke notes agar admin tahu dia ambil shift jam berapa
            'note' => $request->note . ' (Shift: ' . $request->shift_jam_selected . ')',
            'nominal_potongan' => $potonganTelat,
            'telat' => $telat,
            'cabang_id' => getCabangId(),
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
