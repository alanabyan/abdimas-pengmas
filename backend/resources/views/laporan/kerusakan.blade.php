<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kerusakan Inventaris</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #dc2626; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #991b1b; font-size: 18pt; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        /* Header tabel merah karena ini laporan kerusakan/masalah */
        th { background-color: #dc2626; color: white; padding: 10px; text-transform: uppercase; font-size: 9pt; }
        td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        tr:nth-child(even) { background-color: #fef2f2; }
        .status-badge { font-weight: bold; color: #991b1b; }
        .footer { margin-top: 30px; float: right; text-align: center; width: 250px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KERUSAKAN & MASALAH INVENTARIS</h1>
        <p>Sistem SIMBA v2.0 | Periode: {{ $dari }} - {{ $sampai }}</p>
    </div>

    <div class="info">Total Temuan Masalah: <strong>{{ $total }} data</strong></div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Warga</th>
                <th width="20%">Nama Barang</th>
                <th width="15%">Tgl Pinjam</th>
                <th width="15%">Status Akhir</th>
                <th width="25%">Catatan Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->warga->nama_warga ?? '-' }}</td>
                <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                <td>{{ $item->tgl_pinjam }}</td>
                <td class="status-badge">{{ $item->status }}</td>
                <td>{{ $item->kondisi_kembali ?? 'Belum ada catatan' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" align="center">Alhamdulillah, tidak ada laporan kerusakan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Padang, {{ date('d F Y') }}</p>
        <p>Penanggung Jawab Inventaris,</p>
        <div style="height: 60px;"></div>
        <p><strong>( ____________________ )</strong></p>
    </div>
</body>
</html>