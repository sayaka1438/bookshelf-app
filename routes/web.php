<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

Route::middleware('auth')->group(function () {
    Route::resource('books', BookController::class)
        ->except(['index', 'show']);

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::resource('reviews', ReviewController::class)
        ->only(['edit', 'update', 'destroy']);

    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');

    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');

    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'toggle'])
        ->name('reviews.like');
});

Route::resource('books', BookController::class)
    ->only(['index', 'show']);

Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');
