<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KasTransaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $selectedMonth = $request->query('month');
        $query = KasTransaksi::query();

        if ($selectedMonth && preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth);
            $query->whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month);
        }

        $items = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $totalMasuk = (int) $items->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = (int) $items->where('jenis', 'keluar')->sum('nominal');
        $reportMonth = $selectedMonth ?: now()->format('Y-m');

        return response()->json([
            'data' => $items,
            'summary' => [
                'month' => $reportMonth,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo' => $totalMasuk - $totalKeluar,
            ],
            'report' => [
                'month' => $reportMonth,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo' => $totalMasuk - $totalKeluar,
                'transactions' => $items->map(fn ($item) => [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'jenis' => $item->jenis,
                    'kategori' => $item->kategori,
                    'nominal' => (int) $item->nominal,
                    'tanggal' => $item->tanggal?->toDateString(),
                ])->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
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
        $selectedMonth = $request->query('month');
        $query = KasTransaksi::query();

        if ($selectedMonth && preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth);
            $query->whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month);
        }

        $items = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $totalMasuk = (int) $items->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = (int) $items->where('jenis', 'keluar')->sum('nominal');
        $reportMonth = $selectedMonth ?: now()->format('Y-m');

        $pdf = Pdf::loadView('laporan.kas', [
            'summary' => [
                'month' => $reportMonth,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo' => $totalMasuk - $totalKeluar,
            ],
            'transactions' => $items,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kas-' . $reportMonth . '.pdf');
    }

    public function weekly(Request $request): JsonResponse
    {
        $selectedWeek = $request->query('week');
        $weekStart = $selectedWeek ? \Carbon\Carbon::parse($selectedWeek) : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $items = KasTransaksi::query()
            ->whereBetween('tanggal', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $totalMasuk = (int) $items->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = (int) $items->where('jenis', 'keluar')->sum('nominal');

        return response()->json([
            'summary' => [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo' => $totalMasuk - $totalKeluar,
            ],
            'report' => [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo' => $totalMasuk - $totalKeluar,
                'transactions' => $items->map(fn ($item) => [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'jenis' => $item->jenis,
                    'kategori' => $item->kategori,
                    'nominal' => (int) $item->nominal,
                    'tanggal' => $item->tanggal?->toDateString(),
                ])->values()->all(),
            ],
            'data' => $items,
        ]);
    }

    public function weeklyPdf(Request $request): Response
    {
        $selectedWeek = $request->query('week');
        $weekStart = $selectedWeek ? \Carbon\Carbon::parse($selectedWeek) : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $items = KasTransaksi::query()
            ->whereBetween('tanggal', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $totalMasuk = (int) $items->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = (int) $items->where('jenis', 'keluar')->sum('nominal');

        $pdf = Pdf::loadView('laporan.kas', [
            'summary' => [
                'month' => $weekStart->format('Y-m'),
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo' => $totalMasuk - $totalKeluar,
            ],
            'transactions' => $items,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'title' => 'Laporan Kas Mingguan',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kas-mingguan-' . $weekStart->format('Y-m-d') . '.pdf');
    }
}
