<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infaq_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon', 30);
            $table->string('email')->nullable();
            $table->unsignedBigInteger('jumlah');
            $table->enum('jenis', ['infaq', 'sedekah']);
            $table->text('catatan')->nullable();
            $table->string('status')->default('menunggu_konfirmasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infaq_submissions');
    }
};