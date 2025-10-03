<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Rute Umum dan Produk
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category', [HomeController::class, 'show'])->name('category');

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('/{sku}', [ProductController::class, 'show'])->name('show');
});

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
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register'); // Sesuaikan ke showRegister
        
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
Route::middleware(['customer'])->group(function () {

    // 1. Rute Profil, Keranjang, Pesanan & Logout
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        // Halaman Profil (Dashboard Customer)
        Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
        Route::get('/cart', [AuthController::class, 'cart'])->name('cart');
        Route::get('/order', [AuthController::class, 'orders'])->name('order');
        Route::get('/setting', [AuthController::class, 'setting'])->name('setting');

        // Logout - Menggunakan POST, sangat disarankan untuk keamanan
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); 
    });

    // 2. Rute Produk Sensitif (Interaksi)
    Route::resource('cart', CartController::class)->only(['index', 'store', 'update', 'destroy']);
    
    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        // Menambah ke Keranjang - Membutuhkan autentikasi
        Route::get('/{id}/store', [ProductController::class, 'store'])->name('add_cart'); // Lebih baik menggunakan POST
        
        // Menambah ke Wishlist - Membutuhkan autentikasi
        Route::get('/{id}/wislist', [ProductController::class, 'wislist'])->name('wislist');

        // Update Keranjang (PUT)
        Route::put('/{id}', [ProductController::class, 'update'])->name('update_cart');

        // Hapus dari Keranjang (DELETE)
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('delete_cart');
    });

    // ini route buat cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // aksi add (single item) — cocok untuk tombol "Add to Cart"
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    // aksi add (batch, opsional)
    Route::post('/cart/items/batch', [CartController::class, 'storeMany'])->name('cart.items.storeMany');
    // (opsional) ubah qty / hapus item
    Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/checkout/thank-you/{order}', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
});
