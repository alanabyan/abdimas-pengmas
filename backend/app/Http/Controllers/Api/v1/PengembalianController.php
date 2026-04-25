<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PengembalianController extends Controller
{
    /**
     * Menampilkan daftar barang yang sedang dipinjam (untuk menu pengembalian)
     */
    public function index(): JsonResponse
    {
        // TAMBAHIN barang.kategori DISINI biar sinkron
        $data = Peminjaman::with(['barang.kategori', 'warga'])
            ->whereIn('status', ['Aktif', 'Terlambat']) 
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    /**
     * Proses pengembalian barang
     */
    public function store(Request $request, $id): JsonResponse
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
        ]);

        $peminjaman = Peminjaman::findOrFail($id);
        $barang = Barang::find($peminjaman->barang_id);

        // 1. Update data pengembalian
        $peminjaman->tgl_kembali_aktual = now();
        $peminjaman->kondisi_kembali = $request->kondisi_kembali;
        $peminjaman->catatan = $request->catatan;

        // 2. LOGIKA OTOMATIS STATUS
        if ($request->kondisi_kembali === 'Baik') {
            $peminjaman->status = 'Selesai';
        } else {
            $peminjaman->status = 'Rusak/Hilang';
        }

        // 3. Kembalikan stok barang
        // Barang kembali ke stok jika kondisinya 'Baik' atau 'Rusak' (tapi fisik ada)
        // Jika statusnya 'Hilang', stok tidak bertambah
        if ($request->kondisi_kembali !== 'Hilang' && $barang) {
            $barang->increment('stok_tersedia', $peminjaman->jumlah);
        }

        $peminjaman->save();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dikembalikan!',
            'data'    => $peminjaman->load('barang.kategori')
        ]);
    }
}