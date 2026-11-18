<?php

namespace Tests\Feature;

use App\Models\Bendahara;
use App\Models\Marbot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KasFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'personal_access_tokens',
            'marbots',
            'bendaharas',
            'kas_transaksis',
            'infaq_submissions',
            'qurban_registrations',
            'qurban_payments',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('marbots', function (Blueprint $table) {
            $table->id();
            $table->string('nama_marbot');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('aktif')->default(true);
            $table->boolean('is_super_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

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

        Schema::create('infaq_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon');
            $table->string('blok_rumah');
            $table->string('public_token')->unique();
            $table->unsignedBigInteger('jumlah');
            $table->string('jenis');
            $table->string('proof_path')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('qurban_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon');
            $table->unsignedBigInteger('jumlah');
            $table->timestamps();
        });

        Schema::create('qurban_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurban_registration_id')->constrained('qurban_registrations');
            $table->unsignedSmallInteger('payment_number');
            $table->unsignedBigInteger('amount');
            $table->date('paid_at');
            $table->string('note')->nullable();
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

    public function test_marbot_can_record_and_view_cash_transactions(): void
    {
        $marbot = Marbot::create([
            'nama_marbot' => 'Marbot Kas',
            'email' => 'marbot.kas@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $token = $marbot->createToken('testing')->plainTextToken;

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

    public function test_bendahara_can_read_cash_but_cannot_create_transactions(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Read Only',
            'email' => 'bendahara.readonly@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Percobaan',
                'jenis' => 'masuk',
                'kategori' => 'umum',
                'nominal' => 1000,
                'tanggal' => now()->toDateString(),
            ])->assertForbidden();

        $this->withToken($token)->getJson('/api/v1/kas')->assertOk();
    }

    public function test_marbot_can_export_monthly_and_weekly_cash_reports(): void
    {
        $marbot = Marbot::create([
            'nama_marbot' => 'Marbot Laporan',
            'email' => 'marbot.laporan@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $token = $marbot->createToken('testing')->plainTextToken;
        $start = now()->startOfWeek();

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Donasi minggu ini',
                'jenis' => 'masuk',
                'kategori' => 'infaq',
                'nominal' => 500000,
                'tanggal' => $start->toDateString(),
            ])->assertCreated();

        $this->withToken($token)
            ->postJson('/api/v1/kas', [
                'judul' => 'Pengeluaran minggu ini',
                'jenis' => 'keluar',
                'kategori' => 'operasional',
                'nominal' => 150000,
                'tanggal' => $start->copy()->addDay(2)->toDateString(),
            ])->assertCreated();

        $this->withToken($token)
            ->getJson('/api/v1/kas/weekly?week=' . $start->format('Y-m-d'))
            ->assertOk()
            ->assertJsonPath('summary.total_masuk', 500000)
            ->assertJsonPath('summary.total_keluar', 150000)
            ->assertJsonPath('summary.saldo', 350000);

        $this->withToken($token)
            ->get('/api/v1/kas/pdf?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->withToken($token)
            ->get('/api/v1/kas/weekly/pdf?week=' . $start->format('Y-m-d'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
