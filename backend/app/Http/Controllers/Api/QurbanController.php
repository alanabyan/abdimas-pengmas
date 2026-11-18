<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bendahara;
use App\Models\Marbot;
use App\Models\QurbanRegistration;
use App\Models\QurbanSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QurbanController extends Controller
{
    public const KUOTA_TOTAL = 7;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Bendahara && ! $user instanceof Marbot) {
            return response()->json(['message' => 'Akses hanya untuk bendahara atau marbot.'], 403);
        }

        $setting = QurbanSetting::where('is_active', true)->latest()->first() ?? QurbanSetting::create([
            'periode' => 'periode-berjalan',
            'harga_sapi' => 18000000,
            'kuota_total' => self::KUOTA_TOTAL,
            'is_active' => true,
        ]);

        $registrations = QurbanRegistration::with('payments')->latest()->get();
        $registrations->each(function (QurbanRegistration $registration) {
            $paid = (int) $registration->payments->sum('amount');
            $target = (int) $registration->jumlah;
            $registration->setAttribute('total_dibayar', $paid);
            $registration->setAttribute('jumlah_pembayaran', $registration->payments->count());
            $registration->setAttribute('progres_pembayaran', $target > 0
                ? min(100, (int) floor($paid / $target * 100))
                : 0);
        });
        $filled = $registrations->count();
        $hargaSapi = (int) $setting->harga_sapi;
        $kuotaTotal = (int) ($setting->kuota_total ?? self::KUOTA_TOTAL);
        $nominalPerOrang = (int) ceil($hargaSapi / $kuotaTotal);
        $groups = $registrations->groupBy(function ($registration) {
            return $registration->sapi_id ?: 'SAPI-' . $registration->id;
        })->map(function ($items, $sapiId) {
            $status = 'terdaftar';

            if ($items->every(fn ($item) => $item->status === 'lunas')) {
                $status = 'lunas';
            } elseif ($items->contains(fn ($item) => $item->status === 'terkonfirmasi')) {
                $status = 'terkonfirmasi';
            } elseif ($items->contains(fn ($item) => $item->status === 'ditolak')) {
                $status = 'ditolak';
            }

            return [
                'sapi_id' => $sapiId,
                'participant_count' => $items->count(),
                'total_nominal' => (int) $items->sum('jumlah'),
                'total_tambahan' => (int) $items->sum('selisih_nominal'),
                'status' => $status,
                'participants' => $items->sortBy('id')->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'data' => $registrations,
            'groups' => $groups,
            'summary' => [
                'kuota_total' => $kuotaTotal,
                'kuota_tersedia' => max(0, $kuotaTotal - $filled),
                'total_terdaftar' => $filled,
                'total_nominal' => (int) $registrations->sum('jumlah'),
                'harga_sapi' => $hargaSapi,
                'nominal_per_orang' => $nominalPerOrang,
                'rekap_harian' => collect(range(6, 0, -1))->map(function (int $dayOffset) {
                    $date = now()->subDays($dayOffset);

                    return [
                        'label' => $date->translatedFormat('D'),
                        'date' => $date->toDateString(),
                        'total' => (int) QurbanRegistration::whereDate('created_at', $date)->sum('jumlah'),
                    ];
                })->values()->all(),
                'rekap_bulanan' => collect(range(5, 0, -1))->map(function (int $monthOffset) {
                    $date = now()->subMonths($monthOffset);
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();

                    return [
                        'label' => $date->translatedFormat('M'),
                        'period' => $date->format('Y-m'),
                        'total' => (int) QurbanRegistration::whereBetween('created_at', [$start, $end])->sum('jumlah'),
                    ];
                })->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Hanya marbot yang dapat menambahkan peserta qurban.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama' => ['required'],
            'telepon' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'sapi_id' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $namaInput = $request->input('nama');
        $names = [];

        if (is_array($namaInput)) {
            foreach ($namaInput as $value) {
                $name = trim((string) $value);
                if ($name !== '') {
                    $names[] = $name;
                }
            }
        } else {
            $namaValue = trim((string) $namaInput);
            if ($namaValue !== '') {
                $parts = preg_split('/[\r\n,]+/', $namaValue);
                foreach ($parts as $value) {
                    $name = trim((string) $value);
                    if ($name !== '') {
                        $names[] = $name;
                    }
                }
            }
        }

        if (empty($names)) {
            return response()->json([
                'message' => 'Nama peserta qurban wajib diisi.',
            ], 422);
        }

        $quota = (int) (QurbanSetting::where('is_active', true)->latest()->value('kuota_total') ?? self::KUOTA_TOTAL);
        $registered = QurbanRegistration::count();
        $slotsNeeded = count($names);
        $availableSlots = max(0, $quota - $registered);

        if ($slotsNeeded > $availableSlots) {
            return response()->json([
                'message' => 'Kuota qurban hanya tersisa ' . $availableSlots . ' slot. Anda mencoba mendaftarkan ' . $slotsNeeded . ' peserta.',
            ], 422);
        }

        $sapiId = trim((string) ($request->input('sapi_id') ?: 'SAPI-' . (QurbanRegistration::max('id') + 1)));
        $created = [];
        foreach ($names as $name) {
            $created[] = QurbanRegistration::create([
                'nama' => $name,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'jumlah' => $request->jumlah,
                'nominal_awal' => $request->jumlah,
                'nominal_terbaru' => $request->jumlah,
                'selisih_nominal' => 0,
                'sapi_id' => $sapiId,
                'bagian' => '1/7',
                'kuota_total' => $quota,
                'kuota_tersedia' => max(0, $quota - (QurbanRegistration::count() + 1)),
                'catatan' => $request->catatan,
                'status' => 'terdaftar',
            ]);
        }

        return response()->json([
            'message' => 'Pendaftaran qurban berhasil disimpan.',
            'data' => [
                'created_count' => count($created),
                'sapi_id' => $sapiId,
                'names' => array_map(fn ($item) => $item->nama, $created),
                'bagian' => '1/7',
                'kuota_total' => $quota,
                'kuota_tersedia' => max(0, $quota - QurbanRegistration::count()),
                'jumlah' => $request->jumlah,
            ],
        ], 201);
    }

    public function updateStatus(Request $request, QurbanRegistration $qurban): JsonResponse
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Hanya marbot yang dapat mengubah status qurban.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:terdaftar,terkonfirmasi,ditolak'],
        ]);

        if ($qurban->payments()->exists()) {
            return response()->json([
                'message' => 'Status peserta yang sudah memiliki pembayaran mengikuti progres cicilan.',
            ], 422);
        }

        $payload = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'terkonfirmasi' && empty($qurban->tanggal_konfirmasi)) {
            $payload['tanggal_konfirmasi'] = now();
        }

        if ($validated['status'] === 'ditolak') {
            $payload['tanggal_konfirmasi'] = null;
        }

        $qurban->update($payload);

        return response()->json([
            'message' => 'Status qurban berhasil diperbarui.',
            'data' => $qurban->fresh(),
        ]);
    }

    public function storePayment(Request $request, QurbanRegistration $qurban): JsonResponse
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Hanya marbot yang dapat mencatat pembayaran qurban.'], 403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = DB::transaction(function () use ($qurban, $validated) {
            $registration = QurbanRegistration::whereKey($qurban->id)
                ->lockForUpdate()
                ->firstOrFail();
            $alreadyPaid = (int) $registration->payments()->sum('amount');
            $remaining = max(0, (int) $registration->jumlah - $alreadyPaid);

            if ($registration->status === 'ditolak') {
                abort(422, 'Pembayaran tidak dapat dicatat untuk peserta yang ditolak.');
            }

            if ($validated['amount'] > $remaining) {
                abort(422, 'Nominal pembayaran melebihi sisa tagihan sebesar Rp ' . number_format($remaining, 0, ',', '.') . '.');
            }

            $payment = $registration->payments()->create([
                'payment_number' => (int) $registration->payments()->max('payment_number') + 1,
                'amount' => $validated['amount'],
                'paid_at' => $validated['paid_at'],
                'note' => $validated['note'] ?? null,
            ]);

            $totalPaid = $alreadyPaid + (int) $payment->amount;
            $registration->update([
                'status' => $totalPaid >= (int) $registration->jumlah ? 'lunas' : 'terkonfirmasi',
                'tanggal_konfirmasi' => $registration->tanggal_konfirmasi ?? now(),
            ]);

            return $payment;
        });

        return response()->json([
            'message' => 'Pembayaran qurban berhasil dicatat.',
            'data' => $payment->load('registration.payments'),
        ], 201);
    }

    public function updateSetting(Request $request): JsonResponse
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Hanya marbot yang dapat mengubah pengaturan qurban.'], 403);
        }

        $validated = $request->validate([
            'harga_sapi' => ['required', 'integer', 'min:1000000'],
            'periode' => ['nullable', 'string', 'max:100'],
            'kuota_total' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $setting = QurbanSetting::updateOrCreate(
            ['is_active' => true],
            [
                'periode' => $validated['periode'] ?? 'periode-berjalan',
                'harga_sapi' => $validated['harga_sapi'],
                'kuota_total' => $validated['kuota_total'] ?? self::KUOTA_TOTAL,
                'is_active' => true,
            ]
        );

        $nominalPerOrang = (int) ceil($setting->harga_sapi / ($setting->kuota_total ?? self::KUOTA_TOTAL));

        QurbanRegistration::whereIn('status', ['terdaftar', 'terkonfirmasi'])->each(function ($registration) use ($nominalPerOrang) {
            $nominalAwal = (int) ($registration->nominal_awal ?: $registration->jumlah);
            $selisih = max(0, $nominalPerOrang - $nominalAwal);

            $registration->update([
                'nominal_awal' => $nominalAwal,
                'nominal_terbaru' => $nominalPerOrang,
                'selisih_nominal' => $selisih,
                'jumlah' => $nominalPerOrang,
            ]);
        });

        QurbanRegistration::where('status', 'lunas')->each(function ($registration) {
            $registration->update([
                'nominal_awal' => (int) ($registration->nominal_awal ?: $registration->jumlah),
                'nominal_terbaru' => (int) ($registration->nominal_terbaru ?: $registration->jumlah),
                'selisih_nominal' => 0,
                'jumlah' => (int) ($registration->jumlah ?: $registration->nominal_awal ?: 0),
            ]);
        });

        return response()->json([
            'message' => 'Harga sapi qurban periode ini berhasil diperbarui. Selisih biaya hanya dihitung untuk peserta yang belum lunas.',
            'data' => [
                'periode' => $setting->periode,
                'harga_sapi' => (int) $setting->harga_sapi,
                'kuota_total' => (int) ($setting->kuota_total ?? self::KUOTA_TOTAL),
                'nominal_per_orang' => $nominalPerOrang,
                'selisih_nominal' => QurbanRegistration::whereIn('status', ['terdaftar', 'terkonfirmasi'])->sum('selisih_nominal'),
            ],
        ]);
    }
}
