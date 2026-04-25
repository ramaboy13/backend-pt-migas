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
                            <span style="color:#6c757d;">Semua Data</span>
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
                <th class="text-center" style="width:4%">No</th>
                <th style="width:11%">Tanggal</th>
                <th style="width:18%">Sumber Kas</th>
                <th style="width:8%" class="text-center">Tipe</th>
                <th style="width:25%">Keterangan</th>
                <th style="width:12%" class="text-right">Jumlah</th>
                <th style="width:11%" class="text-right">Saldo Sebelum</th>
                <th style="width:11%" class="text-right">Saldo Sesudah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kasEntries as $index => $kas)
                @php
                    // Nilai DB: DEBIT = uang masuk (hijau), KREDIT = uang keluar (merah)
                    $isPositive = strtoupper($kas->tipe_transaksi) === 'DEBIT';

                    $sumberNama = '-';
                    if ($kas->sumberKas) {
                        $bagian = [];
                        if (!empty($kas->sumberKas->nama_bank)) {
                            $bagian[] = $kas->sumberKas->nama_bank;
                        }
                        if (!empty($kas->sumberKas->atas_nama)) {
                            $bagian[] = $kas->sumberKas->atas_nama;
                        }
                        $sumberNama = count($bagian) > 0 ? implode(' - ', $bagian) : '-';
                    }
                @endphp
                <tr>
                    <td class="text-center" style="color:#4B70A9; font-weight:bold;">
                        {{ $index + 1 }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($kas->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
                    </td>
                    <td style="font-size:9.5px;">
                        {{ $sumberNama }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $isPositive ? 'badge-debit' : 'badge-kredit' }}">
                            {{ strtoupper($kas->tipe_transaksi) }}
                        </span>
                    </td>
                    <td style="font-size:9.5px;">
                        {{ $kas->keterangan ?? '-' }}
                    </td>
                    <td class="text-right"
                        style="font-weight:bold; {{ $isPositive ? 'color:#1a7a3a;' : 'color:#c0392b;' }}">
                        {{ $isPositive ? '+' : '-' }} Rp {{ number_format($kas->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="color:#5a7a9a;">
                        Rp {{ number_format($kas->saldo_sebelum ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="font-weight:bold; color:#1a2a3a;">
                        Rp {{ number_format($kas->saldo_sesudah ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding:24px; color:#6c757d;">
                        Tidak ada data kas perusahaan.
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
                                {{ number_format($summary['total_entries'], 0, ',', '.') }}
                                <span style="font-size:9px; font-weight:normal; color:#6c757d;"> transaksi</span>
                            </div>
                            <div class="summary-divider"></div>
                        </div>
                    </td>
                    <td style="width:33%; padding:4px;">
                        <div class="summary-card">
                            <div class="summary-label">Total Uang Masuk</div>
                            <div class="summary-value text-green">
                                Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}
                            </div>
                            <div class="summary-divider" style="background-color:#1a7a3a;"></div>
                        </div>
                    </td>
                    <td style="width:33%; padding:4px;">
                        <div class="summary-card">
                            <div class="summary-label">Total Uang Keluar</div>
                            <div class="summary-value text-red">
                                Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}
                            </div>
                            <div class="summary-divider" style="background-color:#c0392b;"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

@endsection
