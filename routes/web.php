<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

// 仮ルート
Route::middleware('auth')->group(function () {
    Route::get('/books', fn () => '書籍一覧（準備中）')->name('books.index');
});
