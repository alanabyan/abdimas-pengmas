<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InfaqSubmission;
use App\Models\KasTransaksi;
use App\Models\Marbot;
use App\Models\QurbanPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class KasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        [$start, $end, $reportMonth] = $this->monthRange($request->query('month'));
        $transactions = $this->transactionsBetween($start, $end);
        $summary = $this->summary($transactions);

        return response()->json([
            'data' => $transactions,
            'summary' => ['month' => $reportMonth, ...$summary],
            'report' => [
                'month' => $reportMonth,
                ...$summary,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function publicSummary(): JsonResponse
    {
        $transactions = $this->transactionsBetween();

        return response()->json([
            'summary' => $this->summary($transactions),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Hanya marbot yang dapat mencatat transaksi kas.'], 403);
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'jenis' => ['required', 'in:masuk,keluar'],
            'kategori' => ['required', 'string', 'max:100'],
            'nominal' => ['required', 'integer', 'min:1'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'tanggal' => ['required', 'date'],
        ]);

        $transaction = KasTransaksi::create($validated);

        return response()->json([
            'message' => 'Transaksi kas berhasil disimpan.',
            'data' => $transaction,
        ], 201);
    }

    public function pdf(Request $request): Response
    {
        [$start, $end, $reportMonth] = $this->monthRange($request->query('month'));
        $transactions = $this->transactionsBetween($start, $end);
        $summary = ['month' => $reportMonth, ...$this->summary($transactions)];

        return Pdf::loadView('laporan.kas', [
            'summary' => $summary,
            'transactions' => $transactions,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape')
            ->download('laporan-kas-' . $reportMonth . '.pdf');
    }

    public function weekly(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'week' => ['nullable', 'date'],
        ]);
        $weekStart = isset($validated['week'])
            ? Carbon::parse($validated['week'])->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $transactions = $this->transactionsBetween($weekStart, $weekEnd);
        $summary = $this->summary($transactions);

        return response()->json([
            'summary' => [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                ...$summary,
            ],
            'report' => [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                ...$summary,
                'transactions' => $transactions,
            ],
            'data' => $transactions,
        ]);
    }

    public function weeklyPdf(Request $request): Response
    {
        $validated = $request->validate([
            'week' => ['nullable', 'date'],
        ]);
        $weekStart = isset($validated['week'])
            ? Carbon::parse($validated['week'])->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $transactions = $this->transactionsBetween($weekStart, $weekEnd);
        $summary = [
            'month' => $weekStart->format('Y-m'),
            ...$this->summary($transactions),
        ];

        return Pdf::loadView('laporan.kas', [
            'summary' => $summary,
            'transactions' => $transactions,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'title' => 'Laporan Kas Mingguan',
        ])->setPaper('a4', 'landscape')
            ->download('laporan-kas-mingguan-' . $weekStart->format('Y-m-d') . '.pdf');
    }

    private function monthRange(?string $month): array
    {
        if ($month !== null && ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            abort(422, 'Format bulan harus YYYY-MM.');
        }

        $date = $month ? Carbon::createFromFormat('Y-m', $month) : now();

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
            $date->format('Y-m'),
        ];
    }

    private function transactionsBetween(?Carbon $start = null, ?Carbon $end = null): Collection
    {
        $cashQuery = KasTransaksi::query();
        $donationQuery = InfaqSubmission::query()->where('status', 'terkonfirmasi');
        $qurbanQuery = QurbanPayment::query()->with('registration');

        if ($start && $end) {
            $cashQuery->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);
            $donationQuery->whereBetween('updated_at', [$start, $end]);
            $qurbanQuery->whereBetween('paid_at', [$start->toDateString(), $end->toDateString()]);
        }

        $cash = $cashQuery->orderByDesc('tanggal')->orderByDesc('id')->get()->map(fn ($item) => [
            'id' => 'kas-' . $item->id,
            'judul' => $item->judul,
            'jenis' => $item->jenis,
            'kategori' => $item->kategori,
            'nominal' => (int) $item->nominal,
            'tanggal' => $item->tanggal?->toDateString(),
        ]);

        $donations = $donationQuery->latest('updated_at')->get()->map(fn ($item) => [
            'id' => 'ziswaf-' . $item->id,
            'judul' => ucfirst($item->jenis) . ' - ' . $item->nama,
            'jenis' => 'masuk',
            'kategori' => $item->jenis,
            'nominal' => (int) $item->jumlah,
            'tanggal' => $item->updated_at?->toDateString(),
        ]);

        $qurban = $qurbanQuery->latest('paid_at')->get()->map(fn (QurbanPayment $item) => [
            'id' => 'qurban-' . $item->id,
            'judul' => 'Pembayaran qurban ke-' . $item->payment_number . ' - ' . $item->registration?->nama,
            'jenis' => 'masuk',
            'kategori' => 'qurban',
            'nominal' => (int) $item->amount,
            'tanggal' => $item->paid_at?->toDateString(),
        ]);

        return $cash->concat($donations)->concat($qurban)
            ->sortByDesc('tanggal')
            ->values();
    }

    private function summary(Collection $transactions): array
    {
        $totalMasuk = (int) $transactions->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = (int) $transactions->where('jenis', 'keluar')->sum('nominal');

        return [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo' => $totalMasuk - $totalKeluar,
        ];
    }
}
