@extends('layouts.pdf')

@section('content')

    {{-- FILTER INFO --}}
    <div class="filter-wrap">
        <div class="filter-header">&#128203; INFORMASI LAPORAN</div>
        <div class="filter-body">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:50%; padding-right:10px;">
                        <strong>Periode Laporan:</strong>
                        @if (!empty($filters['start_date']) && !empty($filters['end_date']))
                            {{ \Carbon\Carbon::parse($filters['start_date'])->locale('id')->isoFormat('DD MMMM YYYY') }}
                            &nbsp;&ndash;&nbsp;
                            {{ \Carbon\Carbon::parse($filters['end_date'])->locale('id')->isoFormat('DD MMMM YYYY') }}
                        @else
                            Semua Periode
                        @endif
                    </td>
                    <td style="width:50%;">
                        <strong>Filter Aktif:</strong>
                        @php $hasFilter = collect($filters)->filter()->isNotEmpty(); @endphp
                        @if ($hasFilter)
                            @foreach ($filters as $key => $value)
                                @if ($value)
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}: <strong>{{ $value }}</strong>
                                    @if (!$loop->last)
                                        &nbsp;|&nbsp;
                                    @endif
                                @endif
                            @endforeach
                        @else
                            <span style="color:#6c757d;">Semua Transaksi</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- TABEL --}}
    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" style="width:3%">No</th>
                <th style="width:10%">Tanggal</th>
                <th style="width:12%">No. Ref</th>
                <th style="width:7%" class="text-center">Jenis</th>
                <th style="width:10%">Pangkalan</th>
                <th style="width:8%">Tabung</th>
                <th>Keterangan</th>
                <th style="width:5%" class="text-right">Qty</th>
                <th style="width:4%" class="text-center">Unit</th>
                <th style="width:11%" class="text-right">Debit</th>
                <th style="width:11%" class="text-right">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaksi)
                @php
                    $isPemasukan = $transaksi->is_pemasukan ?? false;
                    $jumlah = $transaksi->jumlah ?? 0;
                @endphp
                <tr>
                    <td class="text-center" style="color:#4B70A9; font-weight:bold;">
                        {{ $index + 1 }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($transaksi->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
                    </td>
                    <td style="font-size:9px; color:#2c3e5a; font-weight:bold;">
                        {{ $transaksi->no_ref ?? '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $isPemasukan ? 'badge-debit' : 'badge-kredit' }}">
                            {{ $isPemasukan ? 'IN' : 'OUT' }}
                        </span>
                    </td>
                    <td style="font-size:9.5px;">
                        {{ $transaksi->pangkalan->nama ?? '-' }}
                    </td>
                    <td style="font-size:9.5px;">
                        {{ $transaksi->tabung->nama ?? '-' }}
                    </td>
                    <td style="font-size:9.5px;">
                        {{ $transaksi->keterangan ?? '-' }}
                    </td>
                    <td class="text-right">
                        {{ $transaksi->qty > 0 ? number_format($transaksi->qty, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-center" style="font-size:9px; color:#5a7a9a;">
                        {{ $transaksi->unit ?? '-' }}
                    </td>
                    {{-- Debit = pemasukan (hijau) --}}
                    <td class="text-right" style="font-weight:bold; color:#1a7a3a;">
                        @if ($isPemasukan && $jumlah > 0)
                            + Rp {{ number_format($jumlah, 0, ',', '.') }}
                        @else
                            <span style="color:#adb5bd;">-</span>
                        @endif
                    </td>
                    {{-- Kredit = pengeluaran (merah) --}}
                    <td class="text-right" style="font-weight:bold; color:#c0392b;">
                        @if (!$isPemasukan && $jumlah > 0)
                            - Rp {{ number_format($jumlah, 0, ',', '.') }}
                        @else
                            <span style="color:#adb5bd;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding:24px; color:#6c757d;">
                        Tidak ada data transaksi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SUMMARY --}}
    <div class="summary-section">
        <div class="summary-header">&#9776; RINGKASAN TRANSAKSI</div>
        <div class="summary-body">
            <table class="summary-grid">
                <tr>
                    <td style="width:33%; padding:4px;">
                        <div class="summary-card">
                            <div class="summary-label">Total Data</div>
                            <div class="summary-value text-blue">
                                {{ number_format($summary['total_transactions'], 0, ',', '.') }}
                                <span style="font-size:9px; font-weight:normal; color:#6c757d;"> transaksi</span>
                            </div>
                            <div class="summary-divider"></div>
                        </div>
                    </td>
                    <td style="width:33%; padding:4px;">
                        <div class="summary-card">
                            <div class="summary-label">Total Debit (Masuk)</div>
                            <div class="summary-value text-green">
                                Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}
                            </div>
                            <div class="summary-divider" style="background-color:#1a7a3a;"></div>
                        </div>
                    </td>
                    <td style="width:33%; padding:4px;">
                        <div class="summary-card">
                            <div class="summary-label">Total Kredit (Keluar)</div>
                            <div class="summary-value text-red">
                                Rp {{ number_format($summary['total_credit'], 0, ',', '.') }}
                            </div>
                            <div class="summary-divider" style="background-color:#c0392b;"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

@endsection
