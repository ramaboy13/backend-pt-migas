@extends('layouts.pdf')

@section('content')

    <div class="filter-info">
        <strong>Periode Laporan:</strong> {{ $date }}<br>
        <strong>Filter:</strong>
        @if(!empty($filters))
            @foreach($filters as $key => $value)
                @if($value)
                    {{ ucfirst(str_replace('_', ' ', $key)) }}: <strong>{{ $value }}</strong>
                    @if(!$loop->last) &nbsp;|&nbsp; @endif
                @endif
            @endforeach
        @else
            Semua Transaksi
        @endif
    </div>

    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" style="width:4%">No</th>
                <th style="width:8%">Tanggal</th>
                <th style="width:9%">No. Ref</th>
                <th style="width:6%">Jenis</th>
                <th style="width:12%">Pangkalan</th>
                <th style="width:10%">Tabung</th>
                <th>Keterangan</th>
                <th style="width:6%" class="text-right">Qty</th>
                <th style="width:5%">Unit</th>
                <th style="width:10%" class="text-right">Total</th>
                <th style="width:9%" class="text-right">Debit</th>
                <th style="width:9%" class="text-right">Credit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaksi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $transaksi->no_ref }}</td>
                    <td class="text-center">
                        <span class="badge {{ $transaksi->is_in ? 'badge-masuk' : 'badge-keluar' }}">
                            {{ $transaksi->is_in ? 'IN' : 'OUT' }}
                        </span>
                    </td>
                    <td>{{ $transaksi->pangkalan->nama ?? '-' }}</td>
                    <td>{{ $transaksi->tabung->nama ?? '-' }}</td>
                    <td>{{ $transaksi->keterangan }}</td>
                    <td class="text-right">{{ number_format($transaksi->qty, 0) }}</td>
                    <td>{{ $transaksi->unit }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                    <td class="text-right text-green">
                        {{ $transaksi->debit > 0 ? 'Rp '.number_format($transaksi->debit, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right text-red">
                        {{ $transaksi->credit > 0 ? 'Rp '.number_format($transaksi->credit, 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding:20px; color:#6c757d;">
                        Tidak ada data transaksi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-title">Ringkasan</div>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-label">Total Transaksi</div>
                    <div class="summary-value">{{ number_format($summary['total_transactions'], 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Debit</div>
                    <div class="summary-value text-green">Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Credit</div>
                    <div class="summary-value text-red">Rp {{ number_format($summary['total_credit'], 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Saldo Bersih</div>
                    <div class="summary-value {{ $summary['net_balance'] >= 0 ? 'text-green' : 'text-red' }}">
                        Rp {{ number_format($summary['net_balance'], 0, ',', '.') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

@endsection