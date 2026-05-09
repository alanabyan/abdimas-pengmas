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
     */
    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::with(['warga', 'barang.kategori'])
            ->whereIn('status', ['Aktif', 'Terlambat', 'Sebagian Kembali']); // ← tambahkan

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
     *
     * Kondisi:
     * - Baik         → stok_tersedia +jumlah_kembali, stok_total -sisa (jika ada)
     * - Rusak Ringan → stok_tersedia +jumlah_kembali, stok_total -sisa, kondisi barang diupdate
     * - Rusak Berat  → stok_tersedia +jumlah_kembali, stok_total -sisa, kondisi barang diupdate
     * - Hilang       → stok_tersedia tidak berubah, stok_total -semua
     */
    public function store(Request $request, $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        if (! in_array($peminjaman->status, ['Aktif', 'Terlambat', 'Sebagian Kembali'])) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman ini tidak dalam status yang bisa divalidasi.',
            ], 422);
        }

        $sudahKembali = $peminjaman->jumlah_kembali ?? 0;
        $sisaBelum    = $peminjaman->jumlah - $sudahKembali;

        $request->validate([
            'kondisi_kembali' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat,Hilang'],
            'catatan'         => ['nullable', 'string', 'max:1000'],
            'jumlah_kembali'  => ['nullable', 'integer', 'min:1', 'max:' . $sisaBelum],
        ]);

        if ($request->kondisi_kembali !== 'Baik' && empty(trim($request->catatan ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan wajib diisi jika kondisi barang bukan Baik.',
                'errors'  => ['catatan' => ['Catatan wajib diisi jika kondisi barang bukan Baik.']],
            ], 422);
        }

        $jumlahKembaliSekarang = $request->jumlah_kembali ?? $sisaBelum;
        $totalSudahKembali     = $sudahKembali + $jumlahKembaliSekarang;
        $masihAdaSisa          = $totalSudahKembali < $peminjaman->jumlah;

        DB::transaction(function () use ($request, $peminjaman, $jumlahKembaliSekarang, $totalSudahKembali, $masihAdaSisa) {
            $kondisi = $request->kondisi_kembali;
            $barang  = $peminjaman->barang;

            if ($barang) {
                if ($kondisi === 'Hilang') {
                    $barang->decrement('stok_total', $jumlahKembaliSekarang);
                } else {
                    $barang->increment('stok_tersedia', $jumlahKembaliSekarang);

                    if ($kondisi === 'Rusak Ringan') {
                        $kondisiBarangBaru = 'Rusak Ringan';
                    } elseif ($kondisi === 'Rusak Berat') {
                        $kondisiBarangBaru = 'Rusak Berat';
                    } else {
                        $kondisiBarangBaru = $barang->kondisi;
                    }

                    $barang->update(['kondisi' => $kondisiBarangBaru]);
                }
            }

            if ($masihAdaSisa) {
                $statusAkhir = 'Sebagian Kembali';
            } elseif ($kondisi === 'Baik') {
                $statusAkhir = 'Selesai';
            } else {
                $statusAkhir = 'Rusak/Hilang';
            }

            $peminjaman->update([
                'tgl_kembali_aktual' => $masihAdaSisa ? null : now(),
                'jumlah_kembali'     => $totalSudahKembali,
                'kondisi_kembali'    => $kondisi,
                'status'             => $statusAkhir,
                'catatan'            => $request->catatan,
            ]);
        });

        $fresh    = $peminjaman->fresh();
        $sisaAkhir = $fresh->jumlah - $totalSudahKembali;

        if ($masihAdaSisa) {
            $message = 'Pengembalian sebagian dicatat. Masih ada ' . $sisaAkhir . ' unit belum dikembalikan.';
        } else {
            $message = 'Pengembalian berhasil divalidasi.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $fresh->load(['warga', 'barang.kategori']),
        ]);
    }
}
