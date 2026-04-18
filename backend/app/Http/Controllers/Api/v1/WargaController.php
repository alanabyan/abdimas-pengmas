<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WargaController extends Controller
{
    /**
     * GET /api/v1/warga
     * Daftar semua warga dengan search dan pagination.
     */
    // WargaController.php — method index
    public function index(Request $request): JsonResponse
    {
        $query = Warga::query()->withCount([
            'peminjamans as peminjaman_aktif_count' => function ($q) {
                $q->whereIn('status', ['Aktif', 'Menunggu', 'Terlambat']);
            }
        ]);

        if ($request->filled('search')) {
            $query->cari($request->search);
        }

        if ($request->filled('rt_rw')) {
            $query->where('rt_rw', $request->rt_rw);
        }

        $wargas = $query->orderBy('nama_warga')
            ->paginate($request->get('per_page', 10));

        return response()->json($wargas);
    }

    /**
     * GET /api/v1/warga/search?q=...
     * Autocomplete untuk form peminjaman baru.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2']]);

        $wargas = Warga::cari($request->q)
            ->orderBy('nama_warga')
            ->limit(10)
            ->get(['id', 'nama_warga', 'no_hp', 'rt_rw']);

        return response()->json(['data' => $wargas]);
    }

    /**
     * POST /api/v1/warga
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama_warga'   => ['required', 'string', 'max:255'],
            'no_hp'  => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'rt_rw'  => ['nullable', 'string', 'max:20'],
        ]);

        $warga = Warga::create($request->only('nama_warga', 'no_hp', 'alamat', 'rt_rw'));

        return response()->json([
            'message' => 'Data warga berhasil ditambahkan.',
            'data'    => $warga,
        ], 201);
    }

    /**
     * GET /api/v1/warga/{warga}
     * Detail warga + riwayat peminjaman.
     */
    public function show(Warga $warga): JsonResponse
    {
        $warga->load([
            'peminjamans' => function ($q) {
                $q->with(['barang:id,nama_barang,foto_url'])
                    ->latest()
                    ->limit(20);
            },
        ]);

        $warga->loadCount([
            'peminjamans as peminjaman_aktif_count' => function ($q) {
                $q->whereIn('status', ['Aktif', 'Menunggu', 'Terlambat']);
            }
        ]);

        return response()->json(['data' => $warga]);
    }

    /**
     * PUT /api/v1/warga/{warga}
     */
    public function update(Request $request, Warga $warga): JsonResponse
    {
        $request->validate([
            'nama_warga'   => ['required', 'string', 'max:255'],
            'no_hp'  => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'rt_rw'  => ['nullable', 'string', 'max:20'],
        ]);

        $warga->update($request->only('nama_warga', 'no_hp', 'alamat', 'rt_rw'));

        return response()->json([
            'message' => 'Data warga berhasil diperbarui.',
            'data'    => $warga->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/warga/{warga}
     * Hapus data warga. Ditolak jika masih punya peminjaman aktif.
     */
    public function destroy(Warga $warga): JsonResponse
    {
        if ($warga->peminjamanAktif()->exists()) {
            return response()->json([
                'message' => 'Warga ini masih memiliki peminjaman aktif dan tidak dapat dihapus.',
            ], 422);
        }

        $warga->delete();

        return response()->json(['message' => 'Data warga berhasil dihapus.']);
    }

    /**
     * GET /api/v1/warga/{warga}/riwayat
     * Riwayat lengkap peminjaman warga ini.
     */
    public function riwayatPeminjaman(Warga $warga): JsonResponse
    {
        $riwayat = $warga->peminjamans()
            ->with(['barang:id,nama_barang,foto_url', 'marbot:id,nama_marbot'])
            ->latest()
            ->paginate(10);

        return response()->json($riwayat);
    }
}
