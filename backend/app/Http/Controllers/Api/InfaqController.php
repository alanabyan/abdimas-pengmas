<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bendahara;
use App\Models\InfaqSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfaqController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'telepon' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'jenis' => ['required', 'in:infaq,sedekah'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission = InfaqSubmission::create([
            ...$data,
            'status' => 'menunggu_konfirmasi',
        ]);

        return response()->json([
            'message' => 'Pengajuan infaq/sedekah berhasil dikirim. Bendahara akan menghubungi Anda untuk konfirmasi.',
            'data' => [
                'id' => $submission->id,
                'status' => $submission->status,
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user() instanceof Bendahara) {
            return response()->json(['message' => 'Akses hanya untuk bendahara.'], 403);
        }

        $submissions = InfaqSubmission::latest()->paginate(20);

        return response()->json([
            'data' => $submissions->items(),
            'pagination' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'total' => $submissions->total(),
            ],
            'summary' => [
                'total_pengajuan' => InfaqSubmission::count(),
                'total_nominal' => InfaqSubmission::sum('jumlah'),
                'menunggu_konfirmasi' => InfaqSubmission::where('status', 'menunggu_konfirmasi')->count(),
            ],
        ]);
    }
}