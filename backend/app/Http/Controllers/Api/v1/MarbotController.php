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
    /**
     * UPDATE PROFIL SENDIRI (Halaman Pengaturan)
     * Sesuai Dokumen v2.0 Bagian 4.10 [cite: 199, 202]
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // Ambil ID 1 untuk demo, atau $request->user()->id jika sudah pake Auth
        $marbot = Marbot::find(1); 

        if (!$marbot) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        $request->validate([
            'nama_marbot' => 'required|string|max:150',
            'email'       => ['required', 'email', Rule::unique('marbots')->ignore($marbot->id)],
            'password'    => 'nullable|string|min:8', // Hilangkan 'confirmed' biar simple di satu input
        ]);

        $marbot->nama_marbot = $request->nama_marbot;
        $marbot->email = $request->email;

        if ($request->filled('password')) {
            $marbot->password = Hash::make($request->password);
        }

        $marbot->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil Anda berhasil diperbarui.',
            'data'    => $marbot->only(['id', 'nama_marbot', 'email', 'aktif'])
        ]);
    }

    /**
     * GET DETAIL MARBOT (Pake ID manual biar gak Error 500)
     */
    public function show($id): JsonResponse
    {
        $marbot = Marbot::find($id);

        if (!$marbot) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $marbot->only(['id', 'nama_marbot', 'email', 'aktif', 'created_at']),
        ]);
    }

    /**
     * SISANYA (index, store, update admin, dll) SUDAH OKE
     */
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
        return response()->json($query->select(['id', 'nama_marbot', 'email', 'aktif', 'created_at'])->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama_marbot' => 'required|string|max:150',
            'email'       => 'required|email|unique:marbots,email',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        $marbot = Marbot::create([
            'nama_marbot' => $request->nama_marbot,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'aktif'       => true,
        ]);

        return response()->json(['message' => 'Akun Marbot berhasil dibuat.', 'data' => $marbot], 201);
    }

    public function destroy(Marbot $marbot): JsonResponse
    {
        $marbot->update(['aktif' => false]);
        return response()->json(['message' => 'Akun Marbot berhasil dinonaktifkan.']);
    }
}