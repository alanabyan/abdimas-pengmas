<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_settings', function (Blueprint $table) {
            $table->id();
            $table->string('periode')->default('periode-berjalan');
            $table->unsignedBigInteger('harga_sapi')->default(18000000);
            $table->unsignedTinyInteger('kuota_total')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_settings');
    }
};
