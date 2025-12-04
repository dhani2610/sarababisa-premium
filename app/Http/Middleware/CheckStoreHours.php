<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StoreSetting;
use Carbon\Carbon;

class CheckStoreHours
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Ambil Setting Cabang
        $cabangId = getCabangId();
        $setting = StoreSetting::where('cabang_id', $cabangId)->first();

        // 2. Cek Aturan
        if ($setting && $setting->is_close === 1) { // Pastikan integer 1
            $now = Carbon::now();

            // Parsing jam dari database
            $buka = Carbon::parse($setting->time_open_toko);
            $tutup = Carbon::parse($setting->time_close_toko);

            // Cek apakah SEKARANG di luar jam operasional?
            if ($now->lessThan($buka) || $now->greaterThan($tutup)) {

                // Blokir method perubahan data (POST/PUT/DELETE)
                // if (!$request->isMethod('get')) {
                    // === PERUBAHAN DISINI ===
                    // Arahkan ke View khusus dengan membawa data jam
                    return response()->view('errors.toko-tutup', [
                        'jam_buka' => $buka->format('H:i'),
                        'jam_tutup' => $tutup->format('H:i'),
                        'pesan_tambahan' => 'Silahkan hubungi Kepala Toko untuk akses darurat.'
                    ], 403); // Status 403 Forbidden
                // }
            }
        }

        return $next($request);
    }
}
