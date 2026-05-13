<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\Warga;
use App\Models\Kategori; // Pastikan model Kategori ada
use App\Models\Peminjaman; // Pastikan model Peminjaman ada
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Isi data Kategori (Agar barang punya relasi yang bener)
        $katPesta = Kategori::create([
            'nama' => 'Alat Pesta',
            'deskripsi' => 'Tenda, kursi, meja, dan perlengkapan acara warga.'
        ]);

        $katElektronik = Kategori::create([
            'nama' => 'Elektronik',
            'deskripsi' => 'Sound system, mic, proyektor, dan genset.'
        ]);

        $katIbadah = Kategori::create([
            'nama' => 'Perlengkapan Ibadah',
            'deskripsi' => 'Karpet, pembatas shof, dan kain penutup.'
        ]);

        // --- TAMBAHAN KATEGORI BARU ---
        $katKebersihan = Kategori::create([
            'nama' => 'Alat Kebersihan',
            'deskripsi' => 'Alat pendukung kebersihan area masjid dan lingkungan.'
        ]);

        $katMedis = Kategori::create([
            'nama' => 'Alat Medis',
            'deskripsi' => 'Kursi roda dan tandu untuk warga yang sakit.'
        ]);

        // 2. Isi data Barang
        $barang1 = Barang::create([
            'nama_barang' => 'Kursi Futura Biru',
            'kategori_id' => $katPesta->id,
            'stok_total' => 100,
            'stok_tersedia' => 80, // Anggap 20 lagi dipinjam
            'lokasi' => 'Gudang Belakang',
            'kondisi' => 'Baik',
            'foto' => 'barang/kursi.jpg',
            'deskripsi' => 'Kursi besi lipat dengan busa biru'
        ]);

        $barang2 = Barang::create([
            'nama_barang' => 'Mic Wireless Shure',
            'kategori_id' => $katElektronik->id,
            'stok_total' => 4,
            'stok_tersedia' => 4,
            'lokasi' => 'Lemari Sekretariat',
            'kondisi' => 'Baik',
            'foto'=> 'barang/mic.jpg',
            'deskripsi' => 'Satu set isi 2 mic tanpa kabel'
        ]);

        $barang3 = Barang::create([
            'nama_barang' => 'Vacuum Cleaner Sharp',
            'kategori_id' => $katKebersihan->id, // Diubah agar dinamis ke kategori kebersihan
            'stok_total' => 2,
            'stok_tersedia' => 1,
            'lokasi' => 'Ruang Marbot',
            'kondisi' => 'Baik',
            'foto' => 'barang/vacuum.jpg',
            'deskripsi' => 'Alat penyedot debu karpet masjid'
        ]);

        // --- TAMBAHAN BARANG BARU ---
        $barang4 = Barang::create([
            'nama_barang' => 'Tenda Dome 4x6',
            'kategori_id' => $katPesta->id,
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'lokasi' => 'Samping Masjid',
            'kondisi' => 'Baik',
            'foto' => 'barang/tenda.jpg',
            'deskripsi' => 'Tenda kapasitas besar untuk acara warga'
        ]);

        $barang5 = Barang::create([
            'nama_barang' => 'Genset Honda 2500W',
            'kategori_id' => $katElektronik->id,
            'stok_total' => 1,
            'stok_tersedia' => 1,
            'lokasi' => 'Parkiran Dalam',
            'kondisi' => 'Baik',
            'foto' => 'barang/genset.jpg',
            'deskripsi' => 'Pembangkit listrik cadangan'
        ]);

        $barang6 = Barang::create([
            'nama_barang' => 'Kursi Roda',
            'kategori_id' => $katMedis->id,
            'stok_total' => 1,
            'stok_tersedia' => 1,
            'lokasi' => 'Ruang Posyandu',
            'kondisi' => 'Baik',
            'foto' => 'barang/kursiroda.jpg',
            'deskripsi' => 'Kursi roda untuk warga lansia/sakit'
        ]);

        $barang7 = Barang::create([
            'nama_barang' => 'Speaker Aktif Polytron',
            'kategori_id' => $katElektronik->id,
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'lokasi' => 'Gudang Depan',
            'kondisi' => 'Rusak Ringan',
            'foto' => 'barang/speaker.jpg',
            'deskripsi' => 'Speaker aktif untuk pengajian kecil'
        ]);

        // 3. Isi data Warga
        $warga1 = Warga::create([
            'nama_warga' => 'H. Ahmad Subarjo',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Masjid No. 12',
            'rt_rw' => '001/005'
        ]);

        $warga2 = Warga::create([
            'nama_warga' => 'Ibu Siti Aminah',
            'no_hp' => '085711223344',
            'alamat' => 'Gang Damai No. 5',
            'rt_rw' => '003/005'
        ]);

        // --- TAMBAHAN WARGA BARU ---
        $warga3 = Warga::create([
            'nama_warga' => 'Bpk. Bambang Heru',
            'no_hp' => '081399887766',
            'alamat' => 'Perumahan Ar-Rayyan Blok C1',
            'rt_rw' => '002/005'
        ]);

        $warga4 = Warga::create([
            'nama_warga' => 'Rudi Setiawan',
            'no_hp' => '089900112233',
            'alamat' => 'Kostan Hijau No. 10',
            'rt_rw' => '002/005'
        ]);

        // 4. Tambahan: Data Peminjaman Aktif & Telat (Opsional biar Dashboard Lu rame)
        if (class_exists('App\Models\Peminjaman')) {
            Peminjaman::create([
                'warga_id' => $warga1->id,
                'barang_id' => $barang1->id,
                'jumlah' => 20,
                'tgl_pinjam' => Carbon::now()->subDays(2),
                'tgl_rencana_kembali' => Carbon::now()->addDays(2),
                'keperluan' => 'Acara tasyakuran warga',
                'status' => 'Aktif',
                'kondisi_pinjam' => 'Baik',
                'marbot_id' => 1
            ]);

            Peminjaman::create([
                'warga_id' => $warga2->id,
                'barang_id' => $barang3->id,
                'jumlah' => 1,
                'tgl_pinjam' => Carbon::now()->subDays(5),
                'tgl_rencana_kembali' => Carbon::now()->subDays(1), // H-1 (Biar statusnya TELAT)
                'keperluan' => 'Pembersihan karpet mushola RT',
                'status' => 'Aktif',
                'kondisi_pinjam' => 'Baik',
                'marbot_id' => 1
            ]);
            
            // --- TAMBAHAN SKENARIO RUSAK/BATAL ---
            Peminjaman::create([
                'warga_id' => $warga4->id,
                'barang_id' => $barang2->id,
                'jumlah' => 1,
                'tgl_pinjam' => Carbon::now()->subDays(10),
                'tgl_rencana_kembali' => Carbon::now()->subDays(7),
                'keperluan' => 'Latihan hadroh',
                'status' => 'Rusak/Hilang',
                'kondisi_pinjam' => 'Baik',
                'marbot_id' => 1
            ]);
        }
    }
}