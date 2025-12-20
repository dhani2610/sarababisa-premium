<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DirectPasswordResetController extends Controller
{
    // 1. Tampilkan Halaman Input Email
    public function showRequestForm()
    {
        return view('auth.direct-reset');
    }

    // 2. Cek Email (POST)
    public function checkEmail(Request $request)
    {
        // Validasi input email dengan pesan Indonesia
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Mohon masukkan alamat email Anda.',
            'email.email' => 'Format email yang Anda masukkan tidak valid.',
        ]);

        // Cek apakah user ada di database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tersebut tidak ditemukan dalam sistem kami.']);
        }

        return redirect()->route('direct.reset.form', ['email' => $user->email]);
    }

    // 3. Tampilkan Form Password (GET)
    public function showChangePasswordForm(Request $request)
    {
        $email = $request->query('email');

        if (!$email || !User::where('email', $email)->exists()) {
            return redirect()->route('direct.reset.request');
        }

        return view('auth.direct-reset', [
            'verified_email' => $email
        ]);
    }

    // 4. Update Password (POST)
    public function updatePassword(Request $request)
    {
        // Validasi password baru dengan pesan Indonesia
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.exists' => 'Email tidak valid atau tidak terdaftar.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password baru.',
        ]);

        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Password berhasil diperbarui. Silakan login dengan password baru.');
    }
}
