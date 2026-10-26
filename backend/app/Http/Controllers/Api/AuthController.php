<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bendahara;
use App\Models\Marbot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
    * Login marbot atau bendahara, mengembalikan Sanctum token dan role.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $marbot = Marbot::where('email', $request->email)->first();
        $bendahara = $marbot ? null : Bendahara::where('email', $request->email)->first();
        $account = $marbot ?? $bendahara;

        if (! $account || ! Hash::check($request->password, $account->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $account->aktif) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ], 403);
        }

        $role = $marbot ? 'marbot' : 'bendahara';
        $account->tokens()->delete();

        $token = $account->createToken('simba-' . $role)->plainTextToken;
        $user = [
            'id' => $account->id,
            'role' => $role,
            'nama' => $marbot ? $marbot->nama_marbot : $bendahara->nama_bendahara,
            'email' => $account->email,
            'aktif' => $account->aktif,
        ];

        if ($marbot) {
            $user['nama_marbot'] = $marbot->nama_marbot;
            $user['is_super_admin'] = $marbot->is_super_admin;
        } else {
            $user['nama_bendahara'] = $bendahara->nama_bendahara;
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => $user,
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
        $user = $request->user();
        $isBendahara = $user instanceof Bendahara;

        return response()->json([
            'data' => [
                'id' => $user->id,
                'role' => $isBendahara ? 'bendahara' : 'marbot',
                'nama' => $isBendahara ? $user->nama_bendahara : $user->nama_marbot,
                'nama_marbot' => $isBendahara ? null : $user->nama_marbot,
                'nama_bendahara' => $isBendahara ? $user->nama_bendahara : null,
                'email' => $user->email,
                'aktif' => $user->aktif,
                'is_super_admin' => $isBendahara ? false : $user->is_super_admin,
                'created_at' => $user->created_at,
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
