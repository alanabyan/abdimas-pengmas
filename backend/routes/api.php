<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\v1\PeminjamanController;
use App\Http\Controllers\Api\v1\WargaController;
use App\Http\Controllers\Api\v1\BarangController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    // --- Fitur Auth (Punya Alan) ---
    Route::prefix('auth')->group(function () {
        // POST /api/v1/auth/login
        Route::post('login', [AuthController::class, 'login']);
    });

    // --- Fitur Peminjaman, Warga, & Barang (Punya Lu) ---
    // apiResource udah otomatis nanganin GET (index/show), POST (store), dll.
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::apiResource('warga', WargaController::class);
    Route::apiResource('barang', BarangController::class);

    // Tambahan rute manual (kalau dibutuhin spesifik)
    Route::put('peminjaman/{id}', [PeminjamanController::class, 'update']);
    Route::delete('peminjaman/{id}', [PeminjamanController::class, 'destroy']);
    Route::post('peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan']);
});