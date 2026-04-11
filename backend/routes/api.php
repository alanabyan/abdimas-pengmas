<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\v1\PeminjamanController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        // POST /api/v1/auth/login
        // Body : { email, password }
        // Return: { token, user: { id, nama, email, aktif } }
        Route::post('login', [AuthController::class, 'login']);
    });

    // Jalur untuk fitur peminjaman lu
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::put('peminjaman/{id}', [PeminjamanController::class, 'update']);
    Route::delete('peminjaman/{id}', [PeminjamanController::class, 'destroy']);
});