<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->restrictOnDelete();
            $table->foreignId('warga_id')->constrained('wargas')->restrictOnDelete();
            $table->foreignId('marbot_id')->constrained('marbots')->restrictOnDelete();
            $table->string('keperluan');
            $table->unsignedInteger('jumlah');
            $table->string('kondisi_pinjam');
            $table->date('tgl_pinjam');
            $table->date('tgl_rencana_kembali');
            $table->date('tgl_kembali_aktual')->nullable();
            $table->string('kondisi_kembali')->nullable();
            $table->enum('status', [
                'Menunggu',
                'Aktif',
                'Selesai',
                'Terlambat',
                'Batal',
                'Rusak/Hilang',
            ])->default('Menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('tgl_pinjam');
            $table->index('tgl_rencana_kembali');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
