<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category', [HomeController::class, 'show'])->name('category');
Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('show');
});

/*
|--------------------------------------------------------------------------
| Route untuk TAMU (Guest) & Autentikasi
|--------------------------------------------------------------------------
| Route di dalam grup ini hanya dapat diakses oleh pengguna yang BELUM login.
| Ini termasuk halaman Login dan Register.
*/
Route::middleware(['guest:customer'])->group(function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        // Halaman Pendaftaran (Register) - Dapat diakses oleh tamu
        Route::get('/register', [HomeController::class, 'register'])->name('register');

        // Halaman Login - Dapat diakses oleh tamu
        Route::get('/login', [HomeController::class, 'login'])->name('login');
        Route::post('/login', [HomeController::class, 'login_submit'])->name('login.submit');
    });
});


/*
|--------------------------------------------------------------------------
| Route yang DILINDUNGI (Middleware 'auth:customer')
|--------------------------------------------------------------------------
| Route di dalam grup ini hanya dapat diakses oleh customer yang sudah login.
| Jika customer belum login, mereka akan diarahkan ke route 'login' customer.
*/
Route::middleware(['auth:customer'])->group(function () {

    // Route Auth yang Dilindungi (misalnya, Profil)
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        // Halaman Profil - HANYA dapat diakses oleh customer yang sudah login.
        Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    });

    // Route Produk Sensitif (Keranjang & Transaksi)
    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        // Menambah ke Keranjang - Membutuhkan autentikasi
        Route::get('/{id}/store', [ProductController::class, 'store'])->name('add_cart');

        // Menambah ke Wishlist - Membutuhkan autentikasi
        Route::get('/{id}/wislist', [ProductController::class, 'wislist'])->name('wislist');

        // Checkout - Membutuhkan autentikasi
        Route::get('/checkout', [ProductController::class, 'checkout'])->name('checkout');

        // Riwayat Transaksi - Membutuhkan autentikasi
        Route::get('/transaction', [ProductController::class, 'transaction'])->name('transaction');

        // Update Keranjang (PUT) - Membutuhkan autentikasi
        Route::put('/{id}', [ProductController::class, 'update'])->name('update_cart');

        // Hapus dari Keranjang (DELETE) - Membutuhkan autentikasi
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('delete_cart');
    });
});

