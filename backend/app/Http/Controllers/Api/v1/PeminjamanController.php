<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan semua daftar peminjaman
     */
    public function index()
    {
        $data = Peminjaman::with(['barang', 'warga'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Fungsi STORE: Menambah data peminjaman & POTONG STOK
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'barang_id' => 'required|exists:barangs,id',
            'id_warga'  => 'required|exists:wargas,id_warga',
            'jumlah'    => 'required|integer|min:1',
            'tgl_pinjam' => 'required|date',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        return DB::transaction(function () use ($request) {
            // 2. Cek Stok Barang
            $barang = Barang::findOrFail($request->barang_id);

            if ($barang->stok < $request->jumlah) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stok tidak mencukupi! Sisa stok saat ini: ' . $barang->stok
                ], 400);
            }

            // 3. Simpan Data Peminjaman
            $peminjaman = Peminjaman::create([
                'barang_id'      => $request->barang_id,
                'id_warga'       => $request->id_warga,
                'marbot_id'      => 1, // Sementara default marbot ID 1
                'jumlah'         => $request->jumlah,
                'keperluan'      => $request->keperluan,
                'kondisi_pinjam' => $request->kondisi_pinjam ?? 'Baik',
                'tgl_pinjam'     => $request->tgl_pinjam,
                'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
                'status'         => 'Dipinjam',
            ]);

            // 4. LOGIC UTAMA: Potong Stok Barang
            $barang->decrement('stok', $request->jumlah);

            retuphp artisan tinkerrn response()->json([
                'status' => 'success',
                'message' => 'Peminjaman berhasil dicatat & stok barang telah dikurangi.',
                'data' => $peminjaman
            ], 201);
        });
    }

    /**
     * Fungsi KEMBALIKAN: Update status & TAMBAH STOK LAGI
     */
    public function kembalikan(Request $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $peminjaman = Peminjaman::findOrFail($id);

            if ($peminjaman->status === 'Kembali') {
                return response()->json(['message' => 'Barang ini sudah dikembalikan sebelumnya.'], 400);
            }

            // 1. Update data peminjaman
            $peminjaman->update([
                'status' => 'Kembali',
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali ?? 'Baik'
            ]);

            // 2. LOGIC UTAMA: Tambah balik stok barangnya
            $barang = Barang::find($peminjaman->barang_id);
            $barang->increment('stok', $peminjaman->jumlah);

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil dikembalikan & stok gudang telah bertambah.',
                'stok_sekarang' => $barang->stok
            ]);
        });
    }
}