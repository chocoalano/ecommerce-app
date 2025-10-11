<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cart & E-commerce Routes
|--------------------------------------------------------------------------
| Routes yang berkaitan dengan keranjang belanja, checkout, dan transaksi.
| Cart management dapat diakses oleh guest (menggunakan session),
| sedangkan checkout memerlukan login customer.
*/

// Cart routes - dapat diakses guest
Route::prefix('cart')->name('cart.')->group(function () {
    // View cart page - Halaman keranjang belanja
    Route::get('/', [CartController::class, 'index'])->name('index');

    // Cart items management - Pengelolaan item di keranjang
    Route::prefix('items')->name('items.')->group(function () {
        // Add single item to cart - Tambah item tunggal ke keranjang
        Route::post('/', [CartController::class, 'store'])->name('store');

        // Add multiple items to cart (batch) - Tambah banyak item sekaligus
        Route::post('/batch', [CartController::class, 'storeMany'])->name('storeMany');

        // Update item quantity - Update jumlah item
        Route::patch('/{item}', [CartController::class, 'update'])->name('update');

        // Remove item from cart - Hapus item dari keranjang
        Route::delete('/{item}', [CartController::class, 'destroy'])->name('destroy');
    });
});

// Protected routes that require customer authentication
Route::middleware(['customer'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes
    |--------------------------------------------------------------------------
    | Routes untuk proses checkout dan pembayaran
    | Memerlukan customer authentication
    */
    Route::prefix('checkout')->name('checkout.')->group(function () {
        // Checkout page - Halaman checkout
        Route::get('/', [CheckoutController::class, 'index'])->name('index');

        // Place order - Proses pemesanan
        Route::post('/place', [CheckoutController::class, 'place'])->name('place');

        // Thank you page - Halaman terima kasih setelah checkout
        Route::get('/thank-you/{order}', [CheckoutController::class, 'thankyou'])->name('thankyou');
    });

    /*
    |--------------------------------------------------------------------------
    | Transaction History Routes
    |--------------------------------------------------------------------------
    | Routes untuk melihat riwayat transaksi customer
    | Memerlukan customer authentication
    */
    Route::prefix('transactions')->name('transaction.')->group(function () {
        // Transaction history list - Daftar riwayat transaksi
        Route::get('/', [TransactionController::class, 'index'])->name('index');

        // Transaction detail - Detail transaksi
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
    });

});

/*
|--------------------------------------------------------------------------
| Public Cart Routes (Optional)
|--------------------------------------------------------------------------
| Routes yang bisa diakses tanpa login (untuk guest cart)
| Uncomment jika ingin mendukung guest cart
*/

// Route::prefix('guest-cart')->name('guest.cart.')->group(function () {
//     Route::get('/', [CartController::class, 'guestIndex'])->name('index');
//     Route::post('/items', [CartController::class, 'guestStore'])->name('store');
//     Route::patch('/items/{item}', [CartController::class, 'guestUpdate'])->name('update');
//     Route::delete('/items/{item}', [CartController::class, 'guestDestroy'])->name('destroy');
// });
