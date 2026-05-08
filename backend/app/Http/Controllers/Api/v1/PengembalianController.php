<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    /**
     * GET /api/v1/pengembalian
     * Daftar peminjaman aktif yang belum dikembalikan.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::with(['warga', 'barang.kategori'])
            ->whereIn('status', ['Aktif', 'Terlambat']);

        if ($request->boolean('terlambat')) {
            $query->where('tgl_rencana_kembali', '<', now()->toDateString());
        }

        $data = $query->orderBy('tgl_rencana_kembali')->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/v1/pengembalian/{id}
     * Marbot memvalidasi pengembalian barang.
     *
     * Kondisi yang tersedia:
     * - Baik        → stok kembali, kondisi barang tetap
     * - Rusak Ringan → stok kembali, kondisi barang diupdate ke 'Rusak Ringan'
     * - Rusak Berat  → stok kembali, kondisi barang diupdate ke 'Rusak Berat'
     * - Hilang       → stok TIDAK kembali, kondisi barang diupdate ke 'Hilang'
     *
     * Catatan WAJIB diisi jika kondisi bukan 'Baik'.
     */
    public function store(Request $request, $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        // Hanya peminjaman aktif / terlambat yang bisa divalidasi
        if (! in_array($peminjaman->status, ['Aktif', 'Terlambat'])) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman ini tidak dalam status yang bisa divalidasi.',
            ], 422);
        }

        $request->validate([
            'kondisi_kembali' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat,Hilang'],
            'catatan'         => ['nullable', 'string', 'max:1000'],
        ]);

        // Catatan wajib jika kondisi bukan Baik
        if ($request->kondisi_kembali !== 'Baik' && empty(trim($request->catatan ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan wajib diisi jika kondisi barang bukan Baik.',
                'errors'  => ['catatan' => ['Catatan wajib diisi jika kondisi barang bukan Baik.']],
            ], 422);
        }

        DB::transaction(function () use ($request, $peminjaman) {
            $kondisi = $request->kondisi_kembali;
            $barang  = $peminjaman->barang;

            // 1. Kembalikan stok jika barang fisiknya ada (bukan Hilang)
            if ($kondisi !== 'Hilang' && $barang) {
                $barang->increment('stok_tersedia', $peminjaman->jumlah);
            }

            // 2. Update kondisi barang sesuai hasil pengembalian
            if ($barang) {

                // Jika barang hilang
                if ($kondisi === 'Hilang') {

                    // stok total berkurang karena barang hilang permanen
                    $barang->decrement('stok_total', $peminjaman->jumlah);
                } else {

                    // Update kondisi barang jika rusak
                    $kondisiBarangBaru = match ($kondisi) {
                        'Rusak Ringan' => 'Rusak Ringan',
                        'Rusak Berat'  => 'Rusak Berat',
                        default        => $barang->kondisi, // Baik → tidak berubah
                    };

                    $barang->update([
                        'kondisi' => $kondisiBarangBaru
                    ]);
                }
            }

            // 3. Tentukan status akhir peminjaman
            $statusAkhir = ($kondisi === 'Baik') ? 'Selesai' : 'Rusak/Hilang';

            // 4. Simpan data pengembalian
            $peminjaman->update([
                'tgl_kembali_aktual' => now(),
                'kondisi_kembali'    => $kondisi,
                'status'             => $statusAkhir,
                'catatan'            => $request->catatan,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian berhasil divalidasi.',
            'data'    => $peminjaman->fresh()->load(['warga', 'barang.kategori']),
        ]);
    }
}
