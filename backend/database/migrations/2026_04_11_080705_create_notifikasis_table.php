<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->nullable()->constrained('peminjamans')->nullOnDelete();
            $table->enum('tipe', ['terlambat', 'stok_habis', 'jatuh_tempo']);
            $table->text('pesan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
            $table->index('dibaca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
