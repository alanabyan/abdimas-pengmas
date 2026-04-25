<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\v1\KategoriController;
use App\Http\Controllers\Api\v1\PeminjamanController;
use App\Http\Controllers\Api\v1\WargaController;
use App\Http\Controllers\Api\v1\BarangController;
use App\Http\Controllers\Api\v1\NotifikasiController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\LaporanController;
use App\Http\Controllers\Api\v1\PengembalianController;
use App\Http\Controllers\Api\v1\MarbotController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    // --- Fitur Auth ---
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    // --- Fitur Warga & Notifikasi ---
    Route::get('warga/search', [WargaController::class, 'search']);
    Route::get('warga/{warga}/riwayat', [WargaController::class, 'riwayatPeminjaman']);
    Route::get('notifikasi', [NotifikasiController::class, 'index']);
    Route::patch('notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'tandaiBaca']);
    Route::delete('notifikasi/{notifikasi}', [NotifikasiController::class, 'destroy']);

    // --- Fitur Laporan (Udah OK) ---
    Route::get('laporan/peminjaman', [LaporanController::class, 'peminjaman']);
    Route::get('laporan/kerusakan', [LaporanController::class, 'kerusakan']);
    Route::get('laporan/stok', [LaporanController::class, 'stok']);
    Route::get('laporan/peminjaman/pdf', [LaporanController::class, 'peminjamanPdf']);
    Route::get('laporan/kerusakan/pdf', [LaporanController::class, 'kerusakanPdf']); // Tambahin ini buat PDF kerusakan
    Route::get('laporan/tren-peminjaman', [LaporanController::class, 'trenPeminjaman']);

    // --- Fitur Barang ---
    Route::post('barang/{barang}/foto', [BarangController::class, 'uploadFoto']);
    Route::delete('barang/{barang}/foto', [BarangController::class, 'hapusFoto']);

    // --- Fitur Dashboard ---
    Route::get('dashboard/statistik', [DashboardController::class, 'index']);
    Route::get('dashboard/grafik', [DashboardController::class, 'getGrafik']);

    // --- Fitur CRUD Utama ---
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::apiResource('warga', WargaController::class);
    Route::apiResource('barang', BarangController::class);
    Route::apiResource('kategori', KategoriController::class);

    // --- 2. FITUR PENGEMBALIAN (Jalur Baru) ---
    // GET: /api/v1/pengembalian -> Menampilkan barang yang bisa dikembalikan
    Route::get('pengembalian', [PengembalianController::class, 'index']);
    // POST: /api/v1/pengembalian/{id} -> Proses simpan pengembalian
    Route::post('pengembalian/{id}', [PengembalianController::class, 'store']);

    //update data marbot
    Route::put('marbot/profile/update', [MarbotController::class, 'updateProfile']);
    Route::get('marbot/{marbot}', [MarbotController::class, 'show']);
});