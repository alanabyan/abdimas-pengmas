<?php

namespace Tests\Feature;

use App\Models\Bendahara;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KasFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['personal_access_tokens', 'bendaharas', 'kas_transaksis'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('bendaharas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bendahara');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('aktif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('kas_transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('jenis')->default('masuk');
            $table->string('kategori')->default('umum');
            $table->unsignedBigInteger('nominal');
            $table->text('keterangan')->nullable();
            $table->date('tanggal');
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_bendahara_can_add_and_list_cash_transactions(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Kas',
            'email' => 'bendahara.kas@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Donasi Jumat',
                'jenis' => 'masuk',
                'kategori' => 'infaq',
                'nominal' => 250000,
                'keterangan' => 'Infaq dari jamaah',
                'tanggal' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.jenis', 'masuk')
            ->assertJsonPath('data.nominal', 250000);

        $this->withToken($token)
            ->getJson('/api/v1/kas')
            ->assertOk()
            ->assertJsonPath('summary.total_masuk', 250000)
            ->assertJsonPath('summary.total_keluar', 0)
            ->assertJsonPath('summary.saldo', 250000)
            ->assertJsonCount(1, 'data');
    }

    public function test_bendahara_can_get_monthly_cash_report_and_pdf_export(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Laporan',
            'email' => 'bendahara.laporan@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Kas masuk bulan ini',
                'jenis' => 'masuk',
                'kategori' => 'infaq',
                'nominal' => 300000,
                'keterangan' => 'Infaq',
                'tanggal' => now()->toDateString(),
            ])->assertCreated();

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Belanja perlengkapan',
                'jenis' => 'keluar',
                'kategori' => 'operasional',
                'nominal' => 75000,
                'keterangan' => 'Pembelian alat',
                'tanggal' => now()->toDateString(),
            ])->assertCreated();

        $this->withToken($token)
            ->getJson('/api/v1/kas?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertJsonPath('summary.total_masuk', 300000)
            ->assertJsonPath('summary.total_keluar', 75000)
            ->assertJsonPath('summary.saldo', 225000)
            ->assertJsonPath('report.month', now()->format('Y-m'));

        $this->withToken($token)
            ->get('/api/v1/kas/pdf?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_bendahara_can_get_weekly_cash_summary_and_pdf_export(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Mingguan',
            'email' => 'bendahara.mingguan@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $start = now()->startOfWeek();

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Donasi minggu ini',
                'jenis' => 'masuk',
                'kategori' => 'infaq',
                'nominal' => 500000,
                'keterangan' => 'Minggu ini',
                'tanggal' => $start->toDateString(),
            ])->assertCreated();

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Pengeluaran minggu ini',
                'jenis' => 'keluar',
                'kategori' => 'operasional',
                'nominal' => 150000,
                'keterangan' => 'Minggu ini',
                'tanggal' => $start->copy()->addDay(2)->toDateString(),
            ])->assertCreated();

        $this->withToken($token)
            ->getJson('/api/v1/kas/weekly?week=' . $start->format('Y-m-d'))
            ->assertOk()
            ->assertJsonPath('summary.total_masuk', 500000)
            ->assertJsonPath('summary.total_keluar', 150000)
            ->assertJsonPath('summary.saldo', 350000)
            ->assertJsonPath('report.week_start', $start->format('Y-m-d'));

        $this->withToken($token)
            ->get('/api/v1/kas/weekly/pdf?week=' . $start->format('Y-m-d'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
