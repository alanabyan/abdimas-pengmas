<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\v1\PeminjamanController;

Route::prefix('v1')->group(function () {
    // Jalur untuk fitur peminjaman lu
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::post('/peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan']);
});