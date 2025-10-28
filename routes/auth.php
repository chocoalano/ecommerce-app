<?php

use App\Http\Controllers\Auth\EwalletController;
use App\Http\Controllers\Auth\KomisiController;
use App\Http\Controllers\Auth\TransactionOrderController;
use App\Http\Controllers\Auth\AuthController;

Route::middleware(['customer'])->group(function () {

    // 1. Rute Profil, Keranjang, Pesanan & Logout
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        // Halaman Profil (Dashboard Customer)
        Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/network-list', [AuthController::class, 'network_list'])->name('network-list');
        Route::get('/komisi-list', [KomisiController::class, 'index'])->name('komisi-list');
        Route::get('/ewallet', [EwalletController::class, 'index'])->name('ewallet');
        Route::get('/transaction-order', [TransactionOrderController::class, 'index'])->name('transaction-order');

        Route::get('network', [AuthController::class,'network_info'])->name('network.info');
        // Order History Routes
        Route::get('/orders', [AuthController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AuthController::class, 'orderDetail'])->name('order.detail');
        Route::post('/orders/{order}/cancel', [AuthController::class, 'cancelOrder'])->name('order.cancel');

        // Logout - Menggunakan POST, sangat disarankan untuk keamanan
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

});
