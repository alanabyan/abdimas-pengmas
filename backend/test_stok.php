<?php

// 1. Ambil barang ID 1
$barang = \App\Models\Barang::find(1);
echo "Stok Awal: " . $barang->stok . PHP_EOL;

// 2. Simulasi Request Pinjam 2 unit
$request = new \Illuminate\Http\Request([
    'barang_id' => 1,
    'id_warga'  => 1,
    'jumlah'    => 2,
    'tgl_pinjam' => date('Y-m-d'),
    'tgl_rencana_kembali' => date('Y-m-d', strtotime('+3 days')),
]);

// 3. Panggil Controller lu
echo "Sedang memproses peminjaman..." . PHP_EOL;
app(\App\Http\Controllers\Api\v1\PeminjamanController::class)->store($request);

// 4. Cek Stok Akhir
$barangBaru = \App\Models\Barang::find(1);
echo "Stok Akhir: " . $barangBaru->stok . PHP_EOL;