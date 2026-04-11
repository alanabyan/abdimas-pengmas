<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marbot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Login marbot, mengembalikan Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $marbot = Marbot::where('email', $request->email)->first();

        if (! $marbot || ! Hash::check($request->password, $marbot->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $marbot->aktif) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi Marbot lain.',
            ], 403);
        }

        // Hapus token lama (opsional — single session per device)
        $marbot->tokens()->delete();

        $token = $marbot->createToken('simba-marbot')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'    => $marbot->id,
                'nama_marbot'  => $marbot->nama_marbot,
                'email' => $marbot->email,
                'aktif' => $marbot->aktif,
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $marbot = $request->user();

        return response()->json([
            'data' => [
                'id'         => $marbot->id,
                'nama'       => $marbot->nama,
                'email'      => $marbot->email,
                'aktif'      => $marbot->aktif,
                'created_at' => $marbot->created_at,
            ],
        ]);
    }

    /**
     * PUT /api/v1/auth/me
     * Update profil marbot yang sedang login.
     */
    public function updateMe(Request $request): JsonResponse
    {
        $marbot = $request->user();

        $request->validate([
            'nama'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:marbots,email,' . $marbot->id],
        ]);

        $marbot->update($request->only('nama', 'email'));

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $marbot->fresh(),
        ]);
    }

    /**
     * PUT /api/v1/auth/me/password
     * Ganti password marbot yang sedang login.
     */
    public function ubahPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $marbot = $request->user();

        if (! Hash::check($request->password_lama, $marbot->password)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Password lama tidak sesuai.'],
            ]);
        }

        $marbot->update(['password' => Hash::make($request->password_baru)]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}
