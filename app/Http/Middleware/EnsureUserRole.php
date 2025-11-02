<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() == null) {
            return redirect('/login');
        }
        if (Auth::user() && Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Investor' || Auth::user()->role == 'Admin Toko' || Auth::user()->role == 'Teknisi' || Auth::user()->role == 'Sales') {
            return $next($request);
        }

        return redirect('/hak-akses');
    }
}
