<?php

use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Wishlist Routes
|--------------------------------------------------------------------------
*/

// Public endpoints (with optional auth)
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');
Route::post('/wishlist/status', [WishlistController::class, 'status'])->name('wishlist.status');

// Wishlist page & remove
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/remove/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
