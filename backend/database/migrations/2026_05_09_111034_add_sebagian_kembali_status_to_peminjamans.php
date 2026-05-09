<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM(
            'Menunggu','Aktif','Selesai','Terlambat','Batal','Rusak/Hilang','Sebagian Kembali'
        ) DEFAULT 'Menunggu'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM(
            'Menunggu','Aktif','Selesai','Terlambat','Batal','Rusak/Hilang'
        ) DEFAULT 'Menunggu'");
    }
};
