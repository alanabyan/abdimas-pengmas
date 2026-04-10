<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\Warga;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Isi data Barang
        Barang::create([
            'nama_barang' => 'Tenda Dome',
            'stok' => 10,
            'kondisi' => 'Baik',
            'deskripsi' => 'Tenda kapasitas 4 orang'
        ]);

        // 2. Isi data Warga (Sesuai id_warga, nama_warga, dkk)
        Warga::create([
            'nama_warga' => 'Budi Santoso',
            'no_hp' => '0812345678',
            'alamat' => 'Blok A No. 12',
            'rt_rw' => '01/02'
        ]);
    }
}