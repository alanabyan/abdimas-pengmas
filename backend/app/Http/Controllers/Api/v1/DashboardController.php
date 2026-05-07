<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Notifikasi;
use App\Models\Peminjaman;
use App\Models\Warga;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard/statistik
     * Kartu statistik utama untuk dashboard.
     */
    public function statistik(): JsonResponse
    {
        $totalBarang       = Barang::count();
        $totalKategori     = Kategori::count();
        $totalWarga        = Warga::count();
        $peminjamanAktif   = Peminjaman::whereIn('status', [
            Peminjaman::STATUS_AKTIF,
            Peminjaman::STATUS_MENUNGGU,
        ])->count();
        $peminjamanTerlambat = Peminjaman::where('status', Peminjaman::STATUS_TERLAMBAT)
            ->orWhere(function ($q) {
                $q->whereIn('status', [Peminjaman::STATUS_AKTIF, Peminjaman::STATUS_MENUNGGU])
                    ->where('tgl_rencana_kembali', '<', now()->toDateString());
            })->count();
        $barangRusakHilang = Peminjaman::where('status', Peminjaman::STATUS_RUSAK_HILANG)->count();
        $notifBelumDibaca  = Notifikasi::belumDibaca()->count();

        return response()->json([
            'data' => [
                'total_barang'          => $totalBarang,
                'total_kategori'        => $totalKategori,
                'total_warga'           => $totalWarga,
                'peminjaman_aktif'      => $peminjamanAktif,
                'peminjaman_terlambat'  => $peminjamanTerlambat,
                'barang_rusak_hilang'   => $barangRusakHilang,
                'notif_belum_dibaca'    => $notifBelumDibaca,
            ],
        ]);
    }

    /**
     * GET /api/v1/dashboard/grafik-barang
     * Data untuk bar chart jumlah barang per kategori.
     */
    public function grafikBarang(): JsonResponse
    {
        $data = Kategori::withCount('barangs')
            ->orderByDesc('barangs_count')
            ->get(['id', 'nama', 'ikon'])
            ->map(fn($k) => [
                'label'         => $k->nama,
                'ikon'          => $k->ikon,
                'jumlah_barang' => $k->barangs_count,
            ]);

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/dashboard/grafik-peminjaman
     * Data untuk bar chart tren peminjaman per kategori, 12 bulan terakhir.
     *
     * Response format:
     * [
     *   {
     *     "label": "Elektronik",
     *     "ikon": "laptop",
     *     "data": [
     *       { "bulan": "2024-06", "total": 4 },
     *       ...
     *     ]
     *   },
     *   ...
     * ]
     */
    public function grafikPeminjaman(): JsonResponse
    {
        $mulai = now()->subMonths(11)->startOfMonth();

        // Ambil semua bulan dalam range (12 bulan terakhir)
        $bulanRange = collect();
        for ($i = 0; $i < 12; $i++) {
            $bulanRange->push(now()->subMonths(11 - $i)->startOfMonth()->format('Y-m'));
        }

        // Ambil data peminjaman per kategori per bulan
        // JOIN: peminjamans → barangs → kategoris
        $rows = DB::table('peminjamans')
            ->join('barangs', 'peminjamans.barang_id', '=', 'barangs.id')
            ->join('kategoris', 'barangs.kategori_id', '=', 'kategoris.id')
            ->select(
                'kategoris.id as kategori_id',
                'kategoris.nama as kategori_nama',
                'kategoris.ikon as kategori_ikon',
                DB::raw('YEAR(peminjamans.tgl_pinjam) as tahun'),
                DB::raw('MONTH(peminjamans.tgl_pinjam) as bulan'),
                DB::raw('COUNT(*) as total')
            )
            ->where('peminjamans.tgl_pinjam', '>=', $mulai)
            ->groupBy('kategoris.id', 'kategoris.nama', 'kategoris.ikon', 'tahun', 'bulan')
            ->orderBy('kategoris.nama')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        // Kelompokkan per kategori, isi bulan yang kosong dengan total = 0
        $grouped = $rows->groupBy('kategori_id');

        $data = $grouped->map(function ($items) use ($bulanRange) {
            $first = $items->first();

            // Buat lookup: 'YYYY-MM' => total
            $lookup = $items->keyBy(fn($r) => sprintf('%04d-%02d', $r->tahun, $r->bulan))
                ->map(fn($r) => $r->total);

            // Petakan ke semua 12 bulan, default 0 jika tidak ada
            $dataPerBulan = $bulanRange->map(fn($bln) => [
                'bulan' => $bln,
                'total' => $lookup->get($bln, 0),
            ])->values();

            return [
                'label' => $first->kategori_nama,
                'ikon'  => $first->kategori_ikon,
                'data'  => $dataPerBulan,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
