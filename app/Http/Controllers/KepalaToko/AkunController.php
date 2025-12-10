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

        return view('pages/kepalatoko/akun', compact(
            'users',
            'users_count',
            'types',
            'workers',
            'shift'
        ));
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

        $data = [
            'name' => $request->name,
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
            'shift_id' => $request->shift_id,
            'exp_date' => $langganan,
            'total_cabang' => $total_cabang,
            'cabang_id' => getCabangId(),
        ];

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

        $password = $item->password;
        if ($request->filled('password')) {
            $password = bcrypt($request->password);
        }

        $data = [
            'name' => $request->name,
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
            'nominal_bonus_admin' => $request->nominal_bonus_admin,
            'shift_id' => $request->shift_id,
            'exp_date' => $langganan,
            'total_cabang' => $total_cabang,
        ];

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
