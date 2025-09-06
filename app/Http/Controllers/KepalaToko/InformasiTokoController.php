<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InformasiTokoController extends Controller
{
    public function index()
    {
        $users = Auth::user();
        // dd($users);
        return view('pages/kepalatoko/pengaturan/profil', compact('users'));
    }

    public function update(Request $request)
    {
        $data = $request->all();

        $item = Auth::user();

        // Validasi dan simpan logo jika ada
        if ($request->hasFile('profile_photo_path')) {
            $validator = Validator::make($request->all(), [
                'profile_photo_path' => 'file|mimes:jpeg,jpg,png',
            ]);

            if ($validator->fails()) {
                toast('Gambar Logo harus menggunakan format PNG atau JPG.', 'error');
                return redirect()->back();
            }

            $data['profile_photo_path'] = $request->file('profile_photo_path')->store('assets/user', 'public');
        }
        if ($request->hasFile('foto_portal')) {
            $validator = Validator::make($request->all(), [
                'foto_portal' => 'file|mimes:jpeg,jpg,png',
            ]);

            if ($validator->fails()) {
                toast('Gambar Logo harus menggunakan format PNG atau JPG.', 'error');
                return redirect()->back();
            }

            $data['foto_portal'] = $request->file('foto_portal')->store('assets/user', 'public');
        }

        // ✅ Filter phones yang tidak kosong
        $filteredPhones = collect($request->phones ?? [])->filter(function ($item) {
            return !empty($item['title']) || !empty($item['nomor']);
        })->values()->all();
        $data['phones'] = json_encode($filteredPhones);

        // ✅ Filter banks yang tidak kosong
        $filteredBanks = collect($request->banks ?? [])->filter(function ($item) {
            return !empty($item['bank']) || !empty($item['rekening']) || !empty($item['pemilik']);
        })->values()->all();
        $data['banks'] = json_encode($filteredBanks);


        // Simpan data
        $item->update($data);

        return redirect()->route('informasi-toko')->with('success', 'Informasi berhasil diperbarui.');
    }
}
