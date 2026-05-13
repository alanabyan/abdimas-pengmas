<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman - SI MAS RAYYAN</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 20px;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .top-bar {
            height: 6px;
            background: #10b981;
            margin-bottom: 18px;
        }

        .header {
            width: 100%;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
        }

        .header-left {
            width: 70%;
            vertical-align: top;
        }

        .header-right {
            width: 30%;
            text-align: right;
            vertical-align: top;
        }

        .org {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #059669;
            text-transform: uppercase;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #064e3b;
            margin-top: 4px;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
        }

        .print-date {
            font-size: 10px;
            color: #6b7280;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        .meta-table td {
            border: 1px solid #d1d5db;
            padding: 10px;
            width: 25%;
            background: #f9fafb;
        }

        .meta-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 12px;
            font-weight: bold;
            color: #064e3b;
        }

        .stats {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        .stats td {
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .stats-total {
            background: #ecfdf5;
        }

        .stats-aktif {
            background: #eff6ff;
        }

        .stats-selesai {
            background: #f0fdf4;
        }

        .stats-terlambat {
            background: #fef2f2;
        }

        .stat-number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
        }

        .main-table thead th {
            background: #064e3b;
            color: white;
            padding: 10px 8px;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #065f46;
        }

        .main-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }

        .main-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .center {
            text-align: center;
        }

        .nama {
            font-weight: bold;
            color: #111827;
        }

        .sub {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-kategori {
            background: #ecfdf5;
            color: #065f46;
        }

        .badge-aktif {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-selesai {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-terlambat {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-default {
            background: #f3f4f6;
            color: #6b7280;
        }

        .footer {
            margin-top: 28px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
        }

        .footer-left {
            width: 70%;
            vertical-align: bottom;
            font-size: 9px;
            color: #6b7280;
            line-height: 1.5;
        }

        .footer-right {
            width: 30%;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-space {
            height: 60px;
        }

        .signature-name {
            font-weight: bold;
            border-top: 1px solid #374151;
            display: inline-block;
            padding-top: 4px;
            min-width: 140px;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
        }
    </style>
</head>

<body>

    <div class="top-bar"></div>

    @php
        $total = $peminjamans->count();
        $aktif = $peminjamans->where('status', 'Aktif')->count() + $peminjamans->where('status', 'Pinjam')->count();

        $selesai =
            $peminjamans->where('status', 'Selesai')->count() + $peminjamans->where('status', 'Kembali')->count();

        $terlambat = $peminjamans->where('status', 'Terlambat')->count();
    @endphp

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:70px; vertical-align:middle;">
                    <img src="{{ public_path('arayyan.png') }}" alt="Logo" style="width:58px; height:58px;">
                </td>
                <td class="header-left">
                    <div class="org">SI MAS RAYYAN</div>
                    <div class="title">Laporan Peminjaman</div>
                    <div class="subtitle">
                        Riwayat peminjaman barang inventaris masjid
                    </div>
                </td>
                <td class="header-right">
                    <div class="print-date">
                        Dicetak:<br>
                        <strong>{{ $tanggal }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- META --}}
    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Periode Dari</div>
                <div class="meta-value">
                    {{ $dari ? \Carbon\Carbon::parse($dari)->format('d M Y') : '-' }}
                </div>
            </td>

            <td>
                <div class="meta-label">Periode Sampai</div>
                <div class="meta-value">
                    {{ $sampai ? \Carbon\Carbon::parse($sampai)->format('d M Y') : '-' }}
                </div>
            </td>

            <td>
                <div class="meta-label">Total Data</div>
                <div class="meta-value">
                    {{ $total }} Transaksi
                </div>
            </td>

            <td>
                <div class="meta-label">Filter Status</div>
                <div class="meta-value">
                    {{ request('status') ?: 'Semua' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- STATS --}}
    <table class="stats">
        <tr>
            <td class="stats-total">
                <div class="stat-number">{{ $total }}</div>
                <div class="stat-label">Total</div>
            </td>

            <td class="stats-aktif">
                <div class="stat-number">{{ $aktif }}</div>
                <div class="stat-label">Aktif</div>
            </td>

            <td class="stats-selesai">
                <div class="stat-number">{{ $selesai }}</div>
                <div class="stat-label">Selesai</div>
            </td>

            <td class="stats-terlambat">
                <div class="stat-number">{{ $terlambat }}</div>
                <div class="stat-label">Terlambat</div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Data Peminjaman
    </div>

    {{-- TABLE --}}
    <table class="main-table">
        <thead>
            <tr>
                <th width="4%">#</th>
                <th width="20%">Peminjam</th>
                <th width="24%">Barang</th>
                <th width="8%">Jumlah</th>
                <th width="14%">Tgl Pinjam</th>
                <th width="14%">Tgl Kembali</th>
                <th width="16%">Status</th>
            </tr>
        </thead>

        <tbody>

            @forelse($peminjamans as $key => $item)
                @php
                    $status = $item->status ?? '-';

                    $badgeClass = match (true) {
                        in_array($status, ['Aktif', 'Pinjam']) => 'badge-aktif',
                        in_array($status, ['Selesai', 'Kembali']) => 'badge-selesai',
                        $status === 'Terlambat' => 'badge-terlambat',
                        default => 'badge-default',
                    };
                @endphp

                <tr>

                    <td class="center">
                        {{ $key + 1 }}
                    </td>

                    <td>
                        <div class="nama">
                            {{ $item->warga->nama_warga ?? '-' }}
                        </div>

                        @if (!empty($item->warga?->no_hp))
                            <div class="sub">
                                {{ $item->warga->no_hp }}
                            </div>
                        @endif
                    </td>

                    <td>
                        <div class="nama">
                            {{ $item->barang->nama_barang ?? '-' }}
                        </div>

                        @if (!empty($item->barang?->kategori?->nama))
                            <div style="margin-top:4px">
                                <span class="badge badge-kategori">
                                    {{ $item->barang->kategori->nama }}
                                </span>
                            </div>
                        @endif
                    </td>

                    <td class="center">
                        <strong>{{ $item->jumlah ?? '-' }}</strong>
                    </td>

                    <td>
                        {{ $item->tgl_pinjam ? \Carbon\Carbon::parse($item->tgl_pinjam)->format('d M Y') : '-' }}
                    </td>

                    <td>
                        {{ $item->tgl_kembali_aktual ? \Carbon\Carbon::parse($item->tgl_kembali_aktual)->format('d M Y') : '-' }}
                    </td>

                    <td class="center">
                        <span class="badge {{ $badgeClass }}">
                            {{ $status }}
                        </span>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="empty">
                        Tidak ada data peminjaman.
                    </td>
                </tr>
            @endforelse

        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>SI MAS RAYYAN</strong><br>
                    Sistem Informasi Manajemen Masjid<br>
                    Dokumen dibuat otomatis oleh sistem.
                </td>

                <td class="footer-right">
                    <div>
                        {{ now()->locale('id')->isoFormat('D MMMM Y') }}
                    </div>

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        Marbot Utama
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
