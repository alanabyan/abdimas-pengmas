<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon', 30);
            $table->string('email')->nullable();
            $table->unsignedBigInteger('jumlah');
            $table->string('bagian')->default('1/7');
            $table->unsignedTinyInteger('kuota_total')->default(7);
            $table->unsignedTinyInteger('kuota_tersedia')->default(6);
            $table->text('catatan')->nullable();
            $table->string('status')->default('terdaftar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_registrations');
    }
};
