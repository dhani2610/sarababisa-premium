<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StoreSetting;
use Carbon\Carbon;

class CheckStoreHours
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Pastikan user sudah login agar tidak error saat cek role
        if (auth()->check() && auth()->user()->role != 'Kepala Toko') {

            // 1. Ambil Setting Cabang
            // Pastikan helper getCabangId() tersedia di aplikasi kamu
            $cabangId = getCabangId();
            $setting = StoreSetting::where('cabang_id', $cabangId)->first();

            // 2. Cek Aturan Pembatasan
            if ($setting && $setting->is_close === 1) {

                $now = Carbon::now();

                // Parsing jam dari database
                // Gunakan format hari ini sebagai patokan awal
                $buka = Carbon::parse($setting->time_open_toko);
                $tutup = Carbon::parse($setting->time_close_toko);

                // ================= PERBAIKAN LOGIKA DISINI =================
                // Jika jam tutup lebih kecil dari jam buka (misal: Buka 10:00, Tutup 00:00),
                // maka jam tutup dianggap sebagai "Besok".
                if ($tutup->lessThanOrEqualTo($buka)) {
                    $tutup->addDay();
                }
                // ===========================================================

                // Cek apakah Waktu Sekarang (NOW) TIDAK berada di antara Buka & Tutup
                // Fungsi ->between() sudah mencakup >= buka dan <= tutup
                if (!$now->between($buka, $tutup)) {

                    // Jika kamu ingin memblokir HANYA method tertentu (POST/PUT), uncomment baris di bawah:
                    // if (!$request->isMethod('get')) {

                        return response()->view('errors.toko-tutup', [
                            'jam_buka' => $buka->format('H:i'),
                            'jam_tutup' => $tutup->format('H:i'), // Tampilan tetap jam 00:00 walaupun datenya besok
                            'pesan_tambahan' => 'Silahkan hubungi Kepala Toko untuk akses darurat.'
                        ], 403);

                    // }
                }
            }
        }

        return $next($request);
    }
}
