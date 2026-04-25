<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // 1. Cek nama kolom, biasanya 'stok' bukan 'stok_total'
            // Kalau di DB namanya 'stok', ganti sum('stok')
            $totalBarang = Barang::sum('stok_total') ?: 0; 

            // 2. Transaksi yang masih status 'Pinjam'
            $peminjamanAktif = Peminjaman::where('status', 'Pinjam')->count();

            // 3. Hitung barang rusak (Sesuaikan dengan isi kolom kondisi )
            $barangBermasalah = Barang::where('kondisi', '!=', 'Baik')->count();

            // 4. Stok kritis (Sesuai kolom stok_tersedia)
            $stokRendah = Barang::where('stok_tersedia', '<', 5)->count();

            return response()->json([
                'success' => true,
                'message' => 'Statistik Dashboard Berhasil Dimuat',
                'data' => [
                    'total_barang'     => (int)$totalBarang,
                    'peminjaman_aktif' => $peminjamanAktif,
                    'barang_rusak'     => $barangBermasalah,
                    'stok_rendah'      => $stokRendah
                ]
            ]);
        } catch (\Exception $e) {
            // Biar bisa liat error aslinya di log
            Log::error("Dashboard Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Backend pusing, Wa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getGrafik(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                'datasets' => [
                    [
                        'label' => 'Jumlah Peminjaman',
                        'backgroundColor' => '#10b981',
                        'borderColor' => '#059669',
                        'borderWidth' => 1,
                        'data' => [12, 19, 3, 5, 2, 20, 15]
                    ]
                ]
            ]
        ]);
    }
}