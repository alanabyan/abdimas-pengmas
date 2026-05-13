<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kerusakan - SI MAS RAYYAN</title>

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
            background: #dc2626;
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
            color: #b91c1c;
            text-transform: uppercase;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #7f1d1d;
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
            border: 1px solid #fecaca;
            padding: 10px;
            width: 25%;
            background: #fef2f2;
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
            color: #7f1d1d;
        }

        .stats {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        .stats td {
            width: 33.3%;
            padding: 12px;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .stats-total {
            background: #fef2f2;
        }

        .stats-rusak {
            background: #fff7ed;
        }

        .stats-hilang {
            background: #fee2e2;
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
            color: #b91c1c;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
        }

        .main-table thead th {
            background: #7f1d1d;
            color: white;
            padding: 10px 8px;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #991b1b;
        }

        .main-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }

        .main-table tbody tr:nth-child(even) {
            background: #fef2f2;
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

        .badge-rusak {
            background: #ffedd5;
            color: #c2410c;
        }

        .badge-hilang {
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

        $rusak =
            $peminjamans->where('kondisi_kembali', 'Rusak Ringan')->count() +
            $peminjamans->where('kondisi_kembali', 'Rusak Berat')->count();

        $hilang = $peminjamans->where('kondisi_kembali', 'Hilang')->count();
    @endphp

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:70px; vertical-align:middle;">
                    <img src="{{ public_path('arayyan.png') }}" alt="Logo"
                        style="width:58px; height:58px;">
                </td>

                <td class="header-left">
                    <div class="org">SI MAS RAYYAN</div>

                    <div class="title">
                        Laporan Kerusakan & Kehilangan
                    </div>

                    <div class="subtitle">
                        Data barang inventaris yang rusak atau hilang
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
                <div class="meta-label">Total Temuan</div>

                <div class="meta-value">
                    {{ $total }} Kasus
                </div>
            </td>

            <td>
                <div class="meta-label">Jenis Laporan</div>

                <div class="meta-value">
                    Kerusakan & Kehilangan
                </div>
            </td>

        </tr>
    </table>

    {{-- STATS --}}
    <table class="stats">
        <tr>

            <td class="stats-total">
                <div class="stat-number">{{ $total }}</div>
                <div class="stat-label">Total Kasus</div>
            </td>

            <td class="stats-rusak">
                <div class="stat-number">{{ $rusak }}</div>
                <div class="stat-label">Rusak</div>
            </td>

            <td class="stats-hilang">
                <div class="stat-number">{{ $hilang }}</div>
                <div class="stat-label">Hilang</div>
            </td>

        </tr>
    </table>

    <div class="section-title">
        Data Kerusakan & Kehilangan
    </div>

    {{-- TABLE --}}
    <table class="main-table">

        <thead>
            <tr>
                <th width="4%">#</th>
                <th width="20%">Peminjam</th>
                <th width="24%">Barang</th>
                <th width="10%">Jumlah</th>
                <th width="14%">Tgl Pinjam</th>
                <th width="14%">Tgl Kembali</th>
                <th width="14%">Kondisi</th>
            </tr>
        </thead>

        <tbody>

            @forelse($peminjamans as $key => $item)

                @php
                    $kondisi = $item->kondisi_kembali ?? '-';

                    $badgeClass = match (true) {
                        in_array($kondisi, ['Rusak Ringan', 'Rusak Berat']) => 'badge-rusak',
                        $kondisi === 'Hilang' => 'badge-hilang',
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
                                <span class="badge badge-default">
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
                            {{ $kondisi }}
                        </span>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="empty">
                        Tidak ada data kerusakan atau kehilangan.
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