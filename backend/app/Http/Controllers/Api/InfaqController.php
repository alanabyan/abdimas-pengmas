<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bendahara;
use App\Models\InfaqSubmission;
use App\Models\Marbot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InfaqController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'telepon' => ['required', 'string', 'max:30'],
            'blok_rumah' => ['required', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'jenis' => ['required', 'in:zakat,infaq,sedekah'],
        ]);

        $submission = InfaqSubmission::create([
            ...$data,
            'status' => 'menunggu_transfer',
        ]);

        return response()->json([
            'message' => 'Data donasi berhasil disimpan. Silakan transfer dan unggah bukti pembayaran.',
            'data' => [
                'id' => $submission->id,
                'public_token' => $submission->public_token,
                'status' => $submission->status,
            ],
        ], 201);
    }

    public function uploadReceipt(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $submission = InfaqSubmission::where('public_token', $token)->firstOrFail();

        if (! in_array($submission->status, ['menunggu_transfer', 'menunggu_konfirmasi'], true)) {
            return response()->json([
                'message' => 'Bukti transfer tidak dapat diubah setelah donasi diverifikasi.',
            ], 422);
        }

        $path = $validated['bukti']->store('ziswaf/proofs', 'local');

        if (! $path) {
            throw new \RuntimeException('Bukti transfer gagal disimpan.');
        }

        if ($submission->proof_path) {
            Storage::disk('local')->delete($submission->proof_path);
        }

        $submission->update([
            'proof_path' => $path,
            'status' => 'menunggu_konfirmasi',
        ]);

        return response()->json([
            'message' => 'Bukti transfer berhasil diunggah dan menunggu verifikasi.',
            'data' => [
                'status' => $submission->status,
            ],
        ]);
    }

    public function downloadReceipt(Request $request, InfaqSubmission $infaq): BinaryFileResponse|JsonResponse
    {
        if (! $request->user() instanceof Bendahara && ! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Akses hanya untuk bendahara atau marbot.'], 403);
        }

        if (! $infaq->proof_path || ! Storage::disk('local')->exists($infaq->proof_path)) {
            abort(404, 'Bukti transfer tidak ditemukan.');
        }

        return response()->download(
            Storage::disk('local')->path($infaq->proof_path),
            'bukti-ziswaf-' . $infaq->id . '.' . pathinfo($infaq->proof_path, PATHINFO_EXTENSION),
        );
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user() instanceof Bendahara && ! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Akses hanya untuk bendahara atau marbot.'], 403);
        }

        $submissions = InfaqSubmission::latest()->paginate(20);
        $items = collect($submissions->items())->map(function (InfaqSubmission $submission) {
            return [
                ...$submission->toArray(),
                'has_receipt' => (bool) $submission->proof_path,
                'proof_extension' => $submission->proof_path
                    ? pathinfo($submission->proof_path, PATHINFO_EXTENSION)
                    : null,
            ];
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'total' => $submissions->total(),
            ],
            'summary' => [
                'total_pengajuan' => InfaqSubmission::count(),
                'total_nominal' => InfaqSubmission::where('status', 'terkonfirmasi')->sum('jumlah'),
                'menunggu_transfer' => InfaqSubmission::where('status', 'menunggu_transfer')->count(),
                'menunggu_konfirmasi' => InfaqSubmission::where('status', 'menunggu_konfirmasi')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, InfaqSubmission $infaq): JsonResponse
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Hanya marbot yang dapat memverifikasi donasi.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:terkonfirmasi,ditolak'],
        ]);

        if (! $infaq->proof_path || $infaq->status !== 'menunggu_konfirmasi') {
            return response()->json([
                'message' => 'Donasi hanya dapat diverifikasi setelah bukti transfer diunggah.',
            ], 422);
        }

        $infaq->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Status donasi berhasil diperbarui.',
            'data' => $infaq->fresh(),
        ]);
    }
}
