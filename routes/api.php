<?php

use App\Http\Controllers\Api\V1\BookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    // 認証不要
    Route::apiResource('books', BookController::class)
        ->only(['index', 'show'])
        ->names('api.books');

    // 認証必須
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('books', BookController::class)
            ->only(['store', 'update', 'destroy'])
            ->names('api.books');
    });
});
