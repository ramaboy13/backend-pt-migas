<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .info-karyawan {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .info-karyawan table {
            width: 100%;
        }

        .info-karyawan td {
            padding: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }

        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>SLIP GAJI KARYAWAN</h2>
        <p>PT Migas Sejahtera</p>
        <p>Tanggal Cetak: {{ $date }}</p>
    </div>

    @if (isset($karyawan))
        <div class="info-karyawan">
            <table>
                <tr>
                    <td width="150">NIK</td>
                    <td>: {{ $karyawan->NIK ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td>: {{ $karyawan->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>: {{ $karyawan->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Masuk</td>
                    <td>: {{ $karyawan->tgl_masuk ?? '-' }}</td>
                </tr>
            </table>
        </div>
    @endif

    <h3>Rekap Gaji per Periode</h3>

    @if ($gajiList->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Gapok</th>
                    <th>Lembur</th>
                    <th>Tunjangan</th>
                    <th>Total Pendapatan</th>
                    <th>Pot. BPJS Kes</th>
                    <th>Pot. BPJS TK</th>
                    <th>Pot. Lain</th>
                    <th>Total Potongan</th>
                    <th>PPH21</th>
                    <th>Gaji Bersih</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGapok = 0;
                    $totalLembur = 0;
                    $totalTunjangan = 0;
                    $totalPendapatan = 0;
                    $totalPotonganBpjsKes = 0;
                    $totalPotonganBpjsTk = 0;
                    $totalPotonganLain = 0;
                    $totalPotongan = 0;
                    $totalPph21 = 0;
                    $totalGajiBersih = 0;
                @endphp
                @foreach ($gajiList as $gaji)
                    @php
                        $totalGapok += $gaji->gapok;
                        $totalLembur += $gaji->total_lembur;
                        $totalTunjangan += $gaji->total_tunjangan;
                        $totalPendapatan += $gaji->total_pendapatan;
                        $totalPotonganBpjsKes += $gaji->potongan_bpjs_kesehatan;
                        $totalPotonganBpjsTk += $gaji->potongan_bpjs_tenagakerja;
                        $totalPotonganLain += $gaji->potongan_lainnya;
                        $totalPotongan += $gaji->total_potongan;
                        $totalPph21 += $gaji->pph21;
                        $totalGajiBersih += $gaji->gaji_bersih;
                    @endphp
                    <tr>
                        <td>{{ $gaji->bulan }}/{{ $gaji->tahun }}</td>
                        <td style="text-align: right">{{ number_format($gaji->gapok, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->total_lembur, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->total_tunjangan, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->total_pendapatan, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->potongan_bpjs_kesehatan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right">{{ number_format($gaji->potongan_bpjs_tenagakerja, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right">{{ number_format($gaji->potongan_lainnya, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->total_potongan, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->pph21, 0, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($gaji->gaji_bersih, 0, ',', '.') }}</td>
                        <td>{{ $gaji->status_display ?? $gaji->status }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>Total</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($totalGapok, 0, ',', '.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($totalLembur, 0, ',', '.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($totalTunjangan, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align: right"><strong>{{ number_format($totalPendapatan, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align: right">
                        <strong>{{ number_format($totalPotonganBpjsKes, 0, ',', '.') }}</strong></td>
                    <td style="text-align: right">
                        <strong>{{ number_format($totalPotonganBpjsTk, 0, ',', '.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($totalPotonganLain, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align: right"><strong>{{ number_format($totalPotongan, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align: right"><strong>{{ number_format($totalPph21, 0, ',', '.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($totalGajiBersih, 0, ',', '.') }}</strong>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        @if (isset($summary))
            <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px;">
                <h4>Ringkasan</h4>
                <table style="width: auto;">
                    <tr>
                        <td>Total Periode</td>
                        <td>: {{ $summary['total_periode'] ?? $gajiList->count() }}</td>
                    </tr>
                    <tr>
                        <td>Rata-rata Gaji Bersih</td>
                        <td>: {{ number_format($summary['rata_rata_gaji'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        @endif
    @else
        <p>Tidak ada data gaji untuk karyawan ini.</p>
    @endif

    <div class="footer">
        <p>Slip gaji ini dicetak secara otomatis dari sistem.</p>
    </div>
</body>

</html>
