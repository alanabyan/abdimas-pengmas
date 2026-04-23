<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\v1\PeminjamanController;
use App\Http\Controllers\Api\v1\WargaController;
use App\Http\Controllers\Api\v1\BarangController;
use App\Http\Controllers\Api\v1\NotifikasiController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\LaporanController;

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

    Route::get('warga/search', [WargaController::class, 'search']);
    Route::get('warga/{warga}/riwayat', [WargaController::class, 'riwayatPeminjaman']);

    // GET /api/v1/notifikasi
    Route::get('notifikasi', [NotifikasiController::class, 'index']);

    // GET api buat laporan
    Route::get('laporan/peminjaman', [LaporanController::class, 'peminjaman']);
    Route::get('laporan/kerusakan', [LaporanController::class, 'kerusakan']);
    Route::get('laporan/stok', [LaporanController::class, 'stok']);

    // buat ekspor pdf
    Route::get('laporan/peminjaman/pdf', [LaporanController::class, 'peminjamanPdf']);


    // PATCH /api/v1/notifikasi/{notifikasi}/baca
    Route::patch('notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'tandaiBaca']);

    // DELETE /api/v1/notifikasi/{notifikasi}
    Route::delete('notifikasi/{notifikasi}', [NotifikasiController::class, 'destroy']);

    // --- Fitur Peminjaman, Warga, & Barang (Punya Lu) ---
    // apiResource udah otomatis nanganin GET (index/show), POST (store), dll.
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::apiResource('warga', WargaController::class);
    Route::apiResource('barang', BarangController::class);

    // Tambahan rute manual (kalau dibutuhin spesifik)
    Route::put('peminjaman/{id}', [PeminjamanController::class, 'update']);
    Route::delete('peminjaman/{id}', [PeminjamanController::class, 'destroy']);
    Route::post('peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan']);
    Route::get('peminjaman/{id}', [PeminjamanController::class, 'show']);
    // Pastiin rutenya pake POST sesuai kodingan Vue lu tadi
    Route::post('pengembalian/{id}/validasi', [PeminjamanController::class, 'validasiKembali']);
    Route::get('dashboard/statistik', [DashboardController::class, 'index']);
    Route::get('dashboard/grafik', [DashboardController::class, 'getGrafik']);
});