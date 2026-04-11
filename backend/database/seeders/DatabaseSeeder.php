<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Marbot
        DB::table('marbots')->insert([
            'nama_marbot' => 'Marbot Masjid', 
            'email' => 'marbot@masjid.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Warga
        DB::table('wargas')->insert([
            'nama_warga' => 'Budi Santoso', 
            'alamat' => 'RT 01 RW 02',
            'no_hp' => '08123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Kategori
        $kategoriId = DB::table('kategoris')->insertGetId([
            'nama' => 'Alat Pesta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Barang
        $barangId = DB::table('barangs')->insertGetId([
            'kategori_id' => $kategoriId,
            'nama_barang' => 'Tenda Dome',
            'deskripsi' => 'Tenda biru kapasitas 4 orang',
            'stok_total' => 10,
            'stok_tersedia' => 8,
            'kondisi' => 'Baik',
            'lokasi' => 'Gudang Belakang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Peminjaman (SESUAI SPEK ALAN)
        DB::table('peminjamans')->insert([
            'barang_id' => $barangId,
            'warga_id' => 1,
            'marbot_id' => 1,
            'keperluan' => 'Acara pengajian rutin RT 01',
            'jumlah' => 2,
            'kondisi_pinjam' => 'Baik',
            'tgl_pinjam' => Carbon::now()->toDateString(),
            'tgl_rencana_kembali' => Carbon::now()->addDays(3)->toDateString(), // Wajib ada
            'status' => 'Aktif', // Harus sesuai ENUM Alan (Menunggu/Aktif/Selesai/dst)
            'catatan' => 'Mohon dijaga kebersihannya',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}