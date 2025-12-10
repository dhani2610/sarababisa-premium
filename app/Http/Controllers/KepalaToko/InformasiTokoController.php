<?php

namespace App\Http\Controllers\KepalaToko;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class InformasiTokoController extends Controller
{
    public function index()
    {
        if (getCabangId() == 1) {
            $users = User::where('cabang_id',getCabangId())->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }else{
            $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.', 'error');
            return redirect('/akun')->with('error', 'Silahkan bikin akun kepala toko terlebih dahulu untuk cabang ini.');
        }

        return view('pages/kepalatoko/pengaturan/profil', compact('users'));
    }

    public function update(Request $request)
    {
        $data = $request->all();

        // $item = Auth::user();
        $item = User::find($request->id_kepala_toko);
        // Validasi dan simpan logo jika ada
        $validated = $request->validate([
            'profile_photo_path' => 'nullable|file|mimes:jpeg,jpg,png|max:1024',
            'foto_portal'        => 'nullable|file|mimes:jpeg,jpg,png|max:1024',
            'foto_login'         => 'nullable|file|mimes:jpeg,jpg,png|max:1024',
        ], [
            'profile_photo_path.max' => 'Ukuran foto profil maksimal 1 MB.',
            'foto_portal.max'        => 'Ukuran foto portal maksimal 1 MB.',
            'foto_login.max'         => 'Ukuran foto login maksimal 1 MB.',
        ]);

        $data = $request->all();

        if ($request->hasFile('profile_photo_path')) {
            $data['profile_photo_path'] = $request->file('profile_photo_path')->store('assets/user', 'public');
        }

        if ($request->hasFile('foto_portal')) {
            $data['foto_portal'] = $request->file('foto_portal')->store('assets/user', 'public');
        }

        if ($request->hasFile('foto_login')) {
            $data['foto_login'] = $request->file('foto_login')->store('assets/user', 'public');
        }


        $filteredPhones = collect($request->phones ?? [])->filter(function ($item) {
            return !empty($item['title']) || !empty($item['nomor']);
        })->values()->all();
        $data['phones'] = json_encode($filteredPhones);

        $filteredBanks = collect($request->banks ?? [])->filter(function ($item) {
            return !empty($item['bank']) || !empty($item['rekening']) || !empty($item['pemilik']);
        })->values()->all();
        $data['banks'] = json_encode($filteredBanks);


        // Simpan data
        $item->update($data);

        return redirect()->route('informasi-toko')->with('success', 'Informasi berhasil diperbarui.');
    }
}
