<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('peminjamans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('barang_id')->constrained('barangs');
        $table->foreignId('id_warga')->constrained('wargas', 'id_warga'); // Ngikutin partner lu
        $table->integer('marbot_id')->default(1);
        $table->integer('jumlah');
        $table->string('keperluan')->nullable();
        $table->string('kondisi_pinjam');
        $table->string('kondisi_kembali')->nullable();
        $table->date('tgl_pinjam');
        $table->date('tgl_rencana_kembali');
        $table->date('tgl_kembali')->nullable();
        $table->string('status')->default('Dipinjam');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
