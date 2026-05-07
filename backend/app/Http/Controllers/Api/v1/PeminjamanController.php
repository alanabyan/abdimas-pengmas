<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * GET /api/v1/peminjaman
     * List semua peminjaman dengan filter status dan tanggal.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::with([
            'warga:id,nama_warga,no_hp,rt_rw',
            'barang:id,nama_barang,foto_url',
            'marbot:id,nama_marbot',
        ]);

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('dari') || $request->filled('sampai')) {
            $query->byTanggal($request->dari, $request->sampai);
        }

        if ($request->filled('warga_id')) {
            $query->where('warga_id', $request->warga_id);
        }

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }

        $peminjamans = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($peminjamans);
    }

    /**
     * POST /api/v1/peminjaman
     * Buat peminjaman baru. Stok barang berkurang otomatis.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'barang_id'           => ['required', 'exists:barangs,id'],
            'warga_id'            => ['required', 'exists:wargas,id'],
            'keperluan'           => ['required', 'string', 'max:255'],
            'jumlah'              => ['required', 'integer', 'min:1'],
            'kondisi_pinjam'      => ['required', 'string', 'max:255'],
            'tgl_pinjam'          => ['required', 'date'],
            'tgl_rencana_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
            'catatan'             => ['nullable', 'string'],
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        // Validasi ketersediaan stok
        if ($barang->stok_tersedia < $request->jumlah) {
            return response()->json([
                'message' => "Stok tidak mencukupi. Tersedia: {$barang->stok_tersedia} unit.",
            ], 422);
        }

        $peminjaman = DB::transaction(function () use ($request, $barang) {
            // Kurangi stok terlebih dahulu
            $barang->kurangiStok($request->jumlah);

            return Peminjaman::create([
                'barang_id'           => $request->barang_id,
                'warga_id'            => $request->warga_id,
                'marbot_id'           => $request->user()->id,
                'keperluan'           => $request->keperluan,
                'jumlah'              => $request->jumlah,
                'kondisi_pinjam'      => $request->kondisi_pinjam,
                'tgl_pinjam'          => $request->tgl_pinjam,
                'tgl_rencana_kembali' => $request->tgl_rencana_kembali,
                'status'              => Peminjaman::STATUS_MENUNGGU,
                'catatan'             => $request->catatan,
            ]);
        });

        $peminjaman->load(['warga:id,nama_warga,no_hp', 'barang:id,nama_barang', 'marbot:id,nama_marbot']);

        return response()->json([
            'message' => 'Peminjaman berhasil dibuat dan menunggu konfirmasi.',
            'data'    => $peminjaman,
        ], 201);
    }

    /**
     * GET /api/v1/peminjaman/{peminjaman}
     * Detail peminjaman lengkap.
     */
    public function show(Peminjaman $peminjaman): JsonResponse
    {
        $peminjaman->load([
            'warga',
            'barang.kategori:id,nama',
            'marbot:id,nama_marbot,email',
        ]);

        return response()->json(['data' => $peminjaman]);
    }

    /**
     * PUT /api/v1/peminjaman/{peminjaman}
     * Edit peminjaman. Hanya bisa diubah jika status masih 'Menunggu'.
     */
    public function update(Request $request, Peminjaman $peminjaman): JsonResponse
    {
        if (! $peminjaman->bisaDiEdit()) {
            return response()->json([
                'message' => 'Peminjaman tidak dapat diubah karena sudah dikonfirmasi.',
            ], 422);
        }

        $request->validate([
            'warga_id'            => ['required', 'exists:wargas,id'],
            'keperluan'           => ['required', 'string', 'max:255'],
            'jumlah'              => ['required', 'integer', 'min:1'],
            'kondisi_pinjam'      => ['required', 'string', 'max:255'],
            'tgl_pinjam'          => ['required', 'date'],
            'tgl_rencana_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
            'catatan'             => ['nullable', 'string'],
        ]);

        $barang = $peminjaman->barang;

        // Hitung delta jika jumlah berubah
        $deltaJumlah = $request->jumlah - $peminjaman->jumlah;
        if ($deltaJumlah > 0 && $barang->stok_tersedia < $deltaJumlah) {
            return response()->json([
                'message' => "Stok tidak mencukupi untuk penambahan jumlah.",
            ], 422);
        }

        DB::transaction(function () use ($request, $peminjaman, $barang, $deltaJumlah) {
            // Sesuaikan stok jika jumlah berubah
            if ($deltaJumlah > 0) {
                $barang->kurangiStok($deltaJumlah);
            } elseif ($deltaJumlah < 0) {
                $barang->tambahStok(abs($deltaJumlah));
            }

            $peminjaman->update($request->only(
                'warga_id',
                'keperluan',
                'jumlah',
                'kondisi_pinjam',
                'tgl_pinjam',
                'tgl_rencana_kembali',
                'catatan'
            ));
        });

        return response()->json([
            'message' => 'Peminjaman berhasil diperbarui.',
            'data'    => $peminjaman->fresh()->load(['warga:id,nama_warga', 'barang:id,nama_barang']),
        ]);
    }

    /**
     * DELETE /api/v1/peminjaman/{peminjaman}
     * Batalkan peminjaman. Stok dikembalikan.
     */
    public function destroy(Peminjaman $peminjaman): JsonResponse
    {
        if (! $peminjaman->bisaDibatalkan()) {
            return response()->json([
                'message' => 'Peminjaman dengan status ini tidak dapat dibatalkan.',
            ], 422);
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->barang->tambahStok($peminjaman->jumlah);
            $peminjaman->update(['status' => Peminjaman::STATUS_BATAL]);
        });

        return response()->json(['message' => 'Peminjaman berhasil dibatalkan dan stok dikembalikan.']);
    }

    /**
     * PATCH /api/v1/peminjaman/{peminjaman}/konfirmasi
     * Marbot mengonfirmasi peminjaman → status 'Aktif'.
     */
    public function konfirmasi(Peminjaman $peminjaman): JsonResponse
    {
        if ($peminjaman->status !== Peminjaman::STATUS_MENUNGGU) {
            return response()->json([
                'message' => 'Hanya peminjaman berstatus Menunggu yang bisa dikonfirmasi.',
            ], 422);
        }

        $peminjaman->update(['status' => Peminjaman::STATUS_AKTIF]);

        return response()->json([
            'message' => 'Peminjaman berhasil dikonfirmasi dan berstatus Aktif.',
            'data'    => $peminjaman->fresh(),
        ]);
    }
}
