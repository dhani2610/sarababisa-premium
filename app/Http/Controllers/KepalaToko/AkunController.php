<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Type;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\KepalaToko\UserRequest;

class AkunController extends Controller
{
    public function index()
    {
        $types = Type::all();
        $users = User::with('type')->paginate(10);
        $users_count = User::all()->count();
        $workers = Worker::all();
        return view('pages/kepalatoko/akun', compact('users', 'users_count', 'types', 'workers'));
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

        User::whereIn('id', $selectedIds)->delete();
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
        $item = User::with('worker')->findOrFail($id);
        $types = Type::all();
        $users = User::paginate(10);
        $users_count = User::all()->count();
        $workers = Worker::all();

        return view('pages.kepalatoko.akun-edit', [
            'types' => $types,
            'item' => $item,
            'users' => $users,
            'workers' => $workers,
            'users_count' => $users_count
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
        $langganan = Auth::user()->exp_date;

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
            'exp_date' => $langganan,
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
            'exp_date' => $langganan,
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

        $item->delete();

        toast('Data Akun berhasil dihapus.', 'success');

        return redirect()->route('akun');
    }
}
