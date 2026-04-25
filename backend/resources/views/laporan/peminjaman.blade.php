<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman SIMBA</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 3px double #10b981; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #065f46; font-size: 22pt; }
        .info { margin-bottom: 20px; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #10b981; color: white; padding: 10px; font-size: 10pt; }
        td { border: 1px solid #e5e7eb; padding: 10px; font-size: 9pt; text-align: center; }
        .footer { margin-top: 50px; float: right; text-align: center; width: 200px; }
        .space { height: 70px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIMBA v2.0</h1>
        <p>Laporan Inventaris & Peminjaman Barang Masjid</p>
    </div>

    <div class="info">
        <strong>Periode:</strong> {{ $dari }} s/d {{ $sampai }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Warga</th>
                <th>Barang</th>
                <th>Tgl Pinjam</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->warga->nama_warga ?? 'N/A' }}</td>
                <td>{{ $item->barang->nama_barang ?? 'N/A' }}</td>
                <td>{{ $item->tgl_pinjam }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Padang, {{ date('d F Y') }}</p>
        <p>Mengetahui,</p>
        <div class="space"></div>
        <p><strong>Marbot Utama</strong></p>
    </div>
</body>
</html>