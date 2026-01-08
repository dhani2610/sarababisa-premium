<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\IzinRequest;
use App\Models\Izin;
use App\Models\Shift;
use App\Models\User;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MasterIzinController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $cabangId = getCabangId();

        // === LOGIC YAJRA DATATABLES ===
        if ($request->ajax()) {
            $query = Izin::where('cabang_id', $cabangId)->with('user')->latest();

            // Filter Role (Selain Kepala/Admin Toko hanya lihat data sendiri)
            if ($user->role !== 'Kepala Toko' && $user->role !== 'Admin Toko') {
                $query->where('user_id', $user->id);
            }

            // Filter Tipe (Dari Tabs)
            if ($request->filter_tipe && $request->filter_tipe != 'all') {
                $query->where('tipe', $request->filter_tipe);
            }

            // Filter Date Range
            if ($request->start_date && $request->end_date) {
                $query->where(function ($q) use ($request) {
                    $q->whereBetween('tanggal', [$request->start_date, $request->end_date])
                      ->orWhereBetween('tanggal_mulai', [$request->start_date, $request->end_date])
                      ->orWhereBetween('tanggal_selesai', [$request->start_date, $request->end_date]);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" value="' . $row->id . '" class="form-checkbox table-item">';
                })
                ->addColumn('nama_karyawan', function ($row) {
                    return $row->user->name ?? '-';
                })
                ->editColumn('tipe', function ($row) {
                    return ucfirst($row->tipe);
                })
                ->editColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)->format('d/m/Y');
                })
                ->addColumn('periode', function ($row) {
                    return Carbon::parse($row->tanggal_mulai)->format('d/m/Y') . ' - ' .
                           Carbon::parse($row->tanggal_selesai)->format('d/m/Y');
                })
                ->editColumn('nominal_potongan', function ($row) {
                    return 'Rp ' . number_format($row->nominal_potongan, 0, ',', '.');
                })
                ->editColumn('dokumen', function ($row) {
                    if ($row->dokumen) {
                        return '<a href="' . asset($row->dokumen) . '" target="_blank" class="text-indigo-600 hover:underline">Lihat Dokumen</a>';
                    }
                    return '<span class="text-slate-400">-</span>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 1) return 'DiSetujui';
                    if ($row->status == 0) return 'Pending';
                    if ($row->status == 2) return 'Ditolak';
                    return '-';
                })
                ->addColumn('aksi', function ($row) {
                    // Siapkan data JSON untuk tombol Edit (sesuai format inputan modal)
                    $editData = [
                        'id' => $row->id,
                        'user_id' => $row->user_id,
                        'tipe' => $row->tipe,
                        'status' => $row->status, // Penting untuk Kepala Toko
                        'tanggal' => $row->tanggal, // Format Y-m-d dari database cocok untuk input date
                        'nominal_potongan' => number_format($row->nominal_potongan, 0, ',', '.'), // Format tampilan untuk input mask
                        'keterangan' => $row->keterangan,
                        'tanggal_mulai' => $row->tanggal_mulai,
                        'tanggal_selesai' => $row->tanggal_selesai,
                    ];

                    // Encode JSON aman untuk HTML attribute
                    $json = htmlspecialchars(json_encode($editData), ENT_QUOTES, 'UTF-8');

                    $btnEdit = '<button type="button" class="text-indigo-500 hover:text-indigo-700 mr-2" onclick="openEditModal('.$json.')">Edit</button>';

                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $urlDestroy = route('master-izin.destroy', $row->id);

                    $btnDelete = '
                        <form action="'.$urlDestroy.'" method="POST" class="inline-block" onsubmit="return confirm(\'Yakin hapus?\')">
                            '.$csrf . $method.'
                            <button type="submit" class="text-rose-500 hover:text-rose-700">Delete</button>
                        </form>
                    ';

                    return '<div class="flex items-center justify-center">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['checkbox', 'dokumen', 'aksi'])
                ->make(true);
        }

        // === LOGIC STATISTIK (Dipindah dari Livewire) ===
        $today = Carbon::today();
        $statQuery = Izin::query();
        if ($user->role !== 'Kepala Toko' && $user->role !== 'Admin Toko') {
            $statQuery->where('user_id', $user->id);
        }
        $statQuery->where('cabang_id', $cabangId);

        $stats = [
            'hariIni' => [
                'izin' => (clone $statQuery)->whereDate('tanggal', $today)->where('tipe', 'izin')->count(),
                'sakit' => (clone $statQuery)->whereDate('tanggal', $today)->where('tipe', 'sakit')->count(),
                'alfa'  => (clone $statQuery)->whereDate('tanggal', $today)->where('tipe', 'alfa')->count(),
                'total' => (clone $statQuery)->whereDate('tanggal', $today)->count(),
            ]
        ];

        // List User untuk Dropdown Modal
        if ($user->role == 'Kepala Toko') {
            $users = User::where('cabang_id', $cabangId)->whereIn('role', ['Teknisi', 'Sales', 'Admin Toko'])->select('id', 'name')->get();
        } else if ($user->role == 'Admin Toko') {
            $users = User::where('cabang_id', $cabangId)->whereIn('role', ['Teknisi', 'Sales'])->select('id', 'name')->get();
        } else {
            $users = User::where('cabang_id', $cabangId)->where('id', $user->id)->select('id', 'name')->get();
        }

        $count = Izin::where('cabang_id', $cabangId)->count();

        return view('pages.kepalatoko.master.izin', compact('stats', 'users', 'count'));
    }

    // Method store, update, destroy, deleteSelected, sendMessage TETAP SAMA seperti kode awal Anda
    // ... (Paste method lainnya di sini tanpa perubahan) ...

    public function store(IzinRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('dokumen')) {
                $file = $request->file('dokumen');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/izin'), $filename);
                $data['dokumen'] = 'uploads/izin/' . $filename;
            }

            $store = StoreSetting::where('cabang_id',getCabangId())->first();
            $user = User::find($request->user_id);
            $shift = Shift::where('id',$user->shift_id)->first();

            if (empty($data['nominal_potongan'])) {
                switch ($data['tipe']) {
                    case 'izin': $data['nominal_potongan'] = $shift->potongan_izin ?? 0; break;
                    case 'sakit': $data['nominal_potongan'] = $shift->potongan_sakit ?? 0; break;
                    case 'alfa': $data['nominal_potongan'] = $shift->potongan_tidak_masuk ?? 0; break;
                    case 'cuti': $data['nominal_potongan'] = $shift->potongan_cuti ?? 0; break;
                    default: $data['nominal_potongan'] = 0; break;
                }
            } else {
                $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
            }

            $data['cabang_id'] = getCabangId();
            $izin = Izin::create($data);

            try {
                $this->sendTelegramNotification($izin, 'IZIN BARU DIBUAT');
            } catch (\Exception $e) {
                \Log::error("Gagal kirim Telegram: " . $e->getMessage());
            }

            toast('Data Izin berhasil disimpan.', 'success');
            return redirect()->route('master-izin.index');
        } catch (\Throwable $e) {
            toast('Data Izin gagal disimpan.', 'error');
            return redirect()->route('master-izin.index');
        }
    }

    public function update(IzinRequest $request, $id)
    {
        $data = $request->validated();
        $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
        $izin = Izin::findOrFail($id);

        if ($request->hasFile('dokumen')) {
            if ($izin->dokumen && file_exists(public_path($izin->dokumen))) {
                unlink(public_path($izin->dokumen));
            }
            $file = $request->file('dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/izin'), $filename);
            $data['dokumen'] = 'uploads/izin/' . $filename;
        }

        $izin->update($data);

        try {
            $this->sendTelegramNotification($izin, 'IZIN DI EDIT');
        } catch (\Exception $e) {
             \Log::error("Gagal kirim Telegram: " . $e->getMessage());
        }

        toast('Data Izin berhasil diperbarui.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function destroy($id)
    {
        $item = Izin::findOrFail($id);
        if ($item->dokumen && file_exists(public_path($item->dokumen))) {
            unlink(public_path($item->dokumen));
        }
        $item->delete();

        toast('Data Izin berhasil dihapus.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        $items = Izin::whereIn('id', $selectedIds)->get();
        foreach($items as $item) {
            if ($item->dokumen && file_exists(public_path($item->dokumen))) {
                unlink(public_path($item->dokumen));
            }
            $item->delete();
        }

        return response()->json(['message' => 'Data izin berhasil dihapus.']);
    }

    // Helper private untuk Telegram agar code lebih rapi
    private function sendTelegramNotification($izin, $title) {
        $user = User::find($izin->user_id);
        $userName = $user ? $user->name : '-';
        $tanggal = \Carbon\Carbon::parse($izin->tanggal)->locale('id')->translatedFormat('d F Y');
        $linkDokumen = $izin->dokumen ? '[' . basename($izin->dokumen) . '](' . asset($izin->dokumen) . ')' : '-';

        $statusStr = '';
        if ($title == 'IZIN DI EDIT') {
            if ($izin->status == 1) $statusStr = "👤 *Status:* DiSetujui\n";
            else if ($izin->status == 0) $statusStr = "👤 *Status:* Pending\n";
            else if ($izin->status == 2) $statusStr = "👤 *Status:* Ditolak\n";
        }

        $pesan = "📢 *{$title}*\n\n"
            . $statusStr
            . "👤 *Karyawan:* {$userName}\n"
            . "📅 *Tanggal:* {$tanggal}\n"
            . "📝 *Tipe Izin:* " . ucfirst($izin->tipe) . "\n"
            . "💬 *Keterangan:* " . ($izin->keterangan ?? '-') . "\n"
            . "📎 *Dokumen:* {$linkDokumen}\n"
            . "🧍‍♂️ *Oleh:* " . Auth::user()->name;

        $this->sendMessage($pesan);
    }

    public function sendMessage($message)
    {
        $storeSetting = StoreSetting::where('cabang_id', getCabangId())->first();
        if ($storeSetting && $storeSetting->token_bot && $storeSetting->chat_id) {
            try {
                Http::post("https://api.telegram.org/bot{$storeSetting->token_bot}/sendMessage", [
                    'chat_id' => $storeSetting->chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {}
        }
    }
}
