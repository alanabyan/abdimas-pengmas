<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan daftar peminjaman dengan relasi kategori
     */
    public function index(): JsonResponse
    {
        // Kuncinya di 'barang.kategori' biar sinkron sama data 
        $peminjaman = Peminjaman::with(['barang.kategori', 'warga', 'marbot'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Peminjaman',
            'data'    => $peminjaman
        ]);
    }

    /**
     * Tampilkan detail peminjaman
     */
    public function show($id): JsonResponse
    {
        $peminjaman = Peminjaman::with(['barang.kategori', 'warga', 'marbot'])->find($id);

        if (!$peminjaman) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $peminjaman
        ]);
    }

    /**
     * Simpan peminjaman baru + Potong Stok
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'barang_id'           => 'required|exists:barangs,id',
            'warga_id'            => 'required|exists:wargas,id',
            'jumlah'              => 'required|integer|min:1',
            'tgl_pinjam'          => 'required|date',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
        ]);

        return DB::transaction(function () use ($request) {
            $barang = Barang::findOrFail($request->barang_id);

            // Cek Stok
            if ($barang->stok_tersedia < $request->jumlah) {
                return response()->json([
                    'message' => "Stok {$barang->nama_barang} tidak cukup! Sisa: {$barang->stok_tersedia}"
                ], 422);
            }

            // Simpan Data
            $peminjaman = Peminjaman::create([
                'barang_id'           => $request->barang_id,
                'warga_id'            => $request->warga_id,
                'marbot_id'           => 1, // Sementara manual atau Auth::id()
                'keperluan'           => $request->keperluan,
                'jumlah'              => $request->jumlah,
                'kondisi_pinjam'      => $request->kondisi_pinjam ?? 'Baik',
                'tgl_pinjam'          => $request->tgl_pinjam,
                'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
                'status'              => 'Aktif',
            ]);

            // Potong Stok
            $barang->decrement('stok_tersedia', $request->jumlah);

            return response()->json([
                'message' => 'Peminjaman berhasil dicatat.',
                'data'    => $peminjaman->load('barang.kategori') // Load balik biar lsg muncul di FE
            ], 201);
        });
    }

    /**
     * Update data peminjaman (Revisi data)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'barang_id'           => 'required|exists:barangs,id',
            'warga_id'            => 'required|exists:wargas,id',
            'jumlah'              => 'required|integer|min:1',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $peminjaman = Peminjaman::findOrFail($id);
            
            // Balikin stok lama dulu
            $barangOld = Barang::find($peminjaman->barang_id);
            $barangOld->increment('stok_tersedia', $peminjaman->jumlah);

            // Cek stok baru
            $barangNew = Barang::findOrFail($request->barang_id);
            $barangNew->refresh();
            
            if ($barangNew->stok_tersedia < $request->jumlah) {
                $barangOld->decrement('stok_tersedia', $peminjaman->jumlah);
                return response()->json(['message' => 'Stok tidak mencukupi!'], 422);
            }

            // Potong stok baru
            $barangNew->decrement('stok_tersedia', $request->jumlah);

            $peminjaman->update([
                'barang_id'           => $request->barang_id,
                'warga_id'            => $request->warga_id,
                'jumlah'              => $request->jumlah,
                'keperluan'           => $request->keperluan,
                'kondisi_pinjam'      => $request->kondisi_pinjam,
                'tgl_pinjam'          => $request->tgl_pinjam,
                'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data peminjaman diperbarui!',
                'data'    => $peminjaman->load('barang.kategori')
            ]);
        });
    }

    /**
     * Hapus Peminjaman
     */
    public function destroy($id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::findOrFail($id);

            // Jika dihapus saat masih dipinjam, stok balik
            if (in_array($peminjaman->status, ['Aktif', 'Terlambat'])) {
                $barang = Barang::find($peminjaman->barang_id);
                if ($barang) {
                    $barang->increment('stok_tersedia', $peminjaman->jumlah);
                }
            }

            $peminjaman->delete();
            return response()->json(['message' => 'Peminjaman dihapus dan stok dikembalikan.']);
        });
    }
}