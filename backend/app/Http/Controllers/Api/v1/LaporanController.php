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
     * DATA JSON: Laporan Peminjaman (Web)
     */
    public function peminjaman(Request $request): JsonResponse
    {
        // PENTING: muat relasi 'barang.kategori' biar kategori muncul
        $query = Peminjaman::with([
            'barang' => function($q) {
                $q->select('id', 'nama_barang', 'foto_url', 'stok_total', 'stok_tersedia', 'kategori_id')
                  ->with('kategori:id,nama'); 
            },
            'warga:id,nama_warga,no_hp,rt_rw',
            'marbot:id,nama_marbot',
        ]);

        if ($request->filled('dari'))   $query->whereDate('tgl_pinjam', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tgl_pinjam', '<=', $request->sampai);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->latest('tgl_pinjam')->get();
        return response()->json(['data' => $data, 'total' => $data->count()]);
    }

    /**
     * DATA JSON: Laporan Kerusakan & Kendala (Web)
     */
    public function kerusakan(Request $request): JsonResponse
    {
        $query = Peminjaman::with([
            'barang' => function($q) {
                $q->select('id', 'nama_barang', 'foto_url', 'stok_total', 'stok_tersedia', 'kategori_id')
                  ->with('kategori:id,nama');
            },
            'warga:id,nama_warga,no_hp,rt_rw',
            'marbot:id,nama_marbot',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where(function($q) {
                $q->where('status', 'like', '%Rusak%')
                  ->orWhere('status', 'like', '%Hilang%')
                  ->orWhere('status', 'Batal')
                  ->orWhere('status', 'Terlambat');
            });
        }

        if ($request->filled('dari'))   $query->whereDate('tgl_pinjam', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tgl_pinjam', '<=', $request->sampai);

        $data = $query->latest('tgl_pinjam')->get();
        return response()->json(['data' => $data, 'total' => $data->count()]);
    }

    /**
     * DATA JSON: Laporan Stok per Kategori (Web)
     */
    public function stok(): JsonResponse
    {
        $barangs = Barang::with('kategori:id,nama')->get();
        
        $grouped = $barangs->groupBy(function ($item) {
            return $item->kategori ? $item->kategori->nama : 'Tanpa Kategori';
        })->map(function ($items, $namaKategori) {
            return [
                'kategori' => $namaKategori,
                'jumlah_barang' => $items->count(),
                'stok_total' => $items->sum('stok_total'),
                'stok_tersedia' => $items->sum('stok_tersedia'),
                'stok_dipinjam' => $items->sum('stok_total') - $items->sum('stok_tersedia'),
                'barangs' => $items->values(),
            ];
        })->values();

        return response()->json([
            'data' => $grouped, 
            'grand_total' => ['stok_total' => $barangs->sum('stok_total')]
        ]);
    }

    /**
     * CETAK PDF: Laporan Peminjaman (Landscape)
     */
    public function peminjamanPdf(Request $request)
    {
        // Pastikan relasi barang.kategori juga masuk ke PDF
        $query = Peminjaman::with(['barang.kategori', 'warga', 'marbot']);

        if ($request->filled('dari'))   $query->whereDate('tgl_pinjam', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tgl_pinjam', '<=', $request->sampai);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->latest('tgl_pinjam')->get();
        $total = $data->count();
        $dari = $request->dari ? \Carbon\Carbon::parse($request->dari)->isoFormat('D MMMM YYYY') : 'Semua';
        $sampai = $request->sampai ? \Carbon\Carbon::parse($request->sampai)->isoFormat('D MMMM YYYY') : 'Semua';

        $pdf = Pdf::loadView('laporan.peminjaman', compact('data', 'total', 'dari', 'sampai'))
                  ->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan-peminjaman-' . date('Ymd') . '.pdf');
    }

    /**
     * CETAK PDF: Laporan Kerusakan (Landscape)
     */
    public function kerusakanPdf(Request $request)
    {
        $query = Peminjaman::with(['barang.kategori', 'warga', 'marbot']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where(function($q) {
                $q->where('status', 'like', '%Rusak%')
                  ->orWhere('status', 'like', '%Hilang%')
                  ->orWhere('status', 'Batal')
                  ->orWhere('status', 'Terlambat');
            });
        }

        if ($request->filled('dari'))   $query->whereDate('tgl_pinjam', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('tgl_pinjam', '<=', $request->sampai);

        $data = $query->latest('tgl_pinjam')->get();
        $total = $data->count();
        $dari = $request->dari ? \Carbon\Carbon::parse($request->dari)->isoFormat('D MMMM YYYY') : 'Semua';
        $sampai = $request->sampai ? \Carbon\Carbon::parse($request->sampai)->isoFormat('D MMMM YYYY') : 'Semua';

        $pdf = Pdf::loadView('laporan.kerusakan', compact('data', 'total', 'dari', 'sampai'))
                  ->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan-kerusakan-' . date('Ymd') . '.pdf');
    }

    /**
     * DATA UNTUK GRAFIK: Peminjaman 7 Hari Terakhir
     */
    public function trenPeminjaman(): JsonResponse
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->isoFormat('dddd'); // Senin, Selasa, dst.
            
            $count = Peminjaman::whereDate('tgl_pinjam', $date->format('Y-m-d'))->count();
            $data[] = $count;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Peminjaman',
                    'data' => $data,
                    'backgroundColor' => '#10b981',
                    'borderRadius' => 5
                ]
            ]
        ]);
    }
}