<?php

namespace Tests\Feature;

use App\Models\Bendahara;
use App\Models\InfaqSubmission;
use App\Models\Marbot;
use App\Models\QurbanRegistration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthAndInfaqTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['personal_access_tokens', 'marbots', 'bendaharas', 'infaq_submissions', 'qurban_registrations', 'qurban_settings'] as $table) {
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

        Schema::create('infaq_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon', 30);
            $table->string('email')->nullable();
            $table->unsignedBigInteger('jumlah');
            $table->string('jenis');
            $table->text('catatan')->nullable();
            $table->string('status')->default('menunggu_konfirmasi');
            $table->timestamps();
        });

        Schema::create('qurban_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon', 30);
            $table->string('email')->nullable();
            $table->unsignedBigInteger('jumlah');
            $table->unsignedBigInteger('nominal_awal')->default(0);
            $table->unsignedBigInteger('nominal_terbaru')->default(0);
            $table->unsignedBigInteger('selisih_nominal')->default(0);
            $table->string('bagian')->default('1/7');
            $table->unsignedTinyInteger('kuota_total')->default(7);
            $table->unsignedTinyInteger('kuota_tersedia')->default(6);
            $table->text('catatan')->nullable();
            $table->string('status')->default('terdaftar');
            $table->timestamp('tanggal_konfirmasi')->nullable();
            $table->timestamps();
        });

        Schema::create('qurban_settings', function (Blueprint $table) {
            $table->id();
            $table->string('periode')->default('periode-berjalan');
            $table->unsignedBigInteger('harga_sapi')->default(18000000);
            $table->unsignedTinyInteger('kuota_total')->default(7);
            $table->boolean('is_active')->default(true);
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

    public function test_marbot_login_includes_role(): void
    {
        Marbot::create([
            'nama_marbot' => 'Marbot Utama',
            'email' => 'marbot@example.test',
            'password' => 'password123',
            'aktif' => true,
            'is_super_admin' => false,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'marbot@example.test',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('user.role', 'marbot')
            ->assertJsonPath('user.nama', 'Marbot Utama');

        $this->withToken($login->json('token'))
            ->getJson('/api/v1/infaq')
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->withToken($login->json('token'))
            ->getJson('/api/v1/qurban')
            ->assertOk();
    }

    public function test_bendahara_login_includes_role_and_can_view_infaq_submissions(): void
    {
        Bendahara::create([
            'nama_bendahara' => 'Bendahara Utama',
            'email' => 'bendahara@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'bendahara@example.test',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('user.role', 'bendahara')
            ->assertJsonPath('user.nama', 'Bendahara Utama');

        $this->withToken($login->json('token'))
            ->getJson('/api/v1/infaq')
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->getJson('/api/v1/barang', [
            'Authorization' => 'Bearer ' . $login->json('token'),
        ])->assertForbidden();
    }

    public function test_finance_summary_includes_daily_and_monthly_recap(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Rekap',
            'email' => 'bendahara.rekap@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        InfaqSubmission::create([
            'nama' => 'Rekap Infaq',
            'telepon' => '081234567892',
            'email' => 'rekap@example.test',
            'jumlah' => 150000,
            'jenis' => 'infaq',
            'catatan' => 'Rekap harian',
            'status' => 'menunggu_konfirmasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        QurbanRegistration::create([
            'nama' => 'Rekap Qurban',
            'telepon' => '081234567893',
            'email' => 'rekapqurban@example.test',
            'jumlah' => 2857143,
            'bagian' => '1/7',
            'kuota_total' => 7,
            'kuota_tersedia' => 6,
            'catatan' => 'Rekap bulanan',
            'status' => 'terdaftar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/infaq')
            ->assertOk()
            ->assertJsonPath('summary.rekap_harian.0.label', now()->subDays(6)->translatedFormat('D'))
            ->assertJsonPath('summary.rekap_bulanan.0.label', now()->subMonths(5)->translatedFormat('M'));

        $this->withToken($token)
            ->getJson('/api/v1/qurban')
            ->assertOk()
            ->assertJsonPath('summary.rekap_harian.0.label', now()->subDays(6)->translatedFormat('D'))
            ->assertJsonPath('summary.rekap_bulanan.0.label', now()->subMonths(5)->translatedFormat('M'));
    }

    public function test_bendahara_login_accepts_the_project_email_alias_case_insensitively(): void
    {
        Bendahara::create([
            'nama_bendahara' => 'Bendahara Abdimas',
            'email' => 'bendahara@abdimas-pengmas.org',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'Bendahara@Abdimas-Pengmas.org',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('user.role', 'bendahara')
            ->assertJsonPath('user.nama', 'Bendahara Abdimas');
    }

    public function test_qurban_registration_uses_the_seven_person_share_rule_and_keeps_kuota(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Qurban',
            'email' => 'bendahara.qurban@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/qurban', [
                'nama' => 'Andi Wijaya',
                'telepon' => '081234567891',
                'email' => 'andi@example.test',
                'jumlah' => 2857143,
                'catatan' => 'Ikut 1/7 bagian sapi',
            ])->assertCreated()
            ->assertJsonPath('data.bagian', '1/7')
            ->assertJsonPath('data.kuota_tersedia', 6)
            ->assertJsonPath('data.kuota_total', 7);

        $this->assertDatabaseHas('qurban_registrations', [
            'nama' => 'Andi Wijaya',
            'bagian' => '1/7',
        ]);
    }

    public function test_qurban_registration_accepts_multiple_names_for_a_single_sapi(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Nama 7 Orang',
            'email' => 'bendahara.7orang@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/qurban', [
                'nama' => ['Andi Wijaya', 'Budi Santoso', 'Citra Lestari', 'Dewi Rahma', 'Eko Prasetyo', 'Fani Nuraeni', 'Gilang Permana'],
                'telepon' => '081234567890',
                'email' => 'sapi7@example.test',
                'jumlah' => 2857143,
                'catatan' => 'Satu sapi dibagi 7 peserta',
            ])->assertCreated()
            ->assertJsonPath('data.created_count', 7)
            ->assertJsonPath('data.names.0', 'Andi Wijaya');

        foreach (['Andi Wijaya', 'Budi Santoso', 'Citra Lestari', 'Dewi Rahma', 'Eko Prasetyo', 'Fani Nuraeni', 'Gilang Permana'] as $nama) {
            $this->assertDatabaseHas('qurban_registrations', [
                'nama' => $nama,
                'telepon' => '081234567890',
                'bagian' => '1/7',
            ]);
        }
    }

    public function test_bendahara_can_update_infaq_submission_status(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Infaq',
            'email' => 'bendahara.infaq@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $submission = InfaqSubmission::create([
            'nama' => 'Ahmad Ridwan',
            'telepon' => '081234567899',
            'email' => 'ahmad@example.test',
            'jumlah' => 250000,
            'jenis' => 'infaq',
            'catatan' => 'Donasi kegiatan',
            'status' => 'menunggu_konfirmasi',
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/infaq/' . $submission->id . '/status', [
                'status' => 'terkonfirmasi',
            ])->assertOk()
            ->assertJsonPath('data.status', 'terkonfirmasi');

        $this->assertDatabaseHas('infaq_submissions', [
            'id' => $submission->id,
            'status' => 'terkonfirmasi',
        ]);
    }

    public function test_bendahara_can_update_qurban_registration_status(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Status',
            'email' => 'bendahara.status@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $registration = \App\Models\QurbanRegistration::create([
            'nama' => 'Rina Utami',
            'telepon' => '081234567892',
            'email' => 'rina@example.test',
            'jumlah' => 2857143,
            'bagian' => '1/7',
            'kuota_total' => 7,
            'kuota_tersedia' => 6,
            'status' => 'terdaftar',
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/qurban/' . $registration->id . '/status', [
                'status' => 'lunas',
            ])->assertOk()
            ->assertJsonPath('data.status', 'lunas');

        $this->assertDatabaseHas('qurban_registrations', [
            'id' => $registration->id,
            'status' => 'lunas',
        ]);
    }

    public function test_bendahara_can_update_qurban_price_for_the_current_period(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Harga',
            'email' => 'bendahara.harga@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/qurban/setting', [
                'harga_sapi' => 24000000,
            ])->assertOk()
            ->assertJsonPath('data.harga_sapi', 24000000)
            ->assertJsonPath('data.nominal_per_orang', 3428572);

        $this->withToken($token)
            ->getJson('/api/v1/qurban')
            ->assertOk()
            ->assertJsonPath('summary.harga_sapi', 24000000)
            ->assertJsonPath('summary.nominal_per_orang', 3428572);
    }

    public function test_qurban_price_increase_marks_the_difference_for_unpaid_participants_only(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Kenaikan Harga',
            'email' => 'bendahara.kenaikan@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        $paid = \App\Models\QurbanRegistration::create([
            'nama' => 'Sudah Lunas',
            'telepon' => '081234567894',
            'email' => 'lunas@example.test',
            'jumlah' => 2571429,
            'bagian' => '1/7',
            'kuota_total' => 7,
            'kuota_tersedia' => 6,
            'status' => 'lunas',
        ]);

        $pending = \App\Models\QurbanRegistration::create([
            'nama' => 'Belum Lunas',
            'telepon' => '081234567895',
            'email' => 'pending@example.test',
            'jumlah' => 2571429,
            'bagian' => '1/7',
            'kuota_total' => 7,
            'kuota_tersedia' => 6,
            'status' => 'terdaftar',
        ]);

        $this->withToken($token)
            ->patchJson('/api/v1/qurban/setting', [
                'harga_sapi' => 24000000,
            ])->assertOk();

        $pending->refresh();
        $paid->refresh();

        $this->assertSame(3428572, (int) $pending->jumlah);
        $this->assertSame(857143, (int) $pending->selisih_nominal);
        $this->assertSame(2571429, (int) $paid->jumlah);
        $this->assertSame(0, (int) $paid->selisih_nominal);
    }

    public function test_public_sadaqah_submission_is_accepted_and_stored_under_the_standard_type(): void
    {
        $this->postJson('/api/v1/infaq', [
            'nama' => 'Warga Sadaqah',
            'telepon' => '081234567891',
            'email' => 'sadaqah@example.test',
            'jumlah' => 75000,
            'jenis' => 'sadaqah',
            'catatan' => 'Untuk kebutuhan santunan',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'menunggu_konfirmasi');

        $this->assertDatabaseHas('infaq_submissions', [
            'nama' => 'Warga Sadaqah',
            'jumlah' => 75000,
            'jenis' => 'sedekah',
        ]);
    }

    public function test_keuangan_report_returns_monthly_totals_and_recent_transactions(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Laporan',
            'email' => 'bendahara.laporan@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        InfaqSubmission::create([
            'nama' => 'Laporan Infaq',
            'telepon' => '081234567899',
            'email' => 'laporan.infaq@example.test',
            'jumlah' => 200000,
            'jenis' => 'infaq',
            'catatan' => 'Laporan rutin',
            'status' => 'terkonfirmasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        QurbanRegistration::create([
            'nama' => 'Laporan Qurban',
            'telepon' => '081234567897',
            'email' => 'laporan.qurban@example.test',
            'jumlah' => 2857143,
            'bagian' => '1/7',
            'kuota_total' => 7,
            'kuota_tersedia' => 6,
            'catatan' => 'Laporan qurban',
            'status' => 'lunas',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/laporan/keuangan')
            ->assertOk()
            ->assertJsonPath('summary.total_infaq', 200000)
            ->assertJsonPath('summary.total_qurban', 2857143)
            ->assertJsonPath('monthly.0.infaq', 200000)
            ->assertJsonPath('transactions.0.type', 'qurban');
    }

    public function test_keuangan_report_can_be_filtered_by_specific_month(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara Filter',
            'email' => 'bendahara.filter@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        InfaqSubmission::create([
            'nama' => 'Infaq Bulan Ini',
            'telepon' => '081234567810',
            'email' => 'bulan.ini@example.test',
            'jumlah' => 150000,
            'jenis' => 'infaq',
            'catatan' => 'Untuk bulan ini',
            'status' => 'terkonfirmasi',
            'created_at' => now()->startOfMonth(),
            'updated_at' => now()->startOfMonth(),
        ]);

        InfaqSubmission::create([
            'nama' => 'Infaq Bulan Lalu',
            'telepon' => '081234567811',
            'email' => 'bulan.lalu@example.test',
            'jumlah' => 300000,
            'jenis' => 'infaq',
            'catatan' => 'Untuk bulan lalu',
            'status' => 'terkonfirmasi',
            'created_at' => now()->subMonth()->startOfMonth(),
            'updated_at' => now()->subMonth()->startOfMonth(),
        ]);

        QurbanRegistration::create([
            'nama' => 'Qurban Bulan Ini',
            'telepon' => '081234567812',
            'email' => 'qurban.ini@example.test',
            'jumlah' => 2857143,
            'bagian' => '1/7',
            'kuota_total' => 7,
            'kuota_tersedia' => 6,
            'status' => 'lunas',
            'created_at' => now()->startOfMonth(),
            'updated_at' => now()->startOfMonth(),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/laporan/keuangan?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertJsonPath('summary.total_infaq', 150000)
            ->assertJsonPath('summary.total_qurban', 2857143)
            ->assertJsonPath('summary.month', now()->format('Y-m'));
    }

    public function test_keuangan_report_can_be_exported_as_pdf(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara PDF',
            'email' => 'bendahara.pdf@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $token = $bendahara->createToken('testing')->plainTextToken;

        InfaqSubmission::create([
            'nama' => 'Infaq PDF',
            'telepon' => '081234567813',
            'email' => 'pdf.infaq@example.test',
            'jumlah' => 220000,
            'jenis' => 'infaq',
            'catatan' => 'Untuk laporan PDF',
            'status' => 'terkonfirmasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($token)
            ->get('/api/v1/laporan/keuangan/pdf')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_public_infaq_submission_is_stored_and_not_publicly_listed(): void
    {
        $this->postJson('/api/v1/infaq', [
            'nama' => 'Warga Cikarang',
            'telepon' => '081234567890',
            'email' => 'warga@example.test',
            'jumlah' => 50000,
            'jenis' => 'sedekah',
            'catatan' => 'Untuk kegiatan pendidikan',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'menunggu_konfirmasi');

        $this->assertDatabaseHas('infaq_submissions', [
            'nama' => 'Warga Cikarang',
            'jumlah' => 50000,
            'jenis' => 'sedekah',
        ]);

        $this->getJson('/api/v1/infaq')->assertUnauthorized();
    }
}