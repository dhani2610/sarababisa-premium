<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Type;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\KepalaToko\UserRequest;
use App\Models\Shift;
use Yajra\DataTables\Facades\DataTables;
class AkunController extends Controller
{
    public function index()
    {
        $cabangId = getCabangId(); // Ambil cabang aktif

        $types = Type::where('cabang_id', $cabangId)->get();

        if ($cabangId != 1) {
            $users = User::whereNull('deleted_at')
                ->where('cabang_id', $cabangId)
                ->where('id', '!=',1)
                ->with('type')
                ->paginate(10);
        }else{
            $users = User::whereNull('deleted_at')
                ->where('cabang_id', $cabangId)
                ->with('type')
                ->paginate(10);

        }

        $users_count = User::whereNull('deleted_at')
            ->where('cabang_id', $cabangId)
            ->count();

        $workers = Worker::where('cabang_id', $cabangId)->get();

        $shift = Shift::where('cabang_id', $cabangId)->get();

       if ($cabangId != 1) {
            $count = User::whereNull('deleted_at')
                ->where('cabang_id', $cabangId)
                ->where('id', '!=',1)
                ->with('type')
                ->count();
        }else{
            $count = User::whereNull('deleted_at')
                ->where('cabang_id', $cabangId)
                ->with('type')
                ->count();

        }

        return view('pages/kepalatoko/akun', compact(
            'users',
            'users_count',
            'count',
            'types',
            'workers',
            'shift'
        ));
    }


    public function getData(Request $request)
    {
        $cabangId = getCabangId();

        $query = User::whereNull('deleted_at')
            ->where('cabang_id', $cabangId)
            ->with(['shift', 'type'])
            ->latest();

        if ($cabangId != 1) {
            $query->where('id', '!=', 1);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                if ($row->id == Auth::id() || $row->id == 1) return '';
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->addColumn('cabang_name', function ($row) {
                return getCabangName($row->cabang_id);
            })
            ->editColumn('name', function ($row) {
                return '<div class="font-medium">' . e($row->name) . '</div>';
            })
            ->editColumn('email', function ($row) {
                return $row->email;
            })
            ->editColumn('username', function ($row) {
                return '<div class="font-medium">' . e($row->username) . '</div>';
            })
            ->addColumn('bagian_teknisi', function ($row) {
                return '<div class="font-medium">' . ($row->bagian_teknisi ?? '-') . '</div>';
            })
            ->addColumn('nik', function ($row) {
                return '<div class="font-medium">' . e($row->nik) . '</div>';
            })
            ->addColumn('alamat', function ($row) {
                return '<div class="font-medium">' . e($row->alamat) . '</div>';
            })
            ->addColumn('nomor_hp', function ($row) {
                return '<div class="font-medium">' . e($row->nomor_hp) . '</div>';
            })
            ->addColumn('hak_akses', function ($row) {
                $text = e($row->role);
                if ($row->types_id != null && $row->type) {
                    $text .= ' ' . e($row->type->name);
                }
                return '<div class="font-medium text-slate-800">' . $text . '</div>';
            })
            ->addColumn('persen', function ($row) {
                return '<div class="font-medium text-slate-800">' . e($row->persen) . '</div>';
            })
            ->addColumn('pdf_investor', function ($row) {
                if ($row->role == 'Investor' && $row->pdf_investor) {
                    $url = asset('storage/' . $row->pdf_investor);
                    return '<a href="' . $url . '" target="_blank" class="text-indigo-500 underline text-sm">Lihat PDF</a>';
                }
                return '-';
            })
            ->addColumn('persen_investor', function ($row) {
                return '<div class="font-medium">' . ($row->persen_investor ?? '0') . '%</div>';
            })
            ->addColumn('shift_name', function ($row) {
                if ($row->role == 'Investor') {
                    return '<div class="font-medium">-</div>';
                }else{
                    return '<div class="font-medium">' . ($row->shift ? e($row->shift->nama_shift) : '-') . '</div>';

                }
            })

            // --- TAMBAHAN KOLOM DATA BARU ---
            ->addColumn('foto_ktp', function ($row) {
                if ($row->foto_ktp) {
                    $url = asset('storage/' . $row->foto_ktp);
                    return '<a href="' . $url . '" target="_blank" class="text-sky-500 hover:text-sky-600 font-medium">Lihat</a>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            ->addColumn('foto_kk', function ($row) {
                if ($row->foto_kk) {
                    $url = asset('storage/' . $row->foto_kk);
                    return '<a href="' . $url . '" target="_blank" class="text-sky-500 hover:text-sky-600 font-medium">Lihat</a>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            ->addColumn('foto_ijasah', function ($row) {
                if ($row->foto_ijasah) {
                    $url = asset('storage/' . $row->foto_ijasah);
                    return '<a href="' . $url . '" target="_blank" class="text-sky-500 hover:text-sky-600 font-medium">Lihat</a>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            ->addColumn('dokumen_lain', function ($row) {
                if ($row->dokumen_lain) {
                    $url = asset('storage/' . $row->dokumen_lain);
                    return '<a href="' . $url . '" target="_blank" class="text-sky-500 hover:text-sky-600 font-medium">Lihat</a>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            // --------------------------------

            ->addColumn('aksi', function ($row) {
                if ($row->id == 1 && Auth::id() != 1) return '';

                $editUrl = route('akun-edit', $row->id);
                $deleteUrl = route('akun-destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $deleteBtn = '';
                if ($row->id != Auth::id() && $row->role != 'Kepala Toko') {
                    $deleteBtn = '
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'Yakin ingin menghapus akun ini?\');">
                            ' . $csrf . $method . '
                            <button type="submit" class="text-rose-500 hover:text-rose-600 rounded-full">
                                <span class="sr-only">Delete</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                </svg>
                            </button>
                        </form>
                    ';
                }

                return '
                    <div class="flex space-x-1">
                        <a href="' . $editUrl . '">
                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                <span class="sr-only">Edit</span>
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                </svg>
                            </button>
                        </a>
                        ' . $deleteBtn . '
                    </div>
                ';
            })
            // Tambahkan nama kolom baru ke rawColumns agar HTML link terbaca
            ->rawColumns(['checkbox', 'name', 'username', 'bagian_teknisi', 'nik', 'alamat', 'nomor_hp', 'hak_akses', 'persen', 'pdf_investor','persen_investor', 'shift_name', 'foto_ktp', 'foto_kk', 'foto_ijasah', 'dokumen_lain', 'aksi'])
            ->make(true);
    }

    public function deleteBatch(Request $request)
    {
        $selectedIds = $request->input('ids');

        if (empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 400);
        }

        // Cek Relasi
        $hasRelation = User::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService')
                    ->orWhereHas('relasiSale')
                    ->orWhereHas('expense')
                    ->orWhereHas('salary');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Gagal: Salah satu akun memiliki riwayat transaksi/gaji.'], 422);
        }

        // Soft Delete Manual
        $users = User::whereIn('id', $selectedIds)->get();
        foreach ($users as $user) {
            // Jangan hapus diri sendiri atau Admin Pusat
            if ($user->id == Auth::id() || $user->id == 1) continue;

            $user->deleted_at = now();
            $user->save();
        }

        return response()->json(['message' => 'Data Akun berhasil dihapus.']);
    }


    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');

        $hasRelation = User::whereIn('id', $selectedIds)
            ->where(function ($query) {
                $query->whereHas('relasiService')
                    ->orWhereHas('relasiSale')->orWhereHas('expense')->orWhereHas('salary');
            })
            ->exists();

        if ($hasRelation) {
            return response()->json(['message' => 'Data Akun yang memiliki riwayat transaksi tidak bisa dihapus.']);
        }

        $usr = User::whereIn('id', $selectedIds)->get();

        foreach ($usr as $key => $value) {
            $item = User::findOrFail($value->id);
            if (!empty($item)) {
                $item->deleted_at = date('Y-m-d H:i:s');
                $item->save();
            }
        }

        // User::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data Akun berhasil dihapus.']);
    }

    // public function store(UserRequest $request)
    // {
    //     $langganan = Auth::user()->exp_date;

    //     User::create([
    //         'name' => $request->name,
    //         'password' => bcrypt($request->password),
    //         'username' => $request->username,
    //         'bagian_teknisi' => $request->bagian_teknisi,
    //         'role' => $request->role,
    //         'types_id' => $request->types_id,
    //         'workers_id' => $request->workers_id,
    //         'nik' => $request->nik,
    //         'nomor_hp' => $request->nomor_hp,
    //         'alamat' => $request->alamat,
    //         'persen' => $request->persen,
    //         'exp_date' => $langganan,
    //     ]);

    //     return redirect()->route('akun');
    // }

    public function edit($id)
    {
        $cabangId = getCabangId(); // cabang aktif

        // User yang di-edit juga harus terikat cabang
        $item = User::with('worker')
            ->where('cabang_id', $cabangId)
            ->findOrFail($id);

        $types = Type::where('cabang_id', $cabangId)->get();

        $users = User::where('cabang_id', $cabangId)
            ->paginate(10);

        $users_count = User::where('cabang_id', $cabangId)
            ->count();

        $workers = Worker::where('cabang_id', $cabangId)->get();

        $shift = Shift::where('cabang_id', $cabangId)->get();

        return view('pages.kepalatoko.akun-edit', [
            'types' => $types,
            'item' => $item,
            'users' => $users,
            'workers' => $workers,
            'users_count' => $users_count,
            'shift' => $shift
        ]);
    }

    public function setting()
    {
        $types = Type::all();
        $latestExp = User::max('exp_date');
        return view('pages.kepalatoko.setting-edit', [
            'types' => $types,
            'latestExp' => $latestExp,
        ]);
    }
    public function updateExpDate(Request $request)
    {
        $request->validate([
            'exp_date' => 'required|date',
        ]);

        User::query()->update([
            'exp_date' => $request->exp_date,
        ]);

        return redirect()->back()->with('success', 'Tanggal expired berhasil diperbarui untuk semua user!');
    }
    public function getDataTotalCabang(Request $request)
    {
        try {
            $data = User::select('total_cabang')->first();

            return response()->json(['msg'=>'berhasil','data'=>$data->total_cabang ?? 0]);
        } catch (\Throwable $th) {
            return response()->json(['msg'=>'gagal','error'=> $th->getMessage()]);
        }
    }
    public function updateTotalCabang(Request $request)
    {
        try {
            $request->validate([
                'total_cabang' => 'required|date',
            ]);

            User::query()->update([
                'total_cabang' => $request->total_cabang,
            ]);

            return response()->json(['msg'=>'berhasil','data'=>$data->total_cabang ?? 0]);
        } catch (\Throwable $th) {
            return response()->json(['msg'=>'gagal','error'=> $th->getMessage()]);
        }
    }

    public function updateExpDateJson(Request $request)
    {
        try {
            $request->validate([
                'exp_date' => 'required|date',
            ]);

            User::query()->update([
                'exp_date' => $request->exp_date,
            ]);

            return response()->json(['msg'=>'berhasil']);
        } catch (\Throwable $th) {
            return response()->json(['msg'=>'gagal','error'=> $th->getMessage()]);
        }

    }

    // public function update(Request $request, $id)
    // {
    //     $item = User::findOrFail($id);

    //     $langganan = Auth::user()->exp_date;

    //     $password = $item->password; // Menggunakan password yang terdahulu jika tidak ada perubahan

    //     if ($request->has('password') && !empty($request->password)) {
    //         $password = bcrypt($request->password);
    //     }

    //     $item->update([
    //         'name' => $request->name,
    //         'password' => $password,
    //         'bagian_teknisi' => $request->bagian_teknisi,
    //         'username' => $request->username,
    //         'role' => $request->role,
    //         'types_id' => $request->types_id,
    //         'workers_id' => $request->workers_id,
    //         'nik' => $request->nik,
    //         'nomor_hp' => $request->nomor_hp,
    //         'alamat' => $request->alamat,
    //         'persen' => $request->persen,
    //         'exp_date' => $langganan,
    //     ]);

    //     return redirect()->route('akun');
    // }

    public function store(UserRequest $request)
    {
        // dd($request->all());
        $langganan = Auth::user()->exp_date;
        $total_cabang = Auth::user()->total_cabang;


        if ($request->email != null) {
            $checkEmail = User::where('email', $request->email)->first();

            if ($checkEmail) {

                toast('Email sudah terdaftar, silahkan gunakan email lain.', 'error');
                return redirect()->route('akun');
            }
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'username' => $request->username,
            'bagian_teknisi' => $request->bagian_teknisi,
            'role' => $request->role,
            'types_id' => $request->types_id,
            'workers_id' => $request->workers_id,
            'nik' => $request->nik,
            'nomor_hp' => $request->nomor_hp,
            'alamat' => $request->alamat,
            'persen' => $request->persen,
            'tipe_bonus_admin' => $request->tipe_bonus_admin,
            'nominal_bonus_admin' => $request->nominal_bonus_admin,
            'persen_investor' => $request->persen_investor ?? 0,
            'shift_id' => $request->shift_id,
            'exp_date' => $langganan,
            'total_cabang' => $total_cabang,
            'cabang_id' => getCabangId(),
        ];

        $dokumenFiles = ['foto_ktp', 'foto_kk', 'foto_ijasah', 'dokumen_lain'];

        foreach ($dokumenFiles as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('dokumen_user', 'public');
                $data[$fileKey] = $path;
            }
        }

        if ($request->role === 'Investor' && $request->hasFile('pdf_investor')) {
            $pdfPath = $request->file('pdf_investor')->store('pdf_investors', 'public');
            $data['pdf_investor'] = $pdfPath;
        }

        User::create($data);

        return redirect()->route('akun');
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $item = User::findOrFail($id);
        $langganan = Auth::user()->exp_date;
        $total_cabang = Auth::user()->total_cabang;

        if ($request->email != null) {
            $checkEmail = User::where('email', $request->email)
                              ->where('id', '!=', $id) // Abaikan ID user ini sendiri
                              ->first();
            if ($checkEmail) {
                toast('Email sudah digunakan oleh pengguna lain.', 'error');
                return redirect()->route('akun');
            }
        }


        $password = $item->password;
        if ($request->filled('password')) {
            $password = bcrypt($request->password);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'bagian_teknisi' => $request->bagian_teknisi,
            'username' => $request->username,
            'role' => $request->role,
            'types_id' => $request->types_id,
            'workers_id' => $request->workers_id,
            'nik' => $request->nik,
            'nomor_hp' => $request->nomor_hp,
            'alamat' => $request->alamat,
            'persen' => $request->persen,
            'tipe_bonus_admin' => $request->tipe_bonus_admin,
            'persen_investor' => $request->persen_investor ?? 0,
            'nominal_bonus_admin' => $request->nominal_bonus_admin,
            'shift_id' => $request->shift_id,
            'exp_date' => $langganan,
            'total_cabang' => $total_cabang,
        ];

        $dokumenFiles = ['foto_ktp', 'foto_kk', 'foto_ijasah', 'dokumen_lain'];

        foreach ($dokumenFiles as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('dokumen_user', 'public');
                $data[$fileKey] = $path;
            }
        }

        if ($request->role === 'Investor' && $request->hasFile('pdf_investor')) {
            $pdfPath = $request->file('pdf_investor')->store('pdf_investors', 'public');
            $data['pdf_investor'] = $pdfPath;
        }

        $item->update($data);

        return redirect()->route('akun');
    }


    public function destroy($id)
    {
        $item = User::findOrFail($id);

        $item->deleted_at = date('Y-m-d H:i:s');
        $item->save();

        toast('Data Akun berhasil dihapus.', 'success');

        return redirect()->route('akun');
    }
}
