<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pengecekan utama: Pastikan pengguna sudah login menggunakan guard 'customer'
        if (!Auth::guard('customer')->check()) {
            // Jika belum login, arahkan ke halaman login
            // Nama rute 'auth.login' merujuk pada kode yang ada di Canvas
            return redirect()->route('auth.login')->with('error', 'Anda perlu login untuk mengakses halaman ini.');
        }

        // Jika sudah login sebagai customer, lanjutkan ke request yang diminta
        return $next($request);
    }
}
