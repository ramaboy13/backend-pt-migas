<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1a1a2e;
        }

        /* ── HEADER ── */
        .pdf-header {
            background-color: #4B70A9;
            color: #ffffff;
            padding: 14px 18px;
            margin-bottom: 0;
        }

        .pdf-header-inner {
            display: table;
            width: 100%;
        }

        .pdf-header-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .pdf-header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .company-sub {
            font-size: 9px;
            color: #c8d8f0;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        .report-title-bar {
            background-color: #3a5a8a;
            color: #e8f0fb;
            padding: 7px 18px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin-bottom: 14px;
            border-bottom: 3px solid #2d4a72;
        }

        .header-badge {
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 9px;
            color: #e0eaf8;
            display: inline-block;
        }

        /* ── FILTER INFO ── */
        .filter-wrap {
            margin-bottom: 14px;
            border: 1px solid #c5d5e8;
            border-radius: 4px;
            overflow: hidden;
        }

        .filter-header {
            background-color: #4B70A9;
            color: #ffffff;
            padding: 5px 12px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .filter-body {
            background-color: #f0f5fb;
            padding: 8px 12px;
            font-size: 10px;
            color: #2c3e5a;
        }

        .filter-body strong {
            color: #4B70A9;
        }

        /* ── TABLE ── */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .detail-table th {
            background-color: #4B70A9;
            color: #fff;
            border: 1px solid #3a5a8a;
            padding: 7px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }

        .detail-table td {
            border: 1px solid #d0dce8;
            padding: 6px 8px;
            font-size: 10px;
        }

        .detail-table tbody tr:nth-child(even) {
            background-color: #f0f5fb;
        }

        .detail-table tbody tr:hover {
            background-color: #e2ecf8;
        }

        /* ── BADGE ── */
        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .badge-masuk {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #b8dfc4;
        }

        .badge-keluar {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b8bc;
        }

        .badge-debit {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #b8dfc4;
        }

        .badge-kredit {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b8bc;
        }

        /* ── SUMMARY ── */
        .summary-section {
            margin-top: 10px;
            border: 1px solid #c5d5e8;
            border-radius: 4px;
            overflow: hidden;
        }

        .summary-header {
            background-color: #4B70A9;
            color: #ffffff;
            padding: 6px 14px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .summary-body {
            background-color: #f0f5fb;
            padding: 10px 14px;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-grid td {
            text-align: center;
            padding: 8px 6px;
            vertical-align: top;
        }

        .summary-card {
            background-color: #ffffff;
            border: 1px solid #c5d5e8;
            border-radius: 4px;
            padding: 8px 6px;
        }

        .summary-label {
            font-size: 9px;
            color: #5a7a9a;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #1a2a3a;
        }

        .summary-divider {
            width: 30px;
            height: 2px;
            background-color: #4B70A9;
            margin: 4px auto 0;
        }

        /* ── FOOTER ── */
        .pdf-footer {
            margin-top: 20px;
            border-top: 2px solid #4B70A9;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #5a7a9a;
        }

        /* ── UTILS ── */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-green {
            color: #1a7a3a;
        }

        .text-red {
            color: #c0392b;
        }

        .text-blue {
            color: #4B70A9;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="pdf-header">
        <div class="pdf-header-inner">
            <div class="pdf-header-left">
                <div class="company-name">PT MIGAS FINANCE</div>
                <div class="company-sub">Sistem Manajemen Keuangan &nbsp;·&nbsp; Laporan Resmi Perusahaan</div>
            </div>
            <div class="pdf-header-right">
                <div class="header-badge">
                    Dicetak:
                    {{ isset($date) ? \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $date)->format('d M Y') : now()->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>
    <div class="report-title-bar">&#9658; {{ $title ?? 'Laporan' }}</div>

    {{-- KONTEN --}}
    @yield('content')

    {{-- FOOTER --}}
    <div class="pdf-footer">
        <p>&#169; PT Migas Finance &nbsp;·&nbsp; Dokumen ini dicetak otomatis oleh Sistem Manajemen Keuangan</p>
        <p style="margin-top:2px;">Tanggal Cetak: {{ $date ?? now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>

</html>
