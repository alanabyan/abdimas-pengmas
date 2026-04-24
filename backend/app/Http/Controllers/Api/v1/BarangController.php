<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    /**
     * GET /api/v1/barang
     * List barang dengan search, filter kategori, filter status.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Barang::with('kategori:id,nama,ikon');

        if ($request->filled('search')) {
            $query->cari($request->search);
        }

        if ($request->filled('kategori_id')) {
            $query->byKategori((int) $request->kategori_id);
        }

        if ($request->filled('tersedia') && $request->boolean('tersedia')) {
            $query->tersedia();
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        $barangs = $query->orderBy('nama_barang')
            ->paginate($request->get('per_page', 15));

        return response()->json($barangs);
    }

    /**
     * POST /api/v1/barang
     * Tambah barang baru. Foto opsional (multipart/form-data).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'kategori_id'  => ['required', 'exists:kategoris,id'],
            'nama_barang'         => ['required', 'string', 'max:255'],
            'deskripsi'    => ['nullable', 'string'],
            'stok_total'   => ['required', 'integer', 'min:1'],
            'kondisi'      => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'lokasi'       => ['nullable', 'string', 'max:255'],
            'foto'         => ['nullable', 'image', 'max:2048'], // max 2MB
        ]);

        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $path    = $request->file('foto')->store('barang', 'public');
            $fotoUrl = url(Storage::url($path));
        }

        $barang = Barang::create([
            'kategori_id'   => $request->kategori_id,
            'nama_barang'          => $request->nama_barang,
            'deskripsi'     => $request->deskripsi,
            'stok_total'    => $request->stok_total,
            'stok_tersedia' => $request->stok_total, // awal: semua tersedia
            'kondisi'       => $request->kondisi,
            'lokasi'        => $request->lokasi,
            'foto_url'      => $fotoUrl,
        ]);

        return response()->json([
            'message' => 'Barang berhasil ditambahkan.',
            'data'    => $barang->load('kategori:id,nama'),
        ], 201);
    }

    /**
     * GET /api/v1/barang/{barang}
     * Detail barang lengkap + riwayat peminjaman terbaru.
     */
    public function show(Barang $barang): JsonResponse
    {
        $barang->load([
            'kategori:id,nama,ikon',
            'peminjamans' => function ($q) {
                $q->with(['warga:id,nama_warga,no_hp', 'marbot:id,nama_marbot'])
                    ->latest()
                    ->limit(10);
            },
        ]);

        $barang->loadCount('peminjamanAktif');

        return response()->json(['data' => $barang]);
    }

    /**
     * PUT /api/v1/barang/{barang}
     * Update data barang. Gunakan POST + _method=PUT dari frontend
     * agar bisa sekaligus upload foto.
     */
    public function update(Request $request, Barang $barang): JsonResponse
    {
        $request->validate([
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'nama_barang'        => ['required', 'string', 'max:255'],
            'deskripsi'   => ['nullable', 'string'],
            'stok_total'  => ['required', 'integer', 'min:1'],
            'kondisi'     => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'lokasi'      => ['nullable', 'string', 'max:255'],
            'foto'        => ['nullable', 'image', 'max:2048'],
        ]);

        // Hitung ulang stok_tersedia jika stok_total berubah
        if ((int) $request->stok_total !== $barang->stok_total) {
            $dipinjam             = $barang->stok_dipinjam;
            $stokTersediaBaru     = max(0, $request->stok_total - $dipinjam);
            $barang->stok_tersedia = $stokTersediaBaru;
        }

        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($barang->foto_url) {
                $oldPath = str_replace('/storage/', 'public/', $barang->foto_url);
                Storage::delete($oldPath);
            }
            $path             = $request->file('foto')->store('barang', 'public');
            $barang->foto_url = url(Storage::url($path));
        }

        $barang->fill($request->only('kategori_id', 'nama_barang', 'deskripsi', 'stok_total', 'kondisi', 'lokasi'));
        $barang->save();

        return response()->json([
            'message' => 'Data barang berhasil diperbarui.',
            'data'    => $barang->fresh()->load('kategori:id,nama'),
        ]);
    }

    /**
     * DELETE /api/v1/barang/{barang}
     * Ditolak jika barang sedang dalam peminjaman aktif.
     */
    public function destroy(Barang $barang): JsonResponse
    {
        if ($barang->peminjamanAktif()->exists()) {
            return response()->json([
                'message' => 'Barang tidak dapat dihapus karena sedang dipinjam.',
            ], 422);
        }

        // Hapus foto dari storage
        if ($barang->foto_url) {
            $path = str_replace('/storage/', 'public/', $barang->foto_url);
            Storage::delete($path);
        }

        $barang->delete();

        return response()->json(['message' => 'Barang berhasil dihapus.']);
    }

    /**
     * GET /api/v1/barang/{barang}/ketersediaan
     * Cek apakah barang tersedia untuk dipinjam (untuk validasi form).
     */
    public function ketersediaan(Barang $barang): JsonResponse
    {
        return response()->json([
            'data' => [
                'barang_id'     => $barang->id,
                'nama_barang'          => $barang->nama_barang,
                'stok_total'    => $barang->stok_total,
                'stok_tersedia' => $barang->stok_tersedia,
                'stok_dipinjam' => $barang->stok_dipinjam,
                'tersedia'      => $barang->tersedia,
            ],
        ]);
    }

    /**
     * POST /api/v1/barang/{barang}/foto
     * Upload atau ganti foto barang saja.
     */
    public function uploadFoto(Request $request, Barang $barang): JsonResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'max:2048'],
        ]);

        if ($barang->foto_url) {
            $oldPath = str_replace('/storage/', 'public/', $barang->foto_url);
            Storage::delete($oldPath);
        }

        $path = $request->file('foto')->store('barang', 'public');
        $barang->update(['foto_url' => url(Storage::url($path))]);

        return response()->json([
            'message'  => 'Foto barang berhasil diperbarui.',
            'foto_url' => $barang->foto_url,
        ]);
    }

    /**
     * DELETE /api/v1/barang/{barang}/foto
     * Hapus foto barang.
     */
    public function hapusFoto(Barang $barang): JsonResponse
    {
        if (! $barang->foto_url) {
            return response()->json(['message' => 'Barang tidak memiliki foto.'], 404);
        }

        $path = str_replace('/storage/', 'public/', $barang->foto_url);
        Storage::delete($path);

        $barang->update(['foto_url' => null]);

        return response()->json(['message' => 'Foto barang berhasil dihapus.']);
    }
}
