<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bendahara;
use App\Models\InfaqSubmission;
use App\Models\Marbot;
use App\Models\QurbanRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InfaqController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'telepon' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'jenis' => ['required', 'in:infaq,sedekah,sadaqah'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $normalizedJenis = $data['jenis'] === 'sadaqah' ? 'sedekah' : $data['jenis'];

        $submission = InfaqSubmission::create([
            ...$data,
            'jenis' => $normalizedJenis,
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
        $user = $request->user();

        if (! $user instanceof Bendahara && ! $user instanceof Marbot) {
            return response()->json(['message' => 'Akses hanya untuk bendahara atau marbot.'], 403);
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
                'rekap_harian' => collect(range(6, 0, -1))->map(function (int $dayOffset) {
                    $date = now()->subDays($dayOffset);

                    return [
                        'label' => $date->translatedFormat('D'),
                        'date' => $date->toDateString(),
                        'total' => (int) InfaqSubmission::whereDate('created_at', $date)->sum('jumlah'),
                    ];
                })->values()->all(),
                'rekap_bulanan' => collect(range(5, 0, -1))->map(function (int $monthOffset) {
                    $date = now()->subMonths($monthOffset);
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();

                    return [
                        'label' => $date->translatedFormat('M'),
                        'period' => $date->format('Y-m'),
                        'total' => (int) InfaqSubmission::whereBetween('created_at', [$start, $end])->sum('jumlah'),
                    ];
                })->values()->all(),
            ],
        ]);
    }

    public function updateStatus(Request $request, InfaqSubmission $infaq): JsonResponse
    {
        if (! $request->user() instanceof Bendahara) {
            return response()->json(['message' => 'Akses hanya untuk bendahara.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:menunggu_konfirmasi,terkonfirmasi,ditolak'],
        ]);

        $infaq->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Status pengajuan infaq berhasil diperbarui.',
            'data' => $infaq->fresh(),
        ]);
    }

    public function laporanKeuangan(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Bendahara && ! $user instanceof Marbot) {
            return response()->json(['message' => 'Akses hanya untuk bendahara atau marbot.'], 403);
        }

        $selectedMonth = $request->query('month');
        $monthStart = null;

        if ($selectedMonth && preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        }

        $infaqQuery = InfaqSubmission::query();
        $qurbanQuery = QurbanRegistration::query();

        if ($monthStart) {
            $year = $monthStart->year;
            $month = $monthStart->month;

            $infaqQuery->whereYear('created_at', $year)->whereMonth('created_at', $month);
            $qurbanQuery->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        $monthSeries = collect(range(5, 0, -1))->map(function (int $monthOffset) use ($selectedMonth) {
            $month = now()->copy()->subMonths($monthOffset);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            return [
                'label' => $month->translatedFormat('M Y'),
                'month' => $month->format('Y-m'),
                'infaq' => (int) InfaqSubmission::whereBetween('created_at', [$monthStart, $monthEnd])->sum('jumlah'),
                'qurban' => (int) QurbanRegistration::whereBetween('created_at', [$monthStart, $monthEnd])->sum('jumlah'),
            ];
        })->reverse()->values();

        $monthlyTotals = $monthSeries->map(fn ($item) => [
            'label' => $item['label'],
            'month' => $item['month'],
            'infaq' => $item['infaq'],
            'qurban' => $item['qurban'],
            'total' => $item['infaq'] + $item['qurban'],
        ])->values();

        $infaqTransactions = (clone $infaqQuery)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'infaq',
                    'nama' => $item->nama,
                    'jumlah' => (int) $item->jumlah,
                    'status' => $item->status,
                    'tanggal' => $item->created_at?->toIso8601String(),
                ];
            })->all();

        $qurbanTransactions = (clone $qurbanQuery)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'qurban',
                    'nama' => $item->nama,
                    'jumlah' => (int) $item->jumlah,
                    'status' => $item->status,
                    'tanggal' => $item->created_at?->toIso8601String(),
                ];
            })->all();

        $transactions = collect([...$qurbanTransactions, ...$infaqTransactions])
            ->values()
            ->take(10)
            ->all();

        $totalInfaq = (int) $infaqQuery->sum('jumlah');
        $totalQurban = (int) $qurbanQuery->sum('jumlah');

        return response()->json([
            'summary' => [
                'month' => $selectedMonth ?: now()->format('Y-m'),
                'total_infaq' => $totalInfaq,
                'total_qurban' => $totalQurban,
                'total_keseluruhan' => $totalInfaq + $totalQurban,
                'total_pengajuan_infaq' => $infaqQuery->count(),
                'total_pendaftar_qurban' => $qurbanQuery->count(),
            ],
            'monthly' => $monthlyTotals,
            'transactions' => $transactions,
        ]);
    }

    public function keuanganPdf(Request $request): Response
    {
        $user = $request->user();

        if (! $user instanceof Bendahara && ! $user instanceof Marbot) {
            return response('Akses hanya untuk bendahara atau marbot.', 403);
        }

        $selectedMonth = $request->query('month');
        $monthStart = null;

        if ($selectedMonth && preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        }

        $infaqQuery = InfaqSubmission::query();
        $qurbanQuery = QurbanRegistration::query();

        if ($monthStart) {
            $year = $monthStart->year;
            $month = $monthStart->month;
            $infaqQuery->whereYear('created_at', $year)->whereMonth('created_at', $month);
            $qurbanQuery->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        $summary = [
            'month' => $selectedMonth ?: now()->format('Y-m'),
            'total_infaq' => (int) $infaqQuery->sum('jumlah'),
            'total_qurban' => (int) $qurbanQuery->sum('jumlah'),
            'total_keseluruhan' => (int) $infaqQuery->sum('jumlah') + (int) $qurbanQuery->sum('jumlah'),
            'total_pengajuan_infaq' => $infaqQuery->count(),
            'total_pendaftar_qurban' => $qurbanQuery->count(),
        ];

        $monthly = collect(range(5, 0, -1))->map(function (int $monthOffset) {
            $month = now()->copy()->subMonths($monthOffset);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            return [
                'label' => $month->translatedFormat('M Y'),
                'month' => $month->format('Y-m'),
                'infaq' => (int) InfaqSubmission::whereBetween('created_at', [$monthStart, $monthEnd])->sum('jumlah'),
                'qurban' => (int) QurbanRegistration::whereBetween('created_at', [$monthStart, $monthEnd])->sum('jumlah'),
                'total' => (int) InfaqSubmission::whereBetween('created_at', [$monthStart, $monthEnd])->sum('jumlah')
                    + (int) QurbanRegistration::whereBetween('created_at', [$monthStart, $monthEnd])->sum('jumlah'),
            ];
        })->reverse()->values();

        $transactions = collect([
            ...$qurbanQuery->latest('created_at')->take(5)->get()->map(fn ($item) => [
                'type' => 'qurban',
                'nama' => $item->nama,
                'jumlah' => (int) $item->jumlah,
                'status' => $item->status,
                'tanggal' => $item->created_at?->translatedFormat('d M Y'),
            ])->all(),
            ...$infaqQuery->latest('created_at')->take(5)->get()->map(fn ($item) => [
                'type' => 'infaq',
                'nama' => $item->nama,
                'jumlah' => (int) $item->jumlah,
                'status' => $item->status,
                'tanggal' => $item->created_at?->translatedFormat('d M Y'),
            ])->all(),
        ])->take(10)->values();

        $pdf = Pdf::loadView('laporan.keuangan', [
            'summary' => $summary,
            'monthly' => $monthly,
            'transactions' => $transactions,
            'selectedMonth' => $selectedMonth ?: now()->format('Y-m'),
            'tanggal' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-keuangan-' . ($selectedMonth ?: now()->format('Y-m')) . '.pdf');
    }
}