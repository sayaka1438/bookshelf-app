<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

Route::middleware('auth')->group(function () {
    Route::resource('books', BookController::class)
        ->except(['index', 'show']);
});

Route::resource('books', BookController::class)
    ->only(['index', 'show']);
