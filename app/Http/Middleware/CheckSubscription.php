<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
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
        if (expiredDateCabang() == null) {
            // Periksa apakah berlangganan telah kadaluarsa
            if (Auth::user()->exp_date < now()) {
                // Berlangganan telah kadaluarsa, arahkan pengguna ke halaman pembayaran
                return redirect()->route('payment'); // Gantilah 'payment' dengan nama rute halaman pembayaran Anda
            }
        }else{
            if (expiredDateCabang() < now()) {
                // Berlangganan telah kadaluarsa, arahkan pengguna ke halaman pembayaran
                return redirect()->route('payment'); // Gantilah 'payment' dengan nama rute halaman pembayaran Anda
            }
        }

        // Lanjutkan dengan permintaan jika berlangganan masih berlaku
        return $next($request);
    }
}
