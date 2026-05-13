<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\Warga;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Marbot;
use Carbon\Carbon;


class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Marbot
        $marbot = Marbot::where('email', 'marbot@masjid.com')->firstOrFail();

        // 2. Kategori — pakai firstOrCreate semua agar tidak duplikat
        $katPesta = Kategori::firstOrCreate(
            ['nama' => 'Alat Pesta'],
            [
                'ikon'      => 'confetti',
                'deskripsi' => 'Tenda, kursi, meja, dan perlengkapan acara warga.',
            ]
        );

        $katElektronik = Kategori::firstOrCreate(
            ['nama' => 'Elektronik'],
            [
                'ikon'      => 'plug',
                'deskripsi' => 'Sound system, mic, proyektor, dan genset.',
            ]
        );

        $katIbadah = Kategori::firstOrCreate(
            ['nama' => 'Perlengkapan Ibadah'],
            [
                'ikon'      => 'prayer',
                'deskripsi' => 'Karpet, pembatas shof, dan kain penutup.',
            ]
        );

        $katKebersihan = Kategori::firstOrCreate(
            ['nama' => 'Kebersihan'],
            [
                'ikon'      => 'bucket',
                'deskripsi' => 'Alat pendukung kebersihan area masjid dan lingkungan.',
            ]
        );

        $katMedis = Kategori::firstOrCreate(
            ['nama' => 'Alat Medis'],
            [
                'ikon'      => 'first-aid',
                'deskripsi' => 'Kursi roda dan tandu untuk warga yang sakit.',
            ]
        );

        // 3. Barang — pakai firstOrCreate agar tidak duplikat
        $barang1 = Barang::firstOrCreate(
            ['nama_barang' => 'Kursi Futura Biru'],
            [
                'kategori_id'   => $katPesta->id,
                'stok_total'    => 100,
                'stok_tersedia' => 80,
                'lokasi'        => 'Gudang Belakang',
                'kondisi'       => 'Baik',
                'foto_url'      => 'barang/kursi.jpg',
                'deskripsi'     => 'Kursi besi lipat dengan busa biru',
            ]
        );

        $barang2 = Barang::firstOrCreate(
            ['nama_barang' => 'Mic Wireless Shure'],
            [
                'kategori_id'   => $katElektronik->id,
                'stok_total'    => 4,
                'stok_tersedia' => 4,
                'lokasi'        => 'Lemari Sekretariat',
                'kondisi'       => 'Baik',
                'foto_url'      => 'barang/mic.jpg',
                'deskripsi'     => 'Satu set isi 2 mic tanpa kabel',
            ]
        );

        $barang3 = Barang::firstOrCreate(
            ['nama_barang' => 'Vacuum Cleaner Sharp'],
            [
                'kategori_id'   => $katKebersihan->id,
                'stok_total'    => 2,
                'stok_tersedia' => 1,
                'lokasi'        => 'Ruang Marbot',
                'kondisi'       => 'Baik',
                'foto_url'      => 'barang/vacuum.jpg',
                'deskripsi'     => 'Alat penyedot debu karpet masjid',
            ]
        );

        $barang4 = Barang::firstOrCreate(
            ['nama_barang' => 'Tenda Dome 4x6'],
            [
                'kategori_id'   => $katPesta->id,
                'stok_total'    => 2,
                'stok_tersedia' => 2,
                'lokasi'        => 'Samping Masjid',
                'kondisi'       => 'Baik',
                'foto_url'      => 'barang/tenda.jpg',
                'deskripsi'     => 'Tenda kapasitas besar untuk acara warga',
            ]
        );

        $barang5 = Barang::firstOrCreate(
            ['nama_barang' => 'Genset Honda 2500W'],
            [
                'kategori_id'   => $katElektronik->id,
                'stok_total'    => 1,
                'stok_tersedia' => 1,
                'lokasi'        => 'Parkiran Dalam',
                'kondisi'       => 'Baik',
                'foto_url'      => 'barang/genset.jpg',
                'deskripsi'     => 'Pembangkit listrik cadangan',
            ]
        );

        $barang6 = Barang::firstOrCreate(
            ['nama_barang' => 'Kursi Roda'],
            [
                'kategori_id'   => $katMedis->id,
                'stok_total'    => 1,
                'stok_tersedia' => 1,
                'lokasi'        => 'Ruang Posyandu',
                'kondisi'       => 'Baik',
                'foto_url'      => 'barang/kursiroda.jpg',
                'deskripsi'     => 'Kursi roda untuk warga lansia/sakit',
            ]
        );

        $barang7 = Barang::firstOrCreate(
            ['nama_barang' => 'Speaker Aktif Polytron'],
            [
                'kategori_id'   => $katElektronik->id,
                'stok_total'    => 2,
                'stok_tersedia' => 2,
                'lokasi'        => 'Gudang Depan',
                'kondisi'       => 'Rusak Ringan',
                'foto_url'      => 'barang/speaker.jpg',
                'deskripsi'     => 'Speaker aktif untuk pengajian kecil',
            ]
        );

        // 4. Warga — pakai firstOrCreate berdasarkan no_hp (unik)
        $warga1 = Warga::firstOrCreate(
            ['no_hp' => '081234567890'],
            [
                'nama_warga' => 'H. Ahmad Subarjo',
                'alamat'     => 'Jl. Masjid No. 12',
                'rt_rw'      => '001/005',
            ]
        );

        $warga2 = Warga::firstOrCreate(
            ['no_hp' => '085711223344'],
            [
                'nama_warga' => 'Ibu Siti Aminah',
                'alamat'     => 'Gang Damai No. 5',
                'rt_rw'      => '003/005',
            ]
        );

        $warga3 = Warga::firstOrCreate(
            ['no_hp' => '081399887766'],
            [
                'nama_warga' => 'Bpk. Bambang Heru',
                'alamat'     => 'Perumahan Ar-Rayyan Blok C1',
                'rt_rw'      => '002/005',
            ]
        );

        $warga4 = Warga::firstOrCreate(
            ['no_hp' => '089900112233'],
            [
                'nama_warga' => 'Rudi Setiawan',
                'alamat'     => 'Kostan Hijau No. 10',
                'rt_rw'      => '002/005',
            ]
        );

        // 5. Peminjaman — pakai firstOrCreate agar tidak duplikat
        Peminjaman::firstOrCreate(
            [
                'warga_id'  => $warga1->id,
                'barang_id' => $barang1->id,
                'status'    => 'Aktif',
            ],
            [
                'jumlah'               => 20,
                'tgl_pinjam'           => Carbon::now()->subDays(2)->toDateString(),
                'tgl_rencana_kembali'  => Carbon::now()->addDays(2)->toDateString(),
                'keperluan'            => 'Acara tasyakuran warga',
                'kondisi_pinjam'       => 'Baik',
                'marbot_id'            => $marbot->id,
            ]
        );

        Peminjaman::firstOrCreate(
            [
                'warga_id'  => $warga2->id,
                'barang_id' => $barang3->id,
                'status'    => 'Aktif',
            ],
            [
                'jumlah'               => 1,
                'tgl_pinjam'           => Carbon::now()->subDays(5)->toDateString(),
                'tgl_rencana_kembali'  => Carbon::now()->subDays(1)->toDateString(),
                'keperluan'            => 'Pembersihan karpet mushola RT',
                'kondisi_pinjam'       => 'Baik',
                'marbot_id'            => $marbot->id,
            ]
        );

        Peminjaman::firstOrCreate(
            [
                'warga_id'  => $warga4->id,
                'barang_id' => $barang2->id,
                'status'    => 'Rusak/Hilang',
            ],
            [
                'jumlah'               => 1,
                'tgl_pinjam'           => Carbon::now()->subDays(10)->toDateString(),
                'tgl_rencana_kembali'  => Carbon::now()->subDays(7)->toDateString(),
                'keperluan'            => 'Latihan hadroh',
                'kondisi_pinjam'       => 'Baik',
                'marbot_id'            => $marbot->id,
            ]
        );
    }
}
