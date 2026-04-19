<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #212529;
        }

        /* ── HEADER ── */
        .pdf-header {
            border-bottom: 2px solid #212529;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .pdf-header .company-name {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .pdf-header .company-sub {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .pdf-header .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
        }

        /* ── FILTER INFO ── */
        .filter-info {
            margin-bottom: 14px;
            padding: 8px 10px;
            background-color: #f1f3f5;
            border-left: 3px solid #495057;
            font-size: 10px;
        }

        /* ── TABLE ── */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .detail-table th {
            background-color: #212529;
            color: #fff;
            border: 1px solid #373d44;
            padding: 6px 7px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        .detail-table td {
            border: 1px solid #dee2e6;
            padding: 5px 7px;
            font-size: 10px;
        }
        .detail-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* ── BADGE ── */
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-masuk  { background-color: #d4edda; color: #155724; }
        .badge-keluar { background-color: #f8d7da; color: #721c24; }

        /* ── SUMMARY ── */
        .summary-section {
            margin-top: 10px;
            padding: 12px 15px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .summary-section .summary-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ced4da;
            padding-bottom: 5px;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-grid td {
            width: 25%;
            text-align: center;
            padding: 6px 4px;
        }
        .summary-label { font-size: 9px; color: #6c757d; margin-bottom: 3px; }
        .summary-value { font-size: 13px; font-weight: bold; }

        /* ── FOOTER ── */
        .pdf-footer {
            margin-top: 24px;
            border-top: 1px solid #ced4da;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        /* ── UTILS ── */
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-green  { color: #28a745; }
        .text-red    { color: #dc3545; }
        .page-break  { page-break-after: always; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="pdf-header">
        <div class="company-name">PT MIGAS FINANCE</div>
        <div class="company-sub">Sistem Manajemen Keuangan</div>
        <div class="report-title">{{ $title ?? 'Laporan' }}</div>
    </div>

    {{-- KONTEN --}}
    @yield('content')

    {{-- FOOTER --}}
    <div class="pdf-footer">
        <p>Dokumen ini dicetak otomatis oleh Sistem PT Migas Finance</p>
        <p>Tanggal Cetak: {{ $date ?? now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>