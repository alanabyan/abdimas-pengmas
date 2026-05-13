<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Marbot;
use App\Models\Warga;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Marbot
        $marbot = Marbot::firstOrCreate(
            ['email' => 'marbot@masjid.com'],
            [
                'nama_marbot'    => 'Marbot Utama',
                'password'       => Hash::make('password123'),
                'aktif'          => true,
                'is_super_admin' => true,
            ]
        );

        // 2. Kategori Default
        $kategoris = [
            ['nama' => 'Elektronik',          'ikon' => 'plug',          'deskripsi' => 'Peralatan elektronik masjid'],
            ['nama' => 'Kebersihan',          'ikon' => 'bucket',        'deskripsi' => 'Alat dan perlengkapan kebersihan'],
            ['nama' => 'Perlengkapan Sholat', 'ikon' => 'prayer',        'deskripsi' => 'Sajadah, mukena, Al-Quran, dll'],
            ['nama' => 'Furnitur',            'ikon' => 'armchair',      'deskripsi' => 'Kursi, meja, lemari'],
            ['nama' => 'Konsumsi & Dapur',    'ikon' => 'tools-kitchen', 'deskripsi' => 'Termos, dispenser, perlengkapan dapur'],
            ['nama' => 'Lain-lain',           'ikon' => 'box',           'deskripsi' => 'Barang yang tidak masuk kategori lain'],
        ];

        foreach ($kategoris as $k) {
            Kategori::firstOrCreate(
                ['nama' => $k['nama']],
                $k
            );
        }

        // 3. Warga
        $warga = Warga::firstOrCreate(
            ['no_hp' => '08123456789'],
            [
                'nama_warga' => 'Budi Santoso',
                'alamat'     => 'RT 01 RW 02',
            ]
        );

        // 4. Kategori untuk Peminjaman
        $kategoriPesta = Kategori::firstOrCreate(
            ['nama' => 'Alat Pesta'],
            [
                'ikon'      => 'confetti',
                'deskripsi' => 'Tenda, kursi, meja, dan perlengkapan acara warga.',
            ]
        );

        // 5. Barang
        $barang = Barang::firstOrCreate(
            ['nama_barang' => 'Tenda Dome'],
            [
                'kategori_id'   => $kategoriPesta->id,
                'deskripsi'     => 'Tenda biru kapasitas 4 orang',
                'stok_total'    => 10,
                'stok_tersedia' => 8,
                'kondisi'       => 'Baik',
                'lokasi'        => 'Gudang Belakang',
            ]
        );

        // 6. Peminjaman
        Peminjaman::firstOrCreate(
            [
                'barang_id' => $barang->id,
                'warga_id'  => $warga->id,
                'status'    => 'Aktif',
            ],
            [
                'marbot_id'            => $marbot->id,
                'keperluan'            => 'Acara pengajian rutin RT 01',
                'jumlah'               => 2,
                'kondisi_pinjam'       => 'Baik',
                'tgl_pinjam'           => Carbon::now()->toDateString(),
                'tgl_rencana_kembali'  => Carbon::now()->addDays(3)->toDateString(),
                'catatan'              => 'Mohon dijaga kebersihannya',
            ]
        );
    }
}
