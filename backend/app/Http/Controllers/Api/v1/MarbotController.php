<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Marbot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * MarbotController
 *
 * Mengelola akun Marbot (penjaga masjid):
 *  - index           : Daftar semua marbot (dengan search & pagination)
 *  - store           : Tambah akun marbot baru
 *  - show            : Detail satu marbot
 *  - update          : Edit data marbot (oleh admin/marbot lain)
 *  - destroy         : Nonaktifkan akun (soft-delete via kolom aktif)
 *  - updateProfile   : Marbot edit profil sendiri
 *  - resetPassword   : Reset password marbot oleh admin
 */
class MarbotController extends Controller
{
    // =========================================================================
    // INDEX – GET /api/v1/marbot
    // Daftar semua akun marbot dengan pencarian & pagination.
    // =========================================================================
    public function index(Request $request): JsonResponse
    {
        $query = Marbot::orderBy('nama_marbot');

        // Filter pencarian opsional (nama atau email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_marbot', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter status aktif opsional: ?aktif=1 atau ?aktif=0
        if ($request->has('aktif')) {
            $query->where('aktif', (bool) $request->aktif);
        }

        $marbots = $query
            ->select(['id', 'nama_marbot', 'email', 'aktif', 'created_at'])
            ->paginate(15);

        return response()->json($marbots);
    }

    // =========================================================================
    // STORE – POST /api/v1/marbot
    // Buat akun marbot baru.
    // =========================================================================
    public function store(Request $request): JsonResponse
    {
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
    // Detail satu marbot beserta jumlah peminjaman.
    // =========================================================================
    public function show(Marbot $marbot): JsonResponse
    {
        // Sertakan jumlah peminjaman agar berguna di halaman detail
        $marbot->loadCount('peminjamans');

        return response()->json([
            'success' => true,
            'data'    => $marbot,
        ]);
    }

    // =========================================================================
    // UPDATE – PUT /api/v1/marbot/{marbot}
    // Edit data marbot (nama, email, status aktif) oleh admin.
    // =========================================================================
    public function update(Request $request, Marbot $marbot): JsonResponse
    {
        $request->validate([
            'nama_marbot' => ['required', 'string', 'max:150'],
            'email'       => ['required', 'email', Rule::unique('marbots')->ignore($marbot->id)],
            'aktif'       => ['boolean'],
        ]);

        $marbot->update($request->only(['nama_marbot', 'email', 'aktif']));

        return response()->json([
            'message' => 'Data Marbot berhasil diperbarui.',
            'data'    => $marbot->fresh()->only(['id', 'nama_marbot', 'email', 'aktif', 'created_at']),
        ]);
    }

    // =========================================================================
    // DESTROY – DELETE /api/v1/marbot/{marbot}
    // Nonaktifkan akun marbot (soft-delete via kolom aktif).
    // Marbot tidak bisa menonaktifkan akunnya sendiri.
    // =========================================================================
    public function destroy(Request $request, Marbot $marbot): JsonResponse
    {
        // Cegah marbot menghapus/menonaktifkan diri sendiri
        if ($request->user()->id === $marbot->id) {
            return response()->json([
                'message' => 'Tidak dapat menonaktifkan akun sendiri.',
            ], 422);
        }

        // Nonaktifkan, bukan hapus, agar riwayat peminjaman tetap terjaga
        $marbot->update(['aktif' => false]);

        // Cabut semua token aktif agar marbot tersebut otomatis logout
        $marbot->tokens()->delete();

        return response()->json([
            'message' => "Akun Marbot {$marbot->nama_marbot} berhasil dinonaktifkan.",
        ]);
    }

    // =========================================================================
    // UPDATE PROFILE – PUT /api/v1/marbot/profile
    // Marbot edit profil sendiri (nama, email, password opsional).
    // =========================================================================
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Marbot $marbot */
        $marbot = $request->user();

        if (! $marbot) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        $request->validate([
            'nama_marbot'      => ['required', 'string', 'max:150'],
            'email'            => ['required', 'email', Rule::unique('marbots')->ignore($marbot->id)],
            'password_lama'    => ['nullable', 'string'],         // Wajib jika ingin ganti password
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Jika ingin ganti password, verifikasi password lama dahulu
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
            'data'    => $marbot->only(['id', 'nama_marbot', 'email', 'aktif']),
        ]);
    }

    // =========================================================================
    // RESET PASSWORD – POST /api/v1/marbot/{marbot}/reset-password
    // Reset password marbot oleh admin (tanpa perlu password lama).
    // =========================================================================
    public function resetPassword(Request $request, Marbot $marbot): JsonResponse
    {
        // Admin tidak boleh reset password diri sendiri lewat endpoint ini;
        // gunakan updateProfile untuk itu.
        if ($request->user()->id === $marbot->id) {
            return response()->json([
                'message' => 'Gunakan fitur "Ubah Password" di halaman profil untuk mengubah password Anda sendiri.',
            ], 422);
        }

        $request->validate([
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $marbot->update(['password' => Hash::make($request->password_baru)]);

        // Cabut semua token aktif agar marbot re-login dengan password baru
        $marbot->tokens()->delete();

        return response()->json([
            'message' => "Password Marbot {$marbot->nama_marbot} berhasil direset.",
        ]);
    }
}
