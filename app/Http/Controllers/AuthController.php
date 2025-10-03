<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin(Request $request)
    {
        // Jika pengguna sudah login sebagai 'customer', arahkan langsung ke dashboard
        if (Auth::guard('customer')->check()) {
            return redirect()->route('auth.profile');
        }

        return view('pages.auth.login');
    }

    /**
     * Memproses permintaan login (POST).
     */
    public function login_submit(Request $request)
    {
        // 1. Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Coba autentikasi pengguna menggunakan guard 'customer'
        if (Auth::guard('customer')->attempt($credentials)) {
            
            // Regenerate sesi untuk mencegah session fixation
            $request->session()->regenerate();  
            
            // Redirect ke dashboard pengguna (user.dashboard = /akun)
            return redirect()->intended(route('auth.profile'))->with('success', 'Selamat datang kembali!');
        } 
        
        // 3. Jika gagal, lemparkan error kembali ke halaman login
        throw ValidationException::withMessages([
            'email' => __('Email atau Password yang Anda masukkan salah.'),
        ])->redirectTo(route('auth.login'));
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showRegister(Request $request)
    {
        return view('pages.auth.register');
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showProfile(Request $request)
    {
        return view('pages.auth.profile');
    }
    
    // public function register_submit(Request $request) { ... } // Tambahkan logika registrasi di sini

    /**
     * Memproses permintaan logout.
     */
    public function logout(Request $request)    
    {
        // Proses logout dari guard 'customer'
        Auth::guard('customer')->logout();

        // Invalidasi sesi saat ini
        $request->session()->invalidate();

        // Regenerate token CSRF untuk keamanan tambahan
        $request->session()->regenerateToken();

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('auth.login')->with('success', 'Anda telah berhasil logout.');
    }
}
