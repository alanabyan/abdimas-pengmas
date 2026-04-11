<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategoris')->restrictOnDelete();
            $table->string('nama_barang');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('stok_total')->default(1);
            $table->unsignedInteger('stok_tersedia')->default(1);
            $table->enum('kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->default('Baik');
            $table->string('foto_url')->nullable();
            $table->string('lokasi')->nullable()->comment('Lokasi penyimpanan di masjid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
