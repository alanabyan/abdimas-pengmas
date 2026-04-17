<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Marbot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Marbot (Punya Alan, digabung sama punya lu)
        $marbot = Marbot::create([
            'nama_marbot' => 'Marbot Utama',
            'email'       => 'marbot@masjid.com',
            'password'    => Hash::make('password123'),
            'aktif'       => true,
        ]);

        // 2. Kategori Default (Punya Alan)
        $kategoris = [
            ['nama' => 'Elektronik',          'ikon' => 'plug',          'deskripsi' => 'Peralatan elektronik masjid'],
            ['nama' => 'Kebersihan',          'ikon' => 'bucket',        'deskripsi' => 'Alat dan perlengkapan kebersihan'],
            ['nama' => 'Perlengkapan Sholat', 'ikon' => 'prayer',        'deskripsi' => 'Sajadah, mukena, Al-Quran, dll'],
            ['nama' => 'Furnitur',            'ikon' => 'armchair',      'deskripsi' => 'Kursi, meja, lemari'],
            ['nama' => 'Konsumsi & Dapur',    'ikon' => 'tools-kitchen', 'deskripsi' => 'Termos, dispenser, perlengkapan dapur'],
            ['nama' => 'Lain-lain',           'ikon' => 'box',           'deskripsi' => 'Barang yang tidak masuk kategori lain'],
        ];

        foreach ($kategoris as $k) {
            Kategori::create($k);
        }

        // 3. Warga (Punya Lu)
        $wargaId = DB::table('wargas')->insertGetId([
            'nama_warga' => 'Budi Santoso', 
            'alamat' => 'RT 01 RW 02',
            'no_hp' => '08123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Kategori Tambahan buat tes Peminjaman (Punya Lu)
        $kategoriPestaId = DB::table('kategoris')->insertGetId([
            'nama' => 'Alat Pesta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Barang (Punya Lu)
        $barangId = DB::table('barangs')->insertGetId([
            'kategori_id' => $kategoriPestaId,
            'nama_barang' => 'Tenda Dome',
            'deskripsi' => 'Tenda biru kapasitas 4 orang',
            'stok_total' => 10,
            'stok_tersedia' => 8,
            'kondisi' => 'Baik',
            'lokasi' => 'Gudang Belakang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Peminjaman (Punya Lu)
        DB::table('peminjamans')->insert([
            'barang_id' => $barangId,
            'warga_id' => $wargaId,
            'marbot_id' => $marbot->id, // Otomatis ngambil ID dari marbot yang dibikin di atas
            'keperluan' => 'Acara pengajian rutin RT 01',
            'jumlah' => 2,
            'kondisi_pinjam' => 'Baik',
            'tgl_pinjam' => Carbon::now()->toDateString(),
            'tgl_rencana_kembali' => Carbon::now()->addDays(3)->toDateString(),
            'status' => 'Aktif',
            'catatan' => 'Mohon dijaga kebersihannya',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}