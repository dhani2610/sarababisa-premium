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
        $validated = $request->validate([
            'profile_photo_path' => 'nullable|file|mimes:jpeg,jpg,png|max:1024',
            'foto_portal'        => 'nullable|file|mimes:jpeg,jpg,png|max:1024',
        ], [
            'profile_photo_path.uploaded' => 'Upload foto profil gagal. Pastikan ukuran tidak lebih dari 1 MB.',
            'profile_photo_path.mimes'    => 'Foto profil harus menggunakan format JPG atau PNG.',
            'profile_photo_path.max'      => 'Ukuran foto profil maksimal 1 MB.',

            'foto_portal.uploaded' => 'Upload foto portal gagal. Pastikan ukuran tidak lebih dari 1 MB.',
            'foto_portal.mimes'    => 'Foto portal harus menggunakan format JPG atau PNG.',
            'foto_portal.max'      => 'Ukuran foto portal maksimal 1 MB.',
        ]);


        $data = $request->all();

        // ✅ Upload foto profil
        if ($request->hasFile('profile_photo_path')) {
            $data['profile_photo_path'] = $request->file('profile_photo_path')->store('assets/user', 'public');
        }

        // ✅ Upload foto portal
        if ($request->hasFile('foto_portal')) {
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
