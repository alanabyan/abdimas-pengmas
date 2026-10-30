<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\InfaqSubmission;
use App\Models\KasTransaksi;
use App\Models\Kategori;
use App\Models\Marbot;
use App\Models\Peminjaman;
use App\Models\QurbanRegistration;
use App\Models\QurbanSetting;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Marbot
        $marbot = Marbot::where('email', 'marbot@masjid.com')->first();

        if (! $marbot) {
            $marbot = Marbot::firstOrCreate(
                ['email' => 'marbot@masjid.com'],
                [
                    'nama_marbot' => 'Marbot Utama',
                    'password' => bcrypt('password123'),
                    'aktif' => true,
                    'is_super_admin' => true,
                ]
            );
        }

        // 2. Kategori — pakai firstOrCreate semua agar tidak duplikat
        $katPesta = Kategori::firstOrCreate(
            ['nama' => 'Alat Pesta'],
            [
                'ikon' => 'confetti',
                'deskripsi' => 'Tenda, kursi, meja, dan perlengkapan acara warga.',
            ]
        );

        $katElektronik = Kategori::firstOrCreate(
            ['nama' => 'Elektronik'],
            [
                'ikon' => 'plug',
                'deskripsi' => 'Sound system, mic, proyektor, dan genset.',
            ]
        );

        $katIbadah = Kategori::firstOrCreate(
            ['nama' => 'Perlengkapan Ibadah'],
            [
                'ikon' => 'prayer',
                'deskripsi' => 'Karpet, pembatas shof, dan kain penutup.',
            ]
        );

        $katKebersihan = Kategori::firstOrCreate(
            ['nama' => 'Kebersihan'],
            [
                'ikon' => 'bucket',
                'deskripsi' => 'Alat pendukung kebersihan area masjid dan lingkungan.',
            ]
        );

        $katMedis = Kategori::firstOrCreate(
            ['nama' => 'Alat Medis'],
            [
                'ikon' => 'first-aid',
                'deskripsi' => 'Kursi roda dan tandu untuk warga yang sakit.',
            ]
        );

        // 3. Barang — pakai firstOrCreate agar tidak duplikat
        $barang1 = Barang::firstOrCreate(
            ['nama_barang' => 'Kursi Futura Biru'],
            [
                'kategori_id' => $katPesta->id,
                'stok_total' => 100,
                'stok_tersedia' => 80,
                'lokasi' => 'Gudang Belakang',
                'kondisi' => 'Baik',
                'foto_url' => 'barang/kursi.jpg',
                'deskripsi' => 'Kursi besi lipat dengan busa biru',
            ]
        );

        $barang2 = Barang::firstOrCreate(
            ['nama_barang' => 'Mic Wireless Shure'],
            [
                'kategori_id' => $katElektronik->id,
                'stok_total' => 4,
                'stok_tersedia' => 4,
                'lokasi' => 'Lemari Sekretariat',
                'kondisi' => 'Baik',
                'foto_url' => 'barang/mic.jpg',
                'deskripsi' => 'Satu set isi 2 mic tanpa kabel',
            ]
        );

        $barang3 = Barang::firstOrCreate(
            ['nama_barang' => 'Vacuum Cleaner Sharp'],
            [
                'kategori_id' => $katKebersihan->id,
                'stok_total' => 2,
                'stok_tersedia' => 1,
                'lokasi' => 'Ruang Marbot',
                'kondisi' => 'Baik',
                'foto_url' => 'barang/vacuum.jpg',
                'deskripsi' => 'Alat penyedot debu karpet masjid',
            ]
        );

        $barang4 = Barang::firstOrCreate(
            ['nama_barang' => 'Tenda Dome 4x6'],
            [
                'kategori_id' => $katPesta->id,
                'stok_total' => 2,
                'stok_tersedia' => 2,
                'lokasi' => 'Samping Masjid',
                'kondisi' => 'Baik',
                'foto_url' => 'barang/tenda.jpg',
                'deskripsi' => 'Tenda kapasitas besar untuk acara warga',
            ]
        );

        $barang5 = Barang::firstOrCreate(
            ['nama_barang' => 'Genset Honda 2500W'],
            [
                'kategori_id' => $katElektronik->id,
                'stok_total' => 1,
                'stok_tersedia' => 1,
                'lokasi' => 'Parkiran Dalam',
                'kondisi' => 'Baik',
                'foto_url' => 'barang/genset.jpg',
                'deskripsi' => 'Pembangkit listrik cadangan',
            ]
        );

        $barang6 = Barang::firstOrCreate(
            ['nama_barang' => 'Kursi Roda'],
            [
                'kategori_id' => $katMedis->id,
                'stok_total' => 1,
                'stok_tersedia' => 1,
                'lokasi' => 'Ruang Posyandu',
                'kondisi' => 'Baik',
                'foto_url' => 'barang/kursiroda.jpg',
                'deskripsi' => 'Kursi roda untuk warga lansia/sakit',
            ]
        );

        $barang7 = Barang::firstOrCreate(
            ['nama_barang' => 'Speaker Aktif Polytron'],
            [
                'kategori_id' => $katElektronik->id,
                'stok_total' => 2,
                'stok_tersedia' => 2,
                'lokasi' => 'Gudang Depan',
                'kondisi' => 'Rusak Ringan',
                'foto_url' => 'barang/speaker.jpg',
                'deskripsi' => 'Speaker aktif untuk pengajian kecil',
            ]
        );

        // 4. Warga — pakai firstOrCreate berdasarkan no_hp (unik)
        $warga1 = Warga::firstOrCreate(
            ['no_hp' => '081234567890'],
            [
                'nama_warga' => 'H. Ahmad Subarjo',
                'alamat' => 'Jl. Masjid No. 12',
                'rt_rw' => '001/005',
            ]
        );

        $warga2 = Warga::firstOrCreate(
            ['no_hp' => '085711223344'],
            [
                'nama_warga' => 'Ibu Siti Aminah',
                'alamat' => 'Gang Damai No. 5',
                'rt_rw' => '003/005',
            ]
        );

        $warga3 = Warga::firstOrCreate(
            ['no_hp' => '081399887766'],
            [
                'nama_warga' => 'Bpk. Bambang Heru',
                'alamat' => 'Perumahan Ar-Rayyan Blok C1',
                'rt_rw' => '002/005',
            ]
        );

        $warga4 = Warga::firstOrCreate(
            ['no_hp' => '089900112233'],
            [
                'nama_warga' => 'Rudi Setiawan',
                'alamat' => 'Kostan Hijau No. 10',
                'rt_rw' => '002/005',
            ]
        );

        // 5. Peminjaman — pakai firstOrCreate agar tidak duplikat
        Peminjaman::firstOrCreate(
            [
                'warga_id' => $warga1->id,
                'barang_id' => $barang1->id,
                'status' => 'Aktif',
            ],
            [
                'jumlah' => 20,
                'tgl_pinjam' => Carbon::now()->subDays(2)->toDateString(),
                'tgl_rencana_kembali' => Carbon::now()->addDays(2)->toDateString(),
                'keperluan' => 'Acara tasyakuran warga',
                'kondisi_pinjam' => 'Baik',
                'marbot_id' => $marbot->id,
            ]
        );

        Peminjaman::firstOrCreate(
            [
                'warga_id' => $warga2->id,
                'barang_id' => $barang3->id,
                'status' => 'Aktif',
            ],
            [
                'jumlah' => 1,
                'tgl_pinjam' => Carbon::now()->subDays(5)->toDateString(),
                'tgl_rencana_kembali' => Carbon::now()->subDays(1)->toDateString(),
                'keperluan' => 'Pembersihan karpet mushola RT',
                'kondisi_pinjam' => 'Baik',
                'marbot_id' => $marbot->id,
            ]
        );

        Peminjaman::firstOrCreate(
            [
                'warga_id' => $warga4->id,
                'barang_id' => $barang2->id,
                'status' => 'Rusak/Hilang',
            ],
            [
                'jumlah' => 1,
                'tgl_pinjam' => Carbon::now()->subDays(10)->toDateString(),
                'tgl_rencana_kembali' => Carbon::now()->subDays(7)->toDateString(),
                'keperluan' => 'Latihan hadroh',
                'kondisi_pinjam' => 'Baik',
                'marbot_id' => $marbot->id,
            ]
        );

        // 6. Dummy finance and qurban entries for a complete demo experience
        QurbanSetting::updateOrCreate(
            ['is_active' => true],
            [
                'periode' => 'periode-' . now()->format('Y-m'),
                'harga_sapi' => 24000000,
                'kuota_total' => 7,
                'is_active' => true,
            ]
        );

        $qurbanData = [
            ['nama' => 'Ahmad Yusuf', 'telepon' => '0812001001', 'email' => 'ahmad@example.test', 'jumlah' => 3428572, 'bagian' => '1/7', 'kuota_total' => 7, 'kuota_tersedia' => 6, 'catatan' => 'Bergabung untuk qurban', 'status' => 'lunas', 'created_at' => now()->subDays(12), 'updated_at' => now()->subDays(12)],
            ['nama' => 'Budi Santoso', 'telepon' => '0812001002', 'email' => 'budi@example.test', 'jumlah' => 3428572, 'bagian' => '1/7', 'kuota_total' => 7, 'kuota_tersedia' => 6, 'catatan' => 'Ikut paket qurban', 'status' => 'terkonfirmasi', 'created_at' => now()->subDays(9), 'updated_at' => now()->subDays(9)],
            ['nama' => 'Siti Nurhaliza', 'telepon' => '0812001003', 'email' => 'siti@example.test', 'jumlah' => 3428572, 'bagian' => '1/7', 'kuota_total' => 7, 'kuota_tersedia' => 6, 'catatan' => 'Qurban keluarga', 'status' => 'terdaftar', 'created_at' => now()->subDays(6), 'updated_at' => now()->subDays(6)],
        ];

        foreach ($qurbanData as $row) {
            QurbanRegistration::firstOrCreate(
                ['nama' => $row['nama'], 'telepon' => $row['telepon']],
                $row
            );
        }

        $infaqData = [
            ['nama' => 'H. Miftahudin', 'telepon' => '0813100001', 'email' => 'miftah@example.test', 'jumlah' => 300000, 'jenis' => 'infaq', 'catatan' => 'Donasi penguatan program masjid', 'status' => 'terkonfirmasi', 'created_at' => now()->subDays(7), 'updated_at' => now()->subDays(7)],
            ['nama' => 'Syarifah', 'telepon' => '0813100002', 'email' => 'syarifah@example.test', 'jumlah' => 150000, 'jenis' => 'sedekah', 'catatan' => 'Sedekah untuk kebersihan masjid', 'status' => 'terkonfirmasi', 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
            ['nama' => 'Rizal Pratama', 'telepon' => '0813100003', 'email' => 'rizal@example.test', 'jumlah' => 500000, 'jenis' => 'infaq', 'catatan' => 'Donasi pembelian sembako', 'status' => 'menunggu_konfirmasi', 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)],
        ];

        foreach ($infaqData as $row) {
            InfaqSubmission::firstOrCreate(
                ['nama' => $row['nama'], 'telepon' => $row['telepon']],
                $row
            );
        }

        $kasData = [
            ['judul' => 'Donasi Jumat', 'jenis' => 'masuk', 'kategori' => 'infaq', 'nominal' => 1200000, 'keterangan' => 'Infaq jamaah Jumat', 'tanggal' => now()->subDays(2)->toDateString(), 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['judul' => 'Sumbangan qurban', 'jenis' => 'masuk', 'kategori' => 'qurban', 'nominal' => 5000000, 'keterangan' => 'Pendaftaran qurban', 'tanggal' => now()->subDays(6)->toDateString(), 'created_at' => now()->subDays(6), 'updated_at' => now()->subDays(6)],
            ['judul' => 'Pembelian gas', 'jenis' => 'keluar', 'kategori' => 'operasional', 'nominal' => 350000, 'keterangan' => 'Gas dapur masjid', 'tanggal' => now()->subDays(3)->toDateString(), 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['judul' => 'Perbaikan sound system', 'jenis' => 'keluar', 'kategori' => 'perawatan', 'nominal' => 625000, 'keterangan' => 'Perbaikan mic dan speaker', 'tanggal' => now()->subDays(8)->toDateString(), 'created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)],
        ];

        foreach ($kasData as $row) {
            KasTransaksi::firstOrCreate(
                ['judul' => $row['judul'], 'tanggal' => $row['tanggal']],
                $row
            );
        }
    }
}
