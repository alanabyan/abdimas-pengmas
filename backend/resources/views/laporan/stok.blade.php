<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #f8fafc;
            padding: 0;
        }

        /* ── Cover header ─────────────────────────────────────── */
        .header-band {
            background: #16a34a;
            padding: 0 28px;
            height: 72px;
            display: block;
        }
        .header-inner {
            display: block;
            width: 100%;
        }
        .header-logo-row {
            padding-top: 12px;
            display: block;
        }
        .header-logo-wrap {
            display: inline-block;
            vertical-align: middle;
            width: 46px;
            height: 46px;
            background: white;
            border-radius: 10px;
            text-align: center;
            padding-top: 2px;
        }
        .header-logo-wrap img {
            width: 42px;
            height: 42px;
            border-radius: 8px;
        }
        .header-text {
            display: inline-block;
            vertical-align: middle;
            margin-left: 12px;
        }
        .header-org {
            font-size: 14px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.3px;
            display: block;
        }
        .header-sub-org {
            font-size: 10px;
            color: rgba(255,255,255,0.80);
            display: block;
            margin-top: 2px;
        }
        .header-right {
            display: inline-block;
            vertical-align: middle;
            float: right;
            text-align: right;
            margin-top: 10px;
        }
        .header-right-title {
            font-size: 15px;
            font-weight: 700;
            color: white;
            display: block;
        }
        .header-right-date {
            font-size: 9.5px;
            color: rgba(255,255,255,0.80);
            display: block;
            margin-top: 3px;
        }

        /* Green accent bar */
        .accent-bar {
            height: 4px;
            background: linear-gradient(to right, #15803d, #4ade80, #15803d);
        }

        /* ── Body wrapper ─────────────────────────────────────── */
        .body-wrap {
            padding: 18px 28px 24px;
        }

        /* ── Grand total cards ────────────────────────────────── */
        .gt-section {
            margin-bottom: 16px;
        }
        .gt-section-label {
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            display: block;
        }
        .gt-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .gt-cell {
            width: 25%;
            padding: 12px 14px;
            border-radius: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .gt-cell--default {
            background: white;
            border: 1.5px solid #e5e7eb;
        }
        .gt-cell--green {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
        }
        .gt-cell--orange {
            background: #fff7ed;
            border: 1.5px solid #fed7aa;
        }
        .gt-cell--blue {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
        }
        .gt-icon {
            font-size: 16px;
            display: block;
            margin-bottom: 4px;
        }
        .gt-val {
            font-size: 24px;
            font-weight: 800;
            display: block;
            line-height: 1;
            color: #111827;
        }
        .gt-cell--green .gt-val  { color: #166534; }
        .gt-cell--orange .gt-val { color: #9a3412; }
        .gt-cell--blue .gt-val   { color: #1e40af; }
        .gt-lbl {
            font-size: 9.5px;
            font-weight: 600;
            display: block;
            margin-top: 4px;
            color: #6b7280;
        }
        .gt-cell--green .gt-lbl  { color: #16a34a; }
        .gt-cell--orange .gt-lbl { color: #ea580c; }
        .gt-cell--blue .gt-lbl   { color: #2563eb; }

        /* ── Section divider ──────────────────────────────────── */
        .section-divider {
            border: none;
            border-top: 1.5px solid #e5e7eb;
            margin: 14px 0 12px;
        }
        .section-heading {
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            display: block;
        }

        /* ── Kategori block ───────────────────────────────────── */
        .kat-block {
            background: white;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .kat-header {
            background: #f9fafb;
            padding: 9px 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        .kat-header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kat-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            vertical-align: middle;
        }
        .kat-name {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            vertical-align: middle;
        }
        .kat-sub {
            font-size: 10px;
            color: #9ca3af;
            vertical-align: middle;
            padding-left: 4px;
        }
        .kat-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9.5px;
            font-weight: 700;
            margin-left: 4px;
        }
        .b-total  { background: #f1f5f9; color: #374151; }
        .b-green  { background: #dcfce7; color: #166534; }
        .b-orange { background: #ffedd5; color: #9a3412; }

        /* ── Stok table ───────────────────────────────────────── */
        .stok-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stok-table thead tr {
            background: #f9fafb;
        }
        .stok-table th {
            padding: 7px 12px;
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .th-c { text-align: center; }
        .stok-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
            color: #374151;
            font-size: 10.5px;
        }
        .stok-table tbody tr:last-child td {
            border-bottom: none;
        }
        .stok-table tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        .td-no   { text-align: center; color: #9ca3af; font-size: 10px; font-weight: 600; }
        .td-name { font-weight: 700; color: #111827; }
        .td-loc  { color: #6b7280; }
        .td-c    { text-align: center; }
        .td-bold { font-weight: 700; color: #111827; }
        .td-green  { font-weight: 700; color: #16a34a; }
        .td-orange { font-weight: 700; color: #ea580c; }

        /* Kondisi badge */
        .k-baik   { background: #dcfce7; color: #15803d; padding: 2px 7px; border-radius: 20px; font-size: 9.5px; font-weight: 700; }
        .k-ringan { background: #fef3c7; color: #b45309; padding: 2px 7px; border-radius: 20px; font-size: 9.5px; font-weight: 700; }
        .k-berat  { background: #fee2e2; color: #b91c1c; padding: 2px 7px; border-radius: 20px; font-size: 9.5px; font-weight: 700; }

        /* Progress bar */
        .pct-wrap { display: block; text-align: center; }
        .pct-bar-bg {
            display: block;
            height: 5px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            width: 80px;
            margin: 0 auto 3px;
        }
        .pct-bar-fill {
            display: block;
            height: 5px;
            border-radius: 99px;
        }
        .pct-fill--high { background: #16a34a; }
        .pct-fill--mid  { background: #f59e0b; }
        .pct-fill--low  { background: #ef4444; }
        .pct-text { font-size: 9.5px; font-weight: 700; }
        .pct-text--high { color: #16a34a; }
        .pct-text--mid  { color: #f59e0b; }
        .pct-text--low  { color: #ef4444; }

        /* ── Footer ───────────────────────────────────────────── */
        .footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1.5px solid #e5e7eb;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-left-cell {
            font-size: 9.5px;
            color: #6b7280;
            vertical-align: middle;
        }
        .footer-left-cell strong { color: #374151; }
        .footer-right-cell {
            text-align: right;
            font-size: 9.5px;
            color: #9ca3af;
            vertical-align: middle;
        }
        .footer-dot {
            width: 7px;
            height: 7px;
            background: #16a34a;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
        }

        /* ── Page break ───────────────────────────────────────── */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ══ HEADER BAND ══ --}}
    <div class="header-band">
        <div class="header-inner">
            <div class="header-logo-row">
                <div class="header-logo-wrap">
                    <img src="data:image/jpeg;base64,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" />
                </div>
                <div class="header-text">
                    <span class="header-org">Masjid AR-RAYYAN</span>
                    <span class="header-sub-org">Sistem Manajemen Inventaris</span>
                </div>
                <div class="header-right">
                    <span class="header-right-title">Laporan Stok Barang</span>
                    <span class="header-right-date">Dicetak: {{ $tanggal }}</span>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div>
    <div class="accent-bar"></div>

    {{-- ══ BODY ══ --}}
    <div class="body-wrap">

        {{-- Grand Total --}}
        <div class="gt-section">
            <span class="gt-section-label">Ringkasan Keseluruhan</span>
            <table class="gt-table">
                <tr>
                    <td class="gt-cell gt-cell--default">
                        <span class="gt-val">{{ $grand_total['stok_total'] }}</span>
                        <span class="gt-lbl">Total Unit Barang</span>
                    </td>
                    <td class="gt-cell gt-cell--green">
                        <span class="gt-val">{{ $grand_total['stok_tersedia'] }}</span>
                        <span class="gt-lbl">Unit Tersedia</span>
                    </td>
                    <td class="gt-cell gt-cell--orange">
                        <span class="gt-val">{{ $grand_total['stok_dipinjam'] }}</span>
                        <span class="gt-lbl">Unit Dipinjam</span>
                    </td>
                    <td class="gt-cell gt-cell--blue">
                        <span class="gt-val">{{ $kategoris->count() }}</span>
                        <span class="gt-lbl">Kategori</span>
                    </td>
                </tr>
            </table>
        </div>

        <hr class="section-divider">
        <span class="section-heading">Detail Stok per Kategori</span>

        {{-- Per Kategori --}}
        @foreach ($kategoris as $kat)
        <div class="kat-block">

            {{-- Kategori header --}}
            <div class="kat-header">
                <table class="kat-header-table">
                    <tr>
                        <td style="vertical-align:middle;">
                            <span class="kat-dot" style="background:{{ $loop->index % 8 == 0 ? '#16a34a' : ($loop->index % 8 == 1 ? '#0891b2' : ($loop->index % 8 == 2 ? '#7c3aed' : ($loop->index % 8 == 3 ? '#db2777' : ($loop->index % 8 == 4 ? '#ea580c' : ($loop->index % 8 == 5 ? '#ca8a04' : ($loop->index % 8 == 6 ? '#059669' : '#2563eb')))))) }};"></span>
                            <span class="kat-name">{{ $kat['kategori'] }}</span>
                            <span class="kat-sub">· {{ count($kat['barangs']) }} Jenis Barang</span>
                        </td>
                        <td style="text-align:right; vertical-align:middle;">
                            <span class="kat-badge b-total">Total: {{ $kat['stok_total'] }}</span>
                            <span class="kat-badge b-green">Tersedia: {{ $kat['stok_tersedia'] }}</span>
                            @if ($kat['stok_dipinjam'] > 0)
                            <span class="kat-badge b-orange">Dipinjam: {{ $kat['stok_dipinjam'] }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Tabel barang --}}
            <table class="stok-table">
                <thead>
                    <tr>
                        <th style="width:28px">#</th>
                        <th>Nama Barang</th>
                        <th>Lokasi</th>
                        <th>Kondisi</th>
                        <th class="th-c">Total</th>
                        <th class="th-c">Tersedia</th>
                        <th class="th-c">Dipinjam</th>
                        <th class="th-c" style="width:110px">Ketersediaan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kat['barangs'] as $i => $barang)
                    @php
                        $pct = $barang->stok_total ? round(($barang->stok_tersedia / $barang->stok_total) * 100) : 0;
                        $fillClass = $pct >= 70 ? 'pct-fill--high' : ($pct >= 30 ? 'pct-fill--mid' : 'pct-fill--low');
                        $textClass = $pct >= 70 ? 'pct-text--high' : ($pct >= 30 ? 'pct-text--mid' : 'pct-text--low');
                        $dipinjam = $barang->stok_total - $barang->stok_tersedia;
                    @endphp
                    <tr>
                        <td class="td-no">{{ $i + 1 }}</td>
                        <td><span class="td-name">{{ $barang->nama_barang }}</span></td>
                        <td class="td-loc">{{ $barang->lokasi ?: '—' }}</td>
                        <td>
                            @if ($barang->kondisi === 'Baik')
                                <span class="k-baik">Baik</span>
                            @elseif ($barang->kondisi === 'Rusak Ringan')
                                <span class="k-ringan">Rusak Ringan</span>
                            @elseif ($barang->kondisi)
                                <span class="k-berat">{{ $barang->kondisi }}</span>
                            @else
                                <span style="color:#9ca3af">—</span>
                            @endif
                        </td>
                        <td class="td-c td-bold">{{ $barang->stok_total }}</td>
                        <td class="td-c td-green">{{ $barang->stok_tersedia }}</td>
                        <td class="td-c td-orange">{{ $dipinjam }}</td>
                        <td class="td-c">
                            <span class="pct-wrap">
                                <span class="pct-bar-bg">
                                    <span class="pct-bar-fill {{ $fillClass }}" style="width:{{ $pct }}%;"></span>
                                </span>
                                <span class="pct-text {{ $textClass }}">{{ $pct }}%</span>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach

        {{-- Footer --}}
        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="footer-left-cell">
                        <span class="footer-dot"></span>
                        Laporan dibuat pada: <strong>{{ $tanggal }}</strong>
                    </td>
                    <td class="footer-right-cell">
                        Data bersumber dari sistem inventaris Masjid AR-RAYYAN
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>