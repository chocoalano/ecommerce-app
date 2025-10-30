<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
|
| Routes untuk product functionality dengan repository pattern
|
*/

// Product listing dan filtering
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Product search
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

// Product detail by slug
Route::get('/products/{sku}', [ProductController::class, 'show'])->name('products.show');

// Add to cart
Route::post('/cart/add', [ProductController::class, 'store'])->name('cart.add');

// Category products
Route::get('/category/{categorySlug}', [ProductController::class, 'category'])->name('products.category');
Route::put('/product/reviews/{productId}', [ProductController::class, 'reviews'])->name('products.reviews.store');

/*
|--------------------------------------------------------------------------
| API Routes untuk AJAX calls
|--------------------------------------------------------------------------
*/

// Product suggestions untuk search autocomplete
Route::get('/api/products/suggestions', [ProductController::class, 'suggestions'])->name('api.products.suggestions');

// Check product availability
Route::get('/api/products/{id}/availability', [ProductController::class, 'checkAvailability'])->name('api.products.availability');

// Get product pricing
Route::get('/api/products/{slug}/pricing', [ProductController::class, 'pricing'])->name('api.products.pricing');

// Featured products
Route::get('/api/products/featured', [ProductController::class, 'featured'])->name('api.products.featured');

// Latest products
Route::get('/api/products/latest', [ProductController::class, 'latest'])->name('api.products.latest');
