<?php

namespace Tests\Feature;

use App\Models\Bendahara;
use App\Models\InfaqSubmission;
use App\Models\Marbot;
use App\Models\QurbanRegistration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthAndInfaqTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'personal_access_tokens',
            'marbots',
            'bendaharas',
            'infaq_submissions',
            'qurban_registrations',
            'qurban_settings',
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

        Schema::create('infaq_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon', 30);
            $table->string('blok_rumah');
            $table->string('public_token', 36)->unique();
            $table->unsignedBigInteger('jumlah');
            $table->string('jenis');
            $table->string('proof_path')->nullable();
            $table->string('status')->default('menunggu_transfer');
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
            $table->string('sapi_id')->nullable();
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

        Schema::create('qurban_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurban_registration_id')->constrained('qurban_registrations')->cascadeOnDelete();
            $table->unsignedSmallInteger('payment_number');
            $table->unsignedBigInteger('amount');
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['qurban_registration_id', 'payment_number']);
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

        Storage::fake('local');
    }

    public function test_public_donation_requires_house_block_and_starts_waiting_for_transfer(): void
    {
        $this->postJson('/api/v1/infaq', [
            'nama' => 'Warga Cikarang',
            'telepon' => '081234567890',
            'jumlah' => 50000,
            'jenis' => 'zakat',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('blok_rumah');

        $this->postJson('/api/v1/infaq', [
            'nama' => 'Warga Cikarang',
            'telepon' => '081234567890',
            'blok_rumah' => 'A1 No. 12',
            'jumlah' => 50000,
            'jenis' => 'zakat',
            'email' => 'ignored@example.test',
            'pesan' => 'Tidak disimpan',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'menunggu_transfer')
            ->assertJsonStructure(['data' => ['public_token']]);

        $this->assertDatabaseHas('infaq_submissions', [
            'nama' => 'Warga Cikarang',
            'blok_rumah' => 'A1 No. 12',
            'jenis' => 'zakat',
            'status' => 'menunggu_transfer',
        ]);
    }

    public function test_receipt_upload_moves_donation_to_verification_and_marbot_can_confirm(): void
    {
        $submission = InfaqSubmission::create([
            'nama' => 'Warga Donasi',
            'telepon' => '081234567890',
            'blok_rumah' => 'B2 No. 3',
            'jumlah' => 100000,
            'jenis' => 'infaq',
            'status' => 'menunggu_transfer',
        ]);

        $this->post('/api/v1/infaq/' . $submission->public_token . '/receipt', [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.status', 'menunggu_konfirmasi');

        $submission->refresh();
        $this->assertSame('menunggu_konfirmasi', $submission->status);
        Storage::disk('local')->assertExists($submission->proof_path);

        $marbot = Marbot::create([
            'nama_marbot' => 'Marbot',
            'email' => 'marbot@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $this->withToken($marbot->createToken('testing')->plainTextToken)
            ->patchJson('/api/v1/infaq/' . $submission->id . '/status', [
                'status' => 'terkonfirmasi',
            ])->assertOk()
            ->assertJsonPath('data.status', 'terkonfirmasi');
    }

    public function test_bendahara_can_read_but_cannot_change_donation_status(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara',
            'email' => 'bendahara@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $submission = InfaqSubmission::create([
            'nama' => 'Warga Donasi',
            'telepon' => '081234567890',
            'blok_rumah' => 'B2 No. 3',
            'jumlah' => 100000,
            'jenis' => 'infaq',
            'proof_path' => 'ziswaf/proofs/bukti.jpg',
            'status' => 'menunggu_konfirmasi',
        ]);
        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/infaq')
            ->assertOk()
            ->assertJsonPath('data.0.has_receipt', true);

        $this->withToken($token)
            ->patchJson('/api/v1/infaq/' . $submission->id . '/status', [
                'status' => 'terkonfirmasi',
            ])->assertForbidden();
    }

    public function test_login_keeps_role_identity_for_both_account_types(): void
    {
        Marbot::create([
            'nama_marbot' => 'Marbot',
            'email' => 'login.marbot@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        Bendahara::create([
            'nama_bendahara' => 'Bendahara',
            'email' => 'login.bendahara@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);

        $marbotLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'login.marbot@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('user.role', 'marbot');

        $bendaharaLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'login.bendahara@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('user.role', 'bendahara');

        $this->withToken($marbotLogin->json('token'))->getJson('/api/v1/qurban')->assertOk();
        $this->withToken($bendaharaLogin->json('token'))->getJson('/api/v1/qurban')->assertOk();
    }

    public function test_bendahara_cannot_create_qurban_participants_or_record_payments(): void
    {
        $bendahara = Bendahara::create([
            'nama_bendahara' => 'Bendahara',
            'email' => 'bendahara.qurban@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $registration = QurbanRegistration::create([
            'nama' => 'Peserta Qurban',
            'telepon' => '081234567890',
            'jumlah' => 3000000,
            'status' => 'terdaftar',
        ]);
        $token = $bendahara->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/qurban', [
                'nama' => 'Peserta Baru',
                'telepon' => '081234567891',
                'jumlah' => 3000000,
            ])->assertForbidden();

        $this->withToken($token)
            ->patchJson('/api/v1/qurban/setting', ['harga_sapi' => 24000000])
            ->assertForbidden();

        $this->withToken($token)
            ->postJson('/api/v1/qurban/' . $registration->id . '/payments', [
                'amount' => 1000000,
                'paid_at' => now()->toDateString(),
            ])->assertForbidden();

        $this->withToken($token)->getJson('/api/v1/qurban')->assertOk();
    }

    public function test_marbot_can_register_multiple_participants_in_one_sapi_group(): void
    {
        $marbot = Marbot::create([
            'nama_marbot' => 'Marbot Qurban',
            'email' => 'marbot.group@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $token = $marbot->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/qurban', [
                'nama' => ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fani', 'Gilang'],
                'telepon' => '081234567890',
                'jumlah' => 2857143,
                'sapi_id' => 'SAPI-01',
            ])->assertCreated()
            ->assertJsonPath('data.created_count', 7);

        $this->withToken($token)
            ->getJson('/api/v1/qurban')
            ->assertOk()
            ->assertJsonPath('groups.0.sapi_id', 'SAPI-01')
            ->assertJsonPath('groups.0.participant_count', 7);
    }

    public function test_marbot_can_record_installments_and_progress_updates_until_paid(): void
    {
        $marbot = Marbot::create([
            'nama_marbot' => 'Marbot',
            'email' => 'marbot.qurban@example.test',
            'password' => 'password123',
            'aktif' => true,
        ]);
        $registration = QurbanRegistration::create([
            'nama' => 'Peserta Qurban',
            'telepon' => '081234567890',
            'jumlah' => 3000000,
            'status' => 'terdaftar',
        ]);
        $token = $marbot->createToken('testing')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/qurban/' . $registration->id . '/payments', [
                'amount' => 1000000,
                'paid_at' => now()->toDateString(),
            ])->assertCreated()
            ->assertJsonPath('data.payment_number', 1);

        $this->withToken($token)
            ->postJson('/api/v1/qurban/' . $registration->id . '/payments', [
                'amount' => 2000000,
                'paid_at' => now()->toDateString(),
            ])->assertCreated()
            ->assertJsonPath('data.payment_number', 2);

        $this->withToken($token)
            ->getJson('/api/v1/qurban')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'lunas')
            ->assertJsonPath('data.0.total_dibayar', 3000000)
            ->assertJsonPath('data.0.progres_pembayaran', 100)
            ->assertJsonPath('data.0.jumlah_pembayaran', 2);
    }
}
