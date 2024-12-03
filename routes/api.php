<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::post('/login', [ApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/category', [ApiController::class, 'category']);
    Route::get('/cart', [ApiController::class, 'cart']);
    Route::post('/logout', [ApiController::class, 'logout']);
});
