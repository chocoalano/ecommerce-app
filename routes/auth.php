<?php

use App\Http\Controllers\Auth\EwalletController;
use App\Http\Controllers\Auth\KomisiController;
use App\Http\Controllers\Auth\NetworkController;
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

        // Topup routes
        Route::get('/ewallet/topup', [EwalletController::class, 'showTopupForm'])->name('ewallet.topup');
        Route::post('/ewallet/topup', [EwalletController::class, 'submitTopup'])->name('ewallet.topup.submit');
        Route::get('/ewallet/topup/{id}/finish', [EwalletController::class, 'topupFinish'])->name('ewallet.topup.finish');

        // Manual approve topup for testing (should be admin only in production)
        Route::post('/ewallet/topup/{id}/manual-approve', [EwalletController::class, 'manualApproveTopup'])->name('ewallet.topup.manual-approve');

        // Withdrawal routes
        Route::get('/ewallet/withdrawal', [EwalletController::class, 'showWithdrawalForm'])->name('ewallet.withdrawal');
        Route::post('/ewallet/withdrawal', [EwalletController::class, 'submitWithdrawal'])->name('ewallet.withdrawal.submit');

        // Admin routes (untuk approve/process)
        Route::post('/ewallet/topup/{id}/approve', [EwalletController::class, 'approveTopup'])->name('ewallet.topup.approve');
        Route::post('/ewallet/withdrawal/{id}/process', [EwalletController::class, 'processWithdrawal'])->name('ewallet.withdrawal.process');

        Route::get('/transaction-order', [TransactionOrderController::class, 'index'])->name('transaction-order');

        Route::get('network', [NetworkController::class,'index'])->name('network.binary');
        Route::get('network/sponsorship', [NetworkController::class,'sponsorship'])->name('network.sponsorship');
        // Order History Routes
        Route::get('/orders', [AuthController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AuthController::class, 'orderDetail'])->name('order.detail');
        Route::delete('/orders/{order}/cancel', [AuthController::class, 'cancelOrder'])->name('order.cancel');

        // Logout - Menggunakan POST, sangat disarankan untuk keamanan
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

});
