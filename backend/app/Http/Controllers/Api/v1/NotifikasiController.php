<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotifikasiController extends Controller
{
    /**
     * GET /api/v1/notifikasi
     * Daftar notifikasi, terbaru di atas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notifikasi::with([
            'peminjaman.warga:id,nama',
            'peminjaman.barang:id,nama',
        ])->latest();

        if ($request->boolean('belum_dibaca')) {
            $query->belumDibaca();
        }

        $notifikasis = $query->paginate($request->get('per_page', 20));

        return response()->json($notifikasis);
    }

    /**
     * PATCH /api/v1/notifikasi/{id}/baca
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function tandaiBaca(Notifikasi $notifikasi): JsonResponse
    {
        $notifikasi->update(['dibaca' => true]);

        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }

    /**
     * DELETE /api/v1/notifikasi/{id}
     */
    public function destroy(Notifikasi $notifikasi): JsonResponse
    {
        $notifikasi->delete();

        return response()->json(['message' => 'Notifikasi berhasil dihapus.']);
    }
}
