<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman</title>
</head>
<body>
    <h2>Laporan Peminjaman</h2>
    <p>Total: {{ $total }}</p>

    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>No</th>
                <th>Warga</th>
                <th>Barang</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->warga->nama_warga ?? '-' }}</td>
                    <td>{{ $row->barang->nama_barang ?? '-' }}</td>
                    <td>{{ $row->tgl_pinjam }}</td>
                    <td>{{ $row->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>