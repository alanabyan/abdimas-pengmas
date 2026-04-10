<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang; // Lu butuh ini buat ngurangin stok
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
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
}