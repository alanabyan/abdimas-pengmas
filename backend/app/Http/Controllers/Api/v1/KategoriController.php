<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    /**
     * GET /api/v1/kategori
     */
    public function index(): JsonResponse
    {
        $kategoris = Kategori::withCount('barangs')
            ->orderBy('nama')
            ->get();

        return response()->json(['data' => $kategoris]);
    }

    /**
     * POST /api/v1/kategori
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:kategoris,nama'],
            'deskripsi' => ['nullable', 'string'],
            'ikon'      => ['nullable', 'string', 'max:50'],
        ]);

        $kategori = Kategori::create($request->only('nama', 'deskripsi', 'ikon'));

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => $kategori,
        ], 201);
    }

    /**
     * GET /api/v1/kategori/{kategori}
     */
    public function show(Kategori $kategori): JsonResponse
    {
        $kategori->loadCount('barangs');
        $kategori->load('barangs:id,nama,stok_tersedia,kondisi,kategori_id');

        return response()->json(['data' => $kategori]);
    }

    /**
     * PUT /api/v1/kategori/{kategori}
     */
    public function update(Request $request, Kategori $kategori): JsonResponse
    {
        $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:kategoris,nama,' . $kategori->id],
            'deskripsi' => ['nullable', 'string'],
            'ikon'      => ['nullable', 'string', 'max:50'],
        ]);

        $kategori->update($request->only('nama', 'deskripsi', 'ikon'));

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => $kategori->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/kategori/{kategori}
     * Ditolak jika kategori masih memiliki barang.
     */
    public function destroy(Kategori $kategori): JsonResponse
    {
        if ($kategori->barangs()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki barang terdaftar.',
            ], 422);
        }

        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
