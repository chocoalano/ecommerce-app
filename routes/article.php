<?php

use App\Http\Controllers\ArticleController;

Route::prefix('article')->name('article.')->group(function () {
    // View article list - Halaman daftar artikel
    Route::get('/', [ArticleController::class, 'index'])->name('index');
    Route::get('/{slug}', [ArticleController::class, 'show'])->name('show');
});
