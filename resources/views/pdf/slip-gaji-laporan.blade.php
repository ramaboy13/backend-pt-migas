@extends('layouts.pdf')

@section('content')

<div class="filter-info">
    <strong>Periode:</strong> {{ $periodeLabel }}
    @if($isPerKaryawan && $gaji->isNotEmpty())
        &nbsp;|&nbsp;
        <strong>Karyawan:</strong> {{ $gaji->first()?->karyawan?->nama ?? '-' }}
        (NIK: {{ $gaji->first()?->karyawan?->nik ?? '-' }})
    @else
        &nbsp;|&nbsp;
        <strong>Total Karyawan:</strong> {{ $summary['total_karyawan'] }} orang
    @endif
    &nbsp;|&nbsp; <strong>Dicetak:</strong> {{ $date }}
</div>

@if(!$isPerKaryawan)

    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" style="width:4%">No</th>
                <th style="width:10%">NIK</th>
                <th style="width:18%">Nama Karyawan</th>
                <th style="width:10%">Jabatan</th>
                <th style="width:11%">Periode</th>
                <th style="width:13%" class="text-right">Pendapatan Kotor</th>
                <th style="width:10%" class="text-right">PPh21</th>
                <th style="width:10%" class="text-right">Potongan</th>
                <th style="width:14%" class="text-right">Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gaji as $index => $item)
            @php
                $potonganLain = max(0, ($item->subtotal ?? 0) - ($item->gaji_bersih ?? 0) - ($item->pph21 ?? 0));
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->karyawan?->nik ?? '-' }}</td>
                <td>{{ $item->karyawan?->nama ?? '-' }}</td>
                <td>{{ $item->karyawan?->jabatan ?? '-' }}</td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($item->periode)->translatedFormat('M Y') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right text-red">
                    {{ ($item->pph21 ?? 0) > 0 ? 'Rp '.number_format($item->pph21, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right text-red">
                    {{ $potonganLain > 0 ? 'Rp '.number_format($potonganLain, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right text-green" style="font-weight:bold;">
                    Rp {{ number_format($item->gaji_bersih ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding:24px; color:#6c757d;">
                    Tidak ada data gaji karyawan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

@else

    @php $karyawan = $gaji->first()?->karyawan; @endphp

    <table style="width:100%; margin-bottom:14px; font-size:10px; border-collapse:collapse;">
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:10px;">
                <table style="width:100%;">
                    <tr>
                        <td style="width:35%; color:#6c757d;">NIK</td>
                        <td style="width:5%">:</td>
                        <td><strong>{{ $karyawan?->nik ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#6c757d;">Nama</td>
                        <td>:</td>
                        <td><strong>{{ $karyawan?->nama ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#6c757d;">Jabatan</td>
                        <td>:</td>
                        <td>{{ $karyawan?->jabatan ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:50%; vertical-align:top; padding-left:10px;">
                <table style="width:100%;">
                    <tr>
                        <td style="width:40%; color:#6c757d;">Departemen</td>
                        <td style="width:5%">:</td>
                        <td>{{ $karyawan?->departemen ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6c757d;">Tgl Masuk</td>
                        <td>:</td>
                        <td>{{ $karyawan?->tgl_masuk ? \Carbon\Carbon::parse($karyawan->tgl_masuk)->format('d/m/Y') : '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Tabel Riwayat Gaji per Periode --}}
    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th style="width:13%">Periode</th>
                <th style="width:22%">Komponen Pendapatan</th>
                <th style="width:22%">Komponen Potongan</th>
                <th style="width:13%" class="text-right">Pendapatan Kotor</th>
                <th style="width:11%" class="text-right">PPh21</th>
                <th style="width:14%" class="text-right">Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gaji as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    <strong>{{ \Carbon\Carbon::parse($item->periode)->translatedFormat('F Y') }}</strong>
                </td>
                <td style="font-size:9px; color:#495057;">
                    {{ $item->pendapatan?->nama ?? '-' }}
                </td>
                <td style="font-size:9px; color:#495057;">
                    {{ $item->potongan?->nama ?? '-' }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right text-red">
                    {{ ($item->pph21 ?? 0) > 0 ? 'Rp '.number_format($item->pph21, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right text-green" style="font-weight:bold;">
                    Rp {{ number_format($item->gaji_bersih ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding:24px; color:#6c757d;">
                    Tidak ada data gaji untuk karyawan ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

@endif

{{-- ===================== SUMMARY ===================== --}}
<div class="summary-section">
    <div class="summary-title">Ringkasan Penggajian — {{ $periodeLabel }}</div>
    <table class="summary-grid">
        <tr>
            <td>
                <div class="summary-label">
                    {{ $isPerKaryawan ? 'Total Periode' : 'Total Karyawan' }}
                </div>
                <div class="summary-value">
                    {{ $isPerKaryawan ? $summary['total_entri'] : $summary['total_karyawan'] }}
                    <span style="font-size:10px; font-weight:normal;">
                        {{ $isPerKaryawan ? 'periode' : 'orang' }}
                    </span>
                </div>
            </td>
            <td>
                <div class="summary-label">Total Pendapatan Kotor</div>
                <div class="summary-value">
                    Rp {{ number_format($summary['total_subtotal'], 0, ',', '.') }}
                </div>
            </td>
            <td>
                <div class="summary-label">Total PPh21</div>
                <div class="summary-value text-red">
                    Rp {{ number_format($summary['total_pph21'], 0, ',', '.') }}
                </div>
            </td>
            <td>
                <div class="summary-label">Total Gaji Bersih</div>
                <div class="summary-value text-green">
                    Rp {{ number_format($summary['total_gaji_bersih'], 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- TANDA TANGAN --}}
<table style="width:100%; margin-top:36px; font-size:10px;">
    <tr>
        <td style="width:33%; text-align:center;">
            Dibuat oleh,
            <div style="margin-top:48px; border-top:1px solid #333; padding-top:4px;">HRD / Payroll</div>
        </td>
        <td style="width:33%; text-align:center;">
            Diperiksa oleh,
            <div style="margin-top:48px; border-top:1px solid #333; padding-top:4px;">Manager Keuangan</div>
        </td>
        <td style="width:33%; text-align:center;">
            Disetujui oleh,
            <div style="margin-top:48px; border-top:1px solid #333; padding-top:4px;">Direktur</div>
        </td>
    </tr>
</table>

@endsection