<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LaporanController extends Controller
{
    /**
     * GET /api/v1/laporan/peminjaman
     * Data laporan peminjaman dengan filter tanggal dan warga.
     */
    public function peminjaman(Request $request): JsonResponse
    {
        $request->validate([
            'dari'     => ['nullable', 'date'],
            'sampai'   => ['nullable', 'date', 'after_or_equal:dari'],
            'warga_id' => ['nullable', 'exists:wargas,id'],
            'status'   => ['nullable', 'string'],
        ]);

        $query = Peminjaman::with([
            'warga:id,nama_warga,no_hp,rt_rw',
            'barang' => function ($q) {
                $q->select('id', 'nama_barang', 'kategori_id')
                    ->with('kategori:id,nama');
            },
            'marbot:id,nama_marbot',
        ]);

        $this->terapkanFilterLaporan($query, $request);

        $data = $query->orderBy('tgl_pinjam', 'desc')->get();

        return response()->json([
            'data'  => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * GET /api/v1/laporan/peminjaman/pdf
     * Unduh PDF laporan peminjaman.
     */
    public function peminjamanPdf(Request $request): Response
    {
        $dari = $request->dari ?? now()->startOfMonth()->format('Y-m-d');

        $sampai = $request->sampai ?? now()->addMonth()->startOfMonth()->format('Y-m-d');

        $query = Peminjaman::with([
            'warga:id,nama_warga,no_hp,rt_rw',
            'barang' => function ($q) {
                $q->select('id', 'nama_barang', 'kategori_id')
                    ->with('kategori:id,nama');
            },
            'marbot:id,nama_marbot',
        ]);

        $this->terapkanFilterLaporan($query, $request);

        // default filter jika request kosong
        if (!$request->filled('dari')) {
            $query->whereDate('tgl_pinjam', '>=', $dari);
        }

        if (!$request->filled('sampai')) {
            $query->whereDate('tgl_pinjam', '<=', $sampai);
        }

        $peminjamans = $query
            ->orderBy('tgl_pinjam', 'desc')
            ->get();

        $pdf = Pdf::loadView('laporan.peminjaman', [
            'peminjamans' => $peminjamans,

            'dari' => $request->dari
                ?? now()->startOfMonth()->format('Y-m-d'),

            'sampai' => $request->sampai
                ?? now()->addMonth()->startOfMonth()->format('Y-m-d'),

            'tanggal' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'laporan-peminjaman-' . now()->format('Ymd') . '.pdf'
        );
    }

    /**
     * GET /api/v1/laporan/kerusakan
     * Data laporan barang rusak dan hilang.
     */
    public function kerusakan(Request $request): JsonResponse
    {
        $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
        ]);

        $query = Peminjaman::with([
            'warga:id,nama_warga,no_hp',
            'barang' => function ($q) {
                $q->select('id', 'nama_barang', 'kategori_id')
                    ->with('kategori:id,nama');
            },

            'marbot:id,nama_marbot',
        ])->where('status', Peminjaman::STATUS_RUSAK_HILANG);

        if ($request->filled('dari')) {
            $query->where('tgl_kembali_aktual', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->where('tgl_kembali_aktual', '<=', $request->sampai);
        }

        $data = $query->orderBy('tgl_kembali_aktual', 'desc')->get();

        return response()->json([
            'data'  => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * GET /api/v1/laporan/kerusakan/pdf
     */
    public function kerusakanPdf(Request $request): Response
    {
        $query = Peminjaman::with([
            'warga:id,nama_warga,no_hp',
            'barang' => function ($q) {
                $q->select('id', 'nama_barang', 'kategori_id')
                    ->with('kategori:id,nama');
            },

            'marbot:id,nama_marbot',
        ])->where('status', Peminjaman::STATUS_RUSAK_HILANG);

        if ($request->filled('dari')) {
            $query->where('tgl_kembali_aktual', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->where('tgl_kembali_aktual', '<=', $request->sampai);
        }

        $peminjamans = $query->orderBy('tgl_kembali_aktual', 'desc')->get();

        $pdf = Pdf::loadView('laporan.kerusakan', [
            'peminjamans' => $peminjamans,
            'dari'        => $request->dari,
            'sampai'      => $request->sampai,
            'tanggal'     => now()->format('d/m/Y H:i'),
            'total'    => $peminjamans->count(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kerusakan-' . now()->format('Ymd') . '.pdf');
    }

    /**
     * GET /api/v1/laporan/stok
     * Rekap stok barang per kategori.
     */
    public function stok(): JsonResponse
    {
        $kategoris = Kategori::with([
            'barangs:id,kategori_id,nama_barang,stok_total,stok_tersedia,kondisi,lokasi',
        ])
            ->withCount('barangs')
            ->orderBy('nama')
            ->get();

        $summary = $kategoris->map(fn($k) => [
            'kategori'      => $k->nama,
            'jumlah_barang' => $k->barangs_count,
            'stok_total'    => $k->barangs->sum('stok_total'),
            'stok_tersedia' => $k->barangs->sum('stok_tersedia'),
            'stok_dipinjam' => $k->barangs->sum('stok_total') - $k->barangs->sum('stok_tersedia'),
            'barangs'       => $k->barangs,
        ]);

        return response()->json([
            'data' => $summary,
            'grand_total' => [
                'stok_total'    => $summary->sum('stok_total'),
                'stok_tersedia' => $summary->sum('stok_tersedia'),
                'stok_dipinjam' => $summary->sum('stok_dipinjam'),
            ],
        ]);
    }

    /**
     * GET /api/v1/laporan/stok/pdf
     * Unduh PDF laporan stok barang.
     */
    public function stokPdf(): Response
    {
        $kategoris = Kategori::with([
            'barangs:id,kategori_id,nama_barang,stok_total,stok_tersedia,kondisi,lokasi',
        ])
            ->withCount('barangs')
            ->orderBy('nama')
            ->get();

        $summary = $kategoris->map(fn($k) => [
            'kategori'      => $k->nama,
            'jumlah_barang' => $k->barangs_count,
            'stok_total'    => $k->barangs->sum('stok_total'),
            'stok_tersedia' => $k->barangs->sum('stok_tersedia'),
            'stok_dipinjam' => $k->barangs->sum('stok_total') - $k->barangs->sum('stok_tersedia'),
            'barangs'       => $k->barangs,
        ]);

        $grandTotal = [
            'stok_total'    => $summary->sum('stok_total'),
            'stok_tersedia' => $summary->sum('stok_tersedia'),
            'stok_dipinjam' => $summary->sum('stok_dipinjam'),
        ];

        $pdf = Pdf::loadView('laporan.stok', [
            'kategoris'   => $summary,
            'grand_total' => $grandTotal,
            'tanggal'     => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-' . now()->format('Ymd') . '.pdf');
    }

    // ── Private helper ───────────────────────────────────────────────────

    private function terapkanFilterLaporan($query, Request $request): void
    {
        if ($request->filled('dari')) {
            $query->whereDate('tgl_pinjam', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tgl_pinjam', '<=', $request->sampai);
        }
        if ($request->filled('warga_id')) {
            $query->where('warga_id', $request->warga_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }
}
