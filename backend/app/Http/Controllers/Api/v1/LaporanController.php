<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * GET /api/v1/laporan/peminjaman
     * Filter: dari, sampai, status
     */
    public function peminjaman(Request $request): JsonResponse
    {
        $query = Peminjaman::with([
            'barang:id,nama_barang,foto_url,stok_total,stok_tersedia',
            'warga:id,nama_warga,no_hp,rt_rw',
            'marbot:id,nama_marbot',
        ]);

        if ($request->filled('dari')) {
            $query->whereDate('tgl_pinjam', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tgl_pinjam', '<=', $request->sampai);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest('tgl_pinjam')->get();

        return response()->json([
            'data'  => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * GET /api/v1/laporan/kerusakan
     * Filter: dari, sampai, status (default: Rusak/Hilang, Batal, Terlambat)
     */
    public function kerusakan(Request $request): JsonResponse
    {
        $query = Peminjaman::with([
            'barang:id,nama_barang,foto_url,stok_total,stok_tersedia',
            'warga:id,nama_warga,no_hp,rt_rw',
            'marbot:id,nama_marbot',
        ]);

        // Default status bermasalah
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['Rusak/Hilang', 'Batal', 'Terlambat']);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tgl_pinjam', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tgl_pinjam', '<=', $request->sampai);
        }

        $data = $query->latest('tgl_pinjam')->get();

        return response()->json([
            'data'  => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * GET /api/v1/laporan/stok
     * Semua barang + stok real-time
     */
    public function stok(): JsonResponse
    {
        $barangs = Barang::with('kategori:id,nama')->get();

        $grouped = $barangs->groupBy('kategori.nama')->map(function ($items, $namaKategori) {
            return [
                'kategori'        => $namaKategori,
                'jumlah_barang'   => $items->count(),
                'stok_total'      => $items->sum('stok_total'),
                'stok_tersedia'   => $items->sum('stok_tersedia'),
                'stok_dipinjam'   => $items->sum('stok_total') - $items->sum('stok_tersedia'),
                'barangs'         => $items->values(),
            ];
        })->values();

        $grandTotal = [
            'stok_total'      => $barangs->sum('stok_total'),
            'stok_tersedia'   => $barangs->sum('stok_tersedia'),
            'stok_dipinjam'   => $barangs->sum('stok_total') - $barangs->sum('stok_tersedia'),
        ];

        return response()->json([
            'data' => $grouped,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * GET /api/v1/laporan/peminjaman/pdf
     * Ekspor laporan peminjaman ke PDF
     */
    public function peminjamanPdf(Request $request)
    {
        $query = Peminjaman::with([
            'barang:id,nama_barang,foto_url',
            'warga:id,nama_warga,rt_rw',
            'marbot:id,nama_marbot',
        ]);

        if ($request->filled('dari'))   $query->whereDate('tgl_pinjam', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tgl_pinjam', '<=', $request->sampai);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data  = $query->latest('tgl_pinjam')->get();
        $total = $data->count();
        $dari  = $request->dari  ? \Carbon\Carbon::parse($request->dari)->isoFormat('D MMMM YYYY')  : 'Semua';
        $sampai = $request->sampai ? \Carbon\Carbon::parse($request->sampai)->isoFormat('D MMMM YYYY') : 'Semua';

        $pdf = Pdf::loadView('laporan.peminjaman', compact('data', 'total', 'dari', 'sampai'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-peminjaman.pdf');
    }

    /**
     * GET /api/v1/laporan/kerusakan/pdf
     * Ekspor laporan kerusakan ke PDF
     */
    public function kerusakanPdf(Request $request)
    {
        $query = Peminjaman::with([
            'barang:id,nama_barang',
            'warga:id,nama_warga,no_hp',
            'marbot:id,nama_marbot',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['Rusak/Hilang', 'Batal', 'Terlambat']);
        }

        if ($request->filled('dari'))   $query->whereDate('tgl_pinjam', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tgl_pinjam', '<=', $request->sampai);

        $data  = $query->latest('tgl_pinjam')->get();
        $total = $data->count();
        $dari  = $request->dari  ? \Carbon\Carbon::parse($request->dari)->isoFormat('D MMMM YYYY')  : 'Semua';
        $sampai = $request->sampai ? \Carbon\Carbon::parse($request->sampai)->isoFormat('D MMMM YYYY') : 'Semua';

        $pdf = Pdf::loadView('laporan.kerusakan', compact('data', 'total', 'dari', 'sampai'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kerusakan.pdf');
    }
}
