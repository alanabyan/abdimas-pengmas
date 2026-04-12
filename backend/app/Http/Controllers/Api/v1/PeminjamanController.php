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
    // 1. Validasi inputan
    $request->validate([
        'barang_id' => 'required|exists:barangs,id',
        'warga_id' => 'required|exists:wargas,id', // Sesuaikan kalau PK warga itu 'id'
        'jumlah' => 'required|integer|min:1',
        'tgl_pinjam' => 'required|date',
        'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
    ]);

    // 2. Gunakan Transaction
    return DB::transaction(function () use ($request) {
        $barang = Barang::findOrFail($request->barang_id);

        // CEK STOK (Pake 'stok_tersedia' sesuai perbaikan DB kemarin)
        if ($barang->stok_tersedia < $request->jumlah) {
            return response()->json([
                'message' => "Stok {$barang->nama_barang} tidak mencukupi! Sisa: {$barang->stok_tersedia}"
            ], 422); 
        }

        // 3. Simpan data peminjaman
        $peminjaman = Peminjaman::create([
            'barang_id'          => $request->barang_id,
            'warga_id'           => $request->warga_id,
            'marbot_id'          => 1, // Nanti ganti pake auth()->id() kalau udah ada login
            'keperluan'          => $request->keperluan,
            'jumlah'             => $request->jumlah,
            'kondisi_pinjam'     => $request->kondisi_pinjam,
            'tgl_pinjam'         => $request->tgl_pinjam,
            'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
            'status'             => 'Pinjam', // Samain sama yang di DaftarPinjaman.vue tadi
        ]);

        // 4. POTONG STOK BARANG
        $barang->decrement('stok_tersedia', $request->jumlah);

        return response()->json([
            'message' => 'Alhamdulillah! Peminjaman berhasil dicatat.',
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
        return DB::transaction(function () use ($peminjaman, $request) {
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
    });
    }

    public function update(Request $request, $id)
{
    // 1. Validasi input
    $request->validate([
        'barang_id' => 'required|exists:barangs,id',
        'warga_id'  => 'required|exists:wargas,id_warga',
        'jumlah'    => 'required|integer|min:1',
    ]);

    return DB::transaction(function () use ($request, $id) {
        $peminjaman = Peminjaman::findOrFail($id);
        $barang = Barang::findOrFail($request->barang_id);

        // LOGIKA UPDATE STOK:
        // Kita balikin dulu stok lama, baru kurangin sama jumlah yang baru
        // Ini cara paling aman biar nggak pusing itung selisihnya
        
        // Step A: Balikin stok lama
        $barangOld = Barang::find($peminjaman->barang_id);
        $barangOld->increment('stok', $peminjaman->jumlah);

        // Step B: Cek apakah stok cukup buat jumlah yang baru?
        // Refresh data barang (karena tadi udah di-increment)
        $barang->refresh(); 
        
        if ($barang->stok < $request->jumlah) {
            return response()->json([
                'message' => 'Waduh Wa, stok nggak cukup buat update ini!',
                'stok_tersedia' => $barang->stok
            ], 400);
        }

        // Step C: Kurangin stok dengan jumlah baru
        $barang->decrement('stok', $request->jumlah);

        // 2. Update data peminjaman
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

public function destroy($id)
{
    return DB::transaction(function () use ($id) {
        // 1. Cari data peminjamannya
        $peminjaman = Peminjaman::findOrFail($id);

        // 2. LOGIKA BALIKIN STOK
        // Kita hanya balikin stok kalau statusnya belum 'Kembali'
        // Kalau statusnya sudah 'Kembali', stoknya kan sudah balik pas proses return tadi
        if ($peminjaman->status !== 'Kembali') {
            $barang = Barang::findOrFail($peminjaman->barang_id);
            $barang->increment('stok', $peminjaman->jumlah);
        }

        // 3. Hapus data
        $peminjaman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data peminjaman berhasil dihapus dan stok telah disesuaikan!'
        ]);
    });
}

public function kembalikan($id)
{
    // Cari data peminjaman beserta data barangnya sekalian (Eager Loading)
    $peminjaman = Peminjaman::with('barang')->find($id);

    if (!$peminjaman) {
        return response()->json(['message' => 'Data tidak ditemukan!'], 404);
    }

    if ($peminjaman->status === 'Kembali') {
        return response()->json(['message' => 'Barang sudah dikembalikan sebelumnya.'], 400);
    }

    // 1. Update status
    $peminjaman->update([
        'status' => 'Kembali',
        'tgl_kembali' => now()->toDateString()
    ]);

    // 2. Cek apakah relasi barang ada sebelum nambahin stok
    if ($peminjaman->barang) {
        $peminjaman->barang->increment('stok_tersedia', $peminjaman->jumlah);
    } else {
        return response()->json(['message' => 'Data barang tidak ditemukan, stok gagal diupdate.'], 422);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Alhamdulillah! Barang berhasil dikembalikan.',
        'data' => $peminjaman
    ]);
}
}