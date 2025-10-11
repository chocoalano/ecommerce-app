<?php

use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Midtrans Webhook Routes
|--------------------------------------------------------------------------
| Routes untuk menangani webhook dari Midtrans payment gateway
| Tidak memerlukan middleware autentikasi karena diakses oleh Midtrans
*/

// Webhook notification dari Midtrans (POST callback)
Route::post('/webhook/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])
    ->name('midtrans.webhook.notification');

// Route untuk manual check status pembayaran (optional, untuk admin/customer)
Route::middleware(['customer'])->group(function () {
    Route::post('/payment/check-status/{order}', [MidtransWebhookController::class, 'checkStatus'])
        ->name('payment.check.status');

    // Route untuk mengecek status order dari checkout
    Route::get('/order/{order}/payment-status', [CheckoutController::class, 'paymentStatus'])
        ->name('order.payment.status');
});
