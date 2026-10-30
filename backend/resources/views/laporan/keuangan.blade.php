<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Keuangan</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2937;
            background: #f8fafc;
        }
        .header {
            background: linear-gradient(135deg, #166534 0%, #16a34a 100%);
            padding: 24px 28px 18px;
            color: white;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .subtitle {
            margin-top: 6px;
            font-size: 11px;
            opacity: 0.9;
        }
        .body {
            padding: 20px 28px 28px;
        }
        .stats {
            display: block;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            width: 32%;
            display: inline-block;
            vertical-align: top;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-right: 1.3%;
            margin-bottom: 12px;
        }
        .stat-box:last-child { margin-right: 0; }
        .stat-label {
            display: block;
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        .panel {
            margin-bottom: 20px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .panel-header {
            background: #f9fafb;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        th {
            background: #f3f4f6;
            color: #374151;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .muted {
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-infaq { background: #dcfce7; color: #166534; }
        .badge-qurban { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Keuangan</div>
        <div class="subtitle">Abdimas Pengmas • Periode: {{ $selectedMonth }} • Tanggal cetak: {{ $tanggal }}</div>
    </div>

    <div class="body">
        <div class="stats">
            <div class="stat-box">
                <span class="stat-label">Total Infaq</span>
                <span class="stat-value">Rp {{ number_format($summary['total_infaq'], 0, ',', '.') }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Total Qurban</span>
                <span class="stat-value">Rp {{ number_format($summary['total_qurban'], 0, ',', '.') }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Total Keseluruhan</span>
                <span class="stat-value">Rp {{ number_format($summary['total_keseluruhan'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">Rekap 6 bulan</div>
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Infaq</th>
                        <th>Qurban</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthly as $item)
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td>Rp {{ number_format($item['infaq'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item['qurban'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="panel-header">Transaksi terbaru</div>
            <table>
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Nama</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $item)
                        <tr>
                            <td>
                                <span class="badge {{ $item['type'] === 'qurban' ? 'badge-qurban' : 'badge-infaq' }}">
                                    {{ $item['type'] === 'qurban' ? 'Qurban' : 'Infaq' }}
                                </span>
                            </td>
                            <td>{{ $item['nama'] }}</td>
                            <td>Rp {{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                            <td>{{ $item['status'] }}</td>
                            <td>{{ $item['tanggal'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
