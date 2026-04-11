<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\v1\PeminjamanController;
use App\Http\Controllers\Api\v1\WargaController;
use App\Http\Controllers\Api\v1\BarangController;

Route::prefix('v1')->group(function () {
<<<<<<< Updated upstream
=======

    Route::prefix('auth')->group(function () {
        // POST /api/v1/auth/login
        // Body : { email, password }
        // Return: { token, user: { id, nama, email, aktif } }
        Route::post('login', [AuthController::class, 'login']);
        Route::get('warga', [WargaController::class, 'index']);
        Route::get('barang', [BarangController::class, 'index']);
    });

>>>>>>> Stashed changes
    // Jalur untuk fitur peminjaman lu
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::apiResource('warga', WargaController::class);
    Route::apiResource('barang', BarangController::class);
    Route::put('peminjaman/{id}', [PeminjamanController::class, 'update']);
    Route::delete('peminjaman/{id}', [PeminjamanController::class, 'destroy']);
});