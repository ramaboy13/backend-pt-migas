@extends('layouts.pdf')

@section('content')

    {{-- FILTER INFO --}}
    <div class="filter-info">
        <strong>Periode Laporan:</strong> {{ $date }}<br>
        <strong>Filter:</strong>
        @php $hasFilter = collect($filters)->filter()->isNotEmpty(); @endphp
        @if($hasFilter)
            @foreach($filters as $key => $value)
                @if($value)
                    {{ ucfirst(str_replace('_', ' ', $key)) }}: <strong>{{ $value }}</strong>
                    @if(!$loop->last) &nbsp;|&nbsp; @endif
                @endif
            @endforeach
        @else
            Semua Data
        @endif
    </div>

    {{-- TABEL --}}
    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" style="width:4%">No</th>
                <th style="width:9%">Tanggal</th>
                <th style="width:14%">Sumber Kas</th>
                <th style="width:8%">Tipe</th>
                <th style="width:22%">Keterangan</th>
                <th style="width:14%" class="text-right">Jumlah</th>
                <th style="width:14%" class="text-right">Saldo Sebelum</th>
                <th style="width:14%" class="text-right">Saldo Sesudah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kasEntries as $index => $kas)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($kas->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $kas->sumberKas->nama ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $kas->tipe_transaksi === 'masuk' ? 'badge-masuk' : 'badge-keluar' }}">
                            {{ strtoupper($kas->tipe_transaksi) }}
                        </span>
                    </td>
                    <td>{{ $kas->keterangan ?? '-' }}</td>
                    <td class="text-right {{ $kas->tipe_transaksi === 'masuk' ? 'text-green' : 'text-red' }}">
                        Rp {{ number_format($kas->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($kas->saldo_sebelum, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($kas->saldo_sesudah, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #6c757d;">
                        Tidak ada data kas perusahaan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SUMMARY --}}
    <div class="summary-section">
        <div class="summary-title">Ringkasan</div>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-label">Total Entri</div>
                    <div class="summary-value">
                        {{ number_format($summary['total_entries'], 0, ',', '.') }}
                    </div>
                </td>
                <td>
                    <div class="summary-label">Total Masuk</div>
                    <div class="summary-value text-green">
                        Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}
                    </div>
                </td>
                <td>
                    <div class="summary-label">Total Keluar</div>
                    <div class="summary-value text-red">
                        Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}
                    </div>
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