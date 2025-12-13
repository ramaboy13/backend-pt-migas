<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
        }
        .filter-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .detail-table th {
            background-color: #343a40;
            color: white;
            border: 1px solid #454d55;
            padding: 6px;
            text-align: left;
            font-weight: bold;
        }
        .detail-table td {
            border: 1px solid #dee2e6;
            padding: 6px;
        }
        .detail-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .summary-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
        }
        .summary-label {
            font-size: 10px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #212529;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        .status-in {
            background-color: #d4edda;
            color: #155724;
        }
        .status-out {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT MIGAS FINANCE</div>
        <div style="font-size: 12px; color: #666;">Laporan Transaksi Operasional</div>
    </div>

    <div class="report-title">{{ $title }}</div>

    <div class="filter-info">
        <strong>Periode Laporan:</strong> {{ $date }}<br>
        <strong>Filter yang diterapkan:</strong>
        @if(!empty($filters))
            @foreach($filters as $key => $value)
                @if($value)
                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                    @if(!$loop->last) | @endif
                @endif
            @endforeach
        @else
            Semua Transaksi
        @endif
    </div>

    <table class="detail-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Ref</th>
                <th>Jenis</th>
                <th>Pangkalan</th>
                <th>Tabung</th>
                <th>Keterangan</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Total</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaksi)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $transaksi->no_ref }}</td>
                    <td>
                        <span class="status-badge {{ $transaksi->is_in ? 'status-in' : 'status-out' }}">
                            {{ $transaksi->is_in ? 'IN' : 'OUT' }}
                        </span>
                    </td>
                    <td>{{ $transaksi->pangkalan->nama ?? '-' }}</td>
                    <td>{{ $transaksi->tabung->nama ?? '-' }}</td>
                    <td>{{ $transaksi->keterangan }}</td>
                    <td style="text-align: right;">{{ number_format($transaksi->qty, 0) }}</td>
                    <td>{{ $transaksi->unit }}</td>
                    <td style="text-align: right;">{{ number_format($transaksi->total, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #28a745;">
                        @if($transaksi->debit > 0)
                            {{ number_format($transaksi->debit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right; color: #dc3545;">
                        @if($transaksi->credit > 0)
                            {{ number_format($transaksi->credit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ number_format($summary['total_transactions'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Debit</div>
                <div class="summary-value" style="color: #28a745;">
                    Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Credit</div>
                <div class="summary-value" style="color: #dc3545;">
                    Rp {{ number_format($summary['total_credit'], 0, ',', '.') }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Saldo Bersih</div>
                <div class="summary-value" style="color: {{ $summary['net_balance'] >= 0 ? '#28a745' : '#dc3545' }};">
                    Rp {{ number_format($summary['net_balance'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini dicetak otomatis oleh Sistem PT Migas Finance</p>
        <p>Tanggal Cetak: {{ $date }} | Halaman 1</p>
    </div>
</body>
</html>