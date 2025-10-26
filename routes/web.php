<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Rute Umum dan Produk
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/', [HomeController::class, 'newsletter'])->name('newsletter');

// Page Routes (accessible to everyone)
Route::group(['prefix' => 'pages', 'as' => 'page.'], function () {
    Route::get('/', [PageController::class, 'index'])->name('index'); // Sitemap
    Route::get('/category/{category}', [PageController::class, 'category'])->name('category'); // Pages by category
});

// Individual page route (should be after other routes to avoid conflicts)
Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('page.show');

/*
|--------------------------------------------------------------------------
| Route untuk TAMU (Guest) & Autentikasi
|--------------------------------------------------------------------------
| Route di dalam grup ini menggunakan middleware 'guest:customer'.
| Catatan: Pastikan 'guest:customer' terdaftar di Kernel.php jika Anda menggunakan guard kustom.
*/
Route::withoutMiddleware(['customer'])->group(function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        // Halaman Pendaftaran (Register) - Dapat diakses oleh tamu
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register_submit'])->name('register.submit');

        // Halaman Login - Dapat diakses oleh tamu
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // Sesuaikan ke showLogin
        Route::post('/login', [AuthController::class, 'login_submit'])->name('login.submit');
    });
});


/*
|--------------------------------------------------------------------------
| Route yang DILINDUNGI (Middleware 'customer')
|--------------------------------------------------------------------------
| Route di dalam grup ini HANYA dapat diakses oleh customer yang sudah login.
| Middleware 'customer' merujuk pada alias untuk App\Http\Middleware\CustomerMiddleware.php
*/


// Auth routes
require __DIR__.'/auth.php';

// Include cart & e-commerce routes
require __DIR__.'/cart.php';

// Include product routes
require __DIR__.'/products.php';

// Include Midtrans webhook routes
require __DIR__.'/midtrans.php';
