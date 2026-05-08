<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Marbot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MarbotController extends Controller
{
    // =========================================================================
    // INDEX – GET /api/v1/marbot
    // =========================================================================
    public function index(Request $request): JsonResponse
    {
        $query = Marbot::orderBy('nama_marbot');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_marbot', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('aktif')) {
            $query->where('aktif', (bool) $request->aktif);
        }

        $marbots = $query
            ->select(['id', 'nama_marbot', 'email', 'aktif', 'is_super_admin', 'created_at']) // ← tambah is_super_admin
            ->paginate(15);

        return response()->json($marbots);
    }

    // =========================================================================
    // STORE – POST /api/v1/marbot
    // =========================================================================
    public function store(Request $request): JsonResponse
    {
        // Hanya super admin yang boleh membuat akun marbot baru
        if (! $request->user()->is_super_admin) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'nama_marbot' => ['required', 'string', 'max:150'],
            'email'       => ['required', 'email', 'unique:marbots,email'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $marbot = Marbot::create([
            'nama_marbot' => $request->nama_marbot,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'aktif'       => true,
        ]);

        return response()->json([
            'message' => 'Akun Marbot berhasil dibuat.',
            'data'    => $marbot->only(['id', 'nama_marbot', 'email', 'aktif', 'created_at']),
        ], 201);
    }

    // =========================================================================
    // SHOW – GET /api/v1/marbot/{marbot}
    // =========================================================================
    public function show(Marbot $marbot): JsonResponse
    {
        $marbot->loadCount('peminjamans');

        return response()->json([
            'success' => true,
            'data'    => $marbot,
        ]);
    }

    // =========================================================================
    // UPDATE – PUT /api/v1/marbot/{marbot}
    // =========================================================================
    public function update(Request $request, Marbot $marbot): JsonResponse
    {
        // Hanya super admin yang boleh edit marbot lain
        if (! $request->user()->is_super_admin && $request->user()->id !== $marbot->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'nama_marbot' => ['required', 'string', 'max:150'],
            'email'       => ['required', 'email', Rule::unique('marbots')->ignore($marbot->id)],
            'aktif'       => ['boolean'],
        ]);

        // Non-super admin tidak boleh mengubah status aktif
        $data = $request->only(['nama_marbot', 'email']);
        if ($request->user()->is_super_admin) {
            $data['aktif'] = $request->input('aktif', $marbot->aktif);
        }

        $marbot->update($data);

        return response()->json([
            'message' => 'Data Marbot berhasil diperbarui.',
            'data'    => $marbot->fresh()->only(['id', 'nama_marbot', 'email', 'aktif', 'created_at']),
        ]);
    }

    // =========================================================================
    // DESTROY – DELETE /api/v1/marbot/{marbot}
    // Hanya super admin yang boleh menonaktifkan marbot lain.
    // =========================================================================
    public function destroy(Request $request, Marbot $marbot): JsonResponse
    {
        // Hanya super admin yang boleh nonaktifkan
        if (! $request->user()->is_super_admin) {
            return response()->json(['message' => 'Akses ditolak. Hanya super admin yang dapat menonaktifkan akun.'], 403);
        }

        // Super admin tidak bisa menonaktifkan dirinya sendiri
        if ($request->user()->id === $marbot->id) {
            return response()->json(['message' => 'Tidak dapat menonaktifkan akun sendiri.'], 422);
        }

        $marbot->update(['aktif' => false]);
        $marbot->tokens()->delete();

        return response()->json([
            'message' => "Akun Marbot {$marbot->nama_marbot} berhasil dinonaktifkan.",
        ]);
    }

    // =========================================================================
    // UPDATE PROFILE – PUT /api/v1/marbot/profile
    // =========================================================================
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Marbot $marbot */
        $marbot = $request->user();

        if (! $marbot) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        $request->validate([
            'nama_marbot'   => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', Rule::unique('marbots')->ignore($marbot->id)],
            'password_lama' => ['nullable', 'string'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->filled('password')) {
            if (! $request->filled('password_lama') || ! Hash::check($request->password_lama, $marbot->password)) {
                return response()->json([
                    'message' => 'Password lama tidak sesuai.',
                    'errors'  => ['password_lama' => ['Password lama tidak sesuai.']],
                ], 422);
            }
            $marbot->password = Hash::make($request->password);
        }

        $marbot->nama_marbot = $request->nama_marbot;
        $marbot->email       = $request->email;
        $marbot->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil Anda berhasil diperbarui.',
            'data'    => $marbot->only(['id', 'nama_marbot', 'email', 'aktif', 'is_super_admin']),
        ]);
    }

    // =========================================================================
    // RESET PASSWORD – POST /api/v1/marbot/{marbot}/reset-password
    // Hanya super admin yang boleh reset password marbot lain.
    // =========================================================================
    public function resetPassword(Request $request, Marbot $marbot): JsonResponse
    {
        // Hanya super admin yang boleh reset password orang lain
        if (! $request->user()->is_super_admin) {
            return response()->json(['message' => 'Akses ditolak. Hanya super admin yang dapat mereset password.'], 403);
        }

        if ($request->user()->id === $marbot->id) {
            return response()->json([
                'message' => 'Gunakan fitur "Ubah Password" di halaman profil untuk mengubah password Anda sendiri.',
            ], 422);
        }

        $request->validate([
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $marbot->update(['password' => Hash::make($request->password_baru)]);
        $marbot->tokens()->delete();

        return response()->json([
            'message' => "Password Marbot {$marbot->nama_marbot} berhasil direset.",
        ]);
    }
}
