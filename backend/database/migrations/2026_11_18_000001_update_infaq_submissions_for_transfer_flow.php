<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infaq_submissions', function (Blueprint $table) {
            $table->string('blok_rumah')->default('')->after('telepon');
            $table->string('public_token', 36)->nullable()->unique()->after('blok_rumah');
            $table->string('proof_path')->nullable()->after('jenis');
        });

        DB::table('infaq_submissions')->orderBy('id')->chunkById(100, function ($submissions) {
            foreach ($submissions as $submission) {
                DB::table('infaq_submissions')
                    ->where('id', $submission->id)
                    ->update(['public_token' => (string) Str::uuid()]);
            }
        });

        if (Schema::hasColumn('infaq_submissions', 'email')) {
            Schema::table('infaq_submissions', fn (Blueprint $table) => $table->dropColumn('email'));
        }

        if (Schema::hasColumn('infaq_submissions', 'catatan')) {
            Schema::table('infaq_submissions', fn (Blueprint $table) => $table->dropColumn('catatan'));
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE infaq_submissions MODIFY jenis VARCHAR(20) NOT NULL");
        }

        DB::table('infaq_submissions')
            ->where('status', 'menunggu_konfirmasi')
            ->update(['status' => 'menunggu_transfer']);
    }

    public function down(): void
    {
        DB::table('infaq_submissions')
            ->where('status', 'menunggu_transfer')
            ->update(['status' => 'menunggu_konfirmasi']);
        DB::table('infaq_submissions')
            ->where('jenis', 'zakat')
            ->update(['jenis' => 'infaq']);

        Schema::table('infaq_submissions', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->text('catatan')->nullable();
            $table->dropUnique(['public_token']);
            $table->dropColumn(['blok_rumah', 'public_token', 'proof_path']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE infaq_submissions MODIFY jenis ENUM('infaq', 'sedekah') NOT NULL");
        }
    }
};
