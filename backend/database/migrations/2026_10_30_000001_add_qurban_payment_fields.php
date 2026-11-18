<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qurban_registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('nominal_awal')->default(0)->after('jumlah');
            $table->unsignedBigInteger('nominal_terbaru')->default(0)->after('nominal_awal');
            $table->unsignedBigInteger('selisih_nominal')->default(0)->after('nominal_terbaru');
            $table->timestamp('tanggal_konfirmasi')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('qurban_registrations', function (Blueprint $table) {
            $table->dropColumn(['nominal_awal', 'nominal_terbaru', 'selisih_nominal', 'tanggal_konfirmasi']);
        });
    }
};
