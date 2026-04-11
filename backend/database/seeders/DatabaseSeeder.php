<?php

namespace Database\Seeders;
use App\Models\Kategori;
use App\Models\Marbot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun marbot pertama (default)
        Marbot::create([
            'nama_marbot'     => 'Marbot Utama',
            'email'    => 'marbot@masjid.com',
            'password' => Hash::make('password123'),
            'aktif'    => true,
        ]);

        // Kategori default
        $kategoris = [
            ['nama' => 'Elektronik',          'ikon' => 'plug',        'deskripsi' => 'Peralatan elektronik masjid'],
            ['nama' => 'Kebersihan',           'ikon' => 'bucket',      'deskripsi' => 'Alat dan perlengkapan kebersihan'],
            ['nama' => 'Perlengkapan Sholat',  'ikon' => 'prayer',      'deskripsi' => 'Sajadah, mukena, Al-Quran, dll'],
            ['nama' => 'Furnitur',             'ikon' => 'armchair',    'deskripsi' => 'Kursi, meja, lemari'],
            ['nama' => 'Konsumsi & Dapur',     'ikon' => 'tools-kitchen', 'deskripsi' => 'Termos, dispenser, perlengkapan dapur'],
            ['nama' => 'Lain-lain',            'ikon' => 'box',         'deskripsi' => 'Barang yang tidak masuk kategori lain'],
        ];

        foreach ($kategoris as $k) {
            Kategori::create($k);
        }
    }
}
