<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan semua daftar peminjaman
     */
    public function index()
    {
        $peminjaman = Peminjaman::with(['barang', 'warga'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Peminjaman',
            'data'    => $peminjaman
        ]);
    }

    /**
     * Simpan peminjaman baru + Potong Stok
     */
    public function store(Request $request)
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

            // Cek Stok Tersedia
            if ($barang->stok_tersedia < $request->jumlah) {
                return response()->json([
                    'message' => "Stok {$barang->nama_barang} tidak mencukupi! Sisa: {$barang->stok_tersedia}"
                ], 422);
            }

            // Simpan Data
            $peminjaman = Peminjaman::create([
                'barang_id'           => $request->barang_id,
                'warga_id'            => $request->warga_id,
                'marbot_id'           => 1, // Nanti ganti auth()->id()
                'keperluan'           => $request->keperluan,
                'jumlah'              => $request->jumlah,
                'kondisi_pinjam'      => $request->kondisi_pinjam,
                'tgl_pinjam'          => $request->tgl_pinjam,
                'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
                'status'              => 'Pinjam',
            ]);

            // Potong Stok
            $barang->decrement('stok_tersedia', $request->jumlah);

            return response()->json([
                'message' => 'Alhamdulillah! Peminjaman berhasil dicatat.',
                'data'    => $peminjaman
            ], 201);
        });
    }

    /**
     * Fungsi Pengembalian (Update Status & Balikin Stok)
     */
    public function kembalikan($id)
    {
        $peminjaman = Peminjaman::with('barang')->find($id);

        if (!$peminjaman) {
            return response()->json(['message' => 'Data tidak ditemukan!'], 404);
        }

        if ($peminjaman->status === 'Kembali') {
            return response()->json(['message' => 'Barang sudah dikembalikan sebelumnya.'], 400);
        }

        return DB::transaction(function () use ($peminjaman) {
            // 1. Update status
            $peminjaman->update([
                'status'      => 'Kembali',
                'tgl_kembali' => now()->toDateString()
            ]);

            // 2. Balikin stok
            if ($peminjaman->barang) {
                $peminjaman->barang->increment('stok_tersedia', $peminjaman->jumlah);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Alhamdulillah! Barang berhasil dikembalikan.',
                'data'    => $peminjaman
            ]);
        });
    }

    /**
     * Update data peminjaman (Kalau ada salah input)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'barang_id'           => 'required|exists:barangs,id',
            'warga_id'            => 'required|exists:wargas,id',
            'jumlah'              => 'required|integer|min:1',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $peminjaman = Peminjaman::findOrFail($id);
            $barang = Barang::findOrFail($request->barang_id);

            // Balikin dulu stok lama
            $barangOld = Barang::find($peminjaman->barang_id);
            $barangOld->increment('stok_tersedia', $peminjaman->jumlah);

            // Cek stok baru
            $barang->refresh();
            if ($barang->stok_tersedia < $request->jumlah) {
                return response()->json(['message' => 'Stok nggak cukup buat update!'], 400);
            }

            // Potong stok baru
            $barang->decrement('stok_tersedia', $request->jumlah);

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
                'message' => 'Data peminjaman berhasil diperbarui!',
                'data'    => $peminjaman->load('barang')
            ]);
        });
    }

    /**
     * Hapus Peminjaman (Stok balik kalau status belum 'Kembali')
     */
    public function destroy($id)
{
    return DB::transaction(function () use ($id) {
        $peminjaman = Peminjaman::findOrFail($id);

        // Kalau statusnya masih 'Pinjam', balikin dulu stoknya sebelum dihapus
        if ($peminjaman->status === 'Pinjam') {
            $barang = $peminjaman->barang;
            if ($barang) {
                $barang->increment('stok_tersedia', $peminjaman->jumlah);
            }
        }

        $peminjaman->delete();

        return response()->json(['message' => 'Data peminjaman berhasil dihapus dan stok diperbarui!']);
    });
}

}