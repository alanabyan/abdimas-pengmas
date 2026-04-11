<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang; // Lu butuh ini buat ngurangin stok
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjaman = Peminjaman::with(['barang', 'warga'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Peminjaman',
            'data'    => $peminjaman
        ]);
    }
    
    public function store(Request $request)
    {
        // 1. Validasi inputan dari form
        $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'warga_id' => 'required|exists:wargas,id',
            'jumlah' => 'required|integer|min:1',
            'tgl_pinjam' => 'required|date',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
        ]);

        // 2. Gunakan Transaction biar kalau ada error, database nggak berantakan
        return DB::transaction(function () use ($request) {
            $barang = Barang::findOrFail($request->barang_id);

            // Cek apakah stok cukup?
            if ($barang->stok < $request->jumlah) {
                return response()->json(['message' => 'Stok barang tidak mencukupi!'], 400);
            }

            $barang = Barang::findOrFail($request->barang_id);

    // CEK STOK DI SINI
    if ($barang->stok < $request->jumlah) {
        return response()->json([
            'message' => 'Waduh Wa, stok barang nggak cukup!',
            'stok_saat_ini' => $barang->stok
        ], 400); // 400 itu error buat Bad Request
    }

            // 3. Simpan data peminjaman
            $peminjaman = Peminjaman::create([
                'barang_id' => $request->barang_id,
                'warga_id' => $request->warga_id,
                'marbot_id' => 1, // Sementara hardcode dulu, nanti pake auth
                'keperluan' => $request->keperluan,
                'jumlah' => $request->jumlah,
                'kondisi_pinjam' => $request->kondisi_pinjam,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
                'status' => 'Aktif',
            ]);

            // 4. POTONG STOK BARANG (Tugas krusial lu)
            $barang->decrement('stok', $request->jumlah);

            return response()->json([
                'message' => 'Peminjaman berhasil dicatat!',
                'data' => $peminjaman
            ], 201);
        });
    }
    public function kembali(Request $request, $id)
{
    // 1. Cari data peminjamannya
    $peminjaman = Peminjaman::findOrFail($id);

    // 2. Cek statusnya. Kalau udah kembali, jangan dikembaliin lagi (biar stok gak nambah terus)
    if ($peminjaman->status === 'Kembali') {
        return response()->json(['message' => 'Barang ini sudah dikembalikan sebelumnya!'], 400);
    }

    // 3. Update data peminjaman
    $peminjaman->update([
        'tgl_kembali' => now()->toDateString(),
        'kondisi_kembali' => $request->kondisi_kembali ?? 'Baik',
        'status' => 'Kembali',
    ]);

    // 4. BALIKIN STOKNYA (Logic Utama)
    $barang = \App\Models\Barang::find($peminjaman->barang_id);
    $barang->increment('stok', $peminjaman->jumlah);

    return response()->json([
        'message' => 'Barang berhasil dikembalikan, stok bertambah!',
        'data' => $peminjaman->load('barang') // biar keliatan stok terbarunya
    ]);
}
}
