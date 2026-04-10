<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marbot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarbotController extends Controller
{
    /**
     * GET /api/v1/marbot
     * List semua akun Marbot
     */
    public function index(Request $request): JsonResponse
    {
        $query = Marbot::orderBy('nama');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $marbots = $query->select(['id', 'nama', 'email', 'no_hp', 'is_aktif', 'created_at'])
            ->paginate(15);

        return response()->json($marbots);
    }

    /**
     * POST /api/v1/marbot
     * Buat akun Marbot baru
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama'     => 'required|string|max:150',
            'email'    => 'required|email|unique:marbots,email',
            'no_hp'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $marbot = Marbot::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'no_hp'    => $request->no_hp,
            'password' => Hash::make($request->password),
            'is_aktif' => true,
        ]);

        return response()->json([
            'message' => 'Akun Marbot berhasil dibuat.',
            'data'    => $marbot->only(['id', 'nama', 'email', 'no_hp', 'is_aktif', 'created_at']),
        ], 201);
    }

    /**
     * GET /api/v1/marbot/{id}
     * Detail akun Marbot
     */
    public function show(Marbot $marbot): JsonResponse
    {
        return response()->json([
            'data' => $marbot->only(['id', 'nama', 'email', 'no_hp', 'is_aktif', 'created_at']),
        ]);
    }

    /**
     * PUT /api/v1/marbot/{id}
     * Edit data akun Marbot (nama, email, no_hp, status aktif)
     */
    public function update(Request $request, Marbot $marbot): JsonResponse
    {
        $request->validate([
            'nama'     => 'required|string|max:150',
            'email'    => 'required|email|unique:marbots,email,' . $marbot->id,
            'no_hp'    => 'nullable|string|max:20',
            'is_aktif' => 'nullable|boolean',
        ]);

        // Cegah Marbot menonaktifkan dirinya sendiri
        if ($request->user()->id === $marbot->id && $request->is_aktif === false) {
            return response()->json([
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ], 422);
        }

        $marbot->update($request->only(['nama', 'email', 'no_hp', 'is_aktif']));

        return response()->json([
            'message' => 'Data Marbot berhasil diperbarui.',
            'data'    => $marbot->fresh()->only(['id', 'nama', 'email', 'no_hp', 'is_aktif']),
        ]);
    }

    /**
     * DELETE /api/v1/marbot/{id}
     * Hapus / nonaktifkan akun Marbot
     */
    public function destroy(Request $request, Marbot $marbot): JsonResponse
    {
        // Cegah menghapus diri sendiri
        if ($request->user()->id === $marbot->id) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], 422);
        }

        $jumlahAktif = Marbot::where('is_aktif', true)
            ->where('id', '!=', $marbot->id)
            ->count();

        if ($jumlahAktif === 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus Marbot terakhir yang aktif.',
            ], 422);
        }

        $marbot->update(['is_aktif' => false]);
        $marbot->tokens()->delete();

        return response()->json(['message' => 'Akun Marbot berhasil dinonaktifkan.']);
    }

    /**
     * POST /api/v1/marbot/{id}/reset-password
     * Reset password Marbot oleh Marbot lain
     */
    public function resetPassword(Request $request, Marbot $marbot): JsonResponse
    {
        $request->validate([
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        $marbot->update([
            'password' => Hash::make($request->password_baru),
        ]);

        // Paksa logout semua sesi Marbot yang bersangkutan
        $marbot->tokens()->delete();

        return response()->json([
            'message' => "Password Marbot {$marbot->nama} berhasil direset. Semua sesi aktif telah diakhiri.",
        ]);
    }
}
