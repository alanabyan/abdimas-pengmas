<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2b28;
            margin: 24px;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 24px;
        }
        .meta {
            font-size: 12px;
            color: #4f5e57;
            margin-bottom: 18px;
        }
        .summary {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            border: 1px solid #e0e6df;
            border-radius: 8px;
            padding: 10px 12px;
            background: #f8faf7;
        }
        .label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #658072;
            margin-bottom: 6px;
        }
        .value {
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #dfe8e1;
            padding: 8px 10px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background: #edf2ea;
        }
        .muted {
            color: #5e6d66;
        }
    </style>
</head>
<body>
    <h1>Laporan Kas Masjid</h1>
    <div class="meta">
        Bulan: {{ $summary['month'] ?? now()->format('Y-m') }}<br>
        Dicetak: {{ $generatedAt ?? now()->translatedFormat('d F Y H:i') }}
    </div>

    <div class="summary">
        <div class="card">
            <span class="label">Total Masuk</span>
            <div class="value">Rp {{ number_format($summary['total_masuk'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <span class="label">Total Keluar</span>
            <div class="value">Rp {{ number_format($summary['total_keluar'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <span class="label">Saldo</span>
            <div class="value">Rp {{ number_format($summary['saldo'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Jenis</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $item)
                <tr>
                    <td>{{ $item->tanggal ? $item->tanggal->translatedFormat('d M Y') : '-' }}</td>
                    <td>{{ $item->judul }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ $item->jenis }}</td>
                    <td>Rp {{ number_format((int) $item->nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">Belum ada transaksi untuk bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
