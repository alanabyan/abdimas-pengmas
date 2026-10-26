<?php

namespace Tests\Feature;

use App\Models\Bendahara;
use App\Models\Marbot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthAndInfaqTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['personal_access_tokens', 'marbots', 'bendaharas', 'infaq_submissions'] as $table) {
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

        $this->withToken($login->json('token'))->getJson('/api/v1/infaq')->assertForbidden();
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