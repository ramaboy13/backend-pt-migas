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

        .periode {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
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

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .summary h3 {
            margin-top: 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Tanggal Cetak: {{ $date }}</p>
    </div>

    @if (isset($periodeLabel) && $periodeLabel)
        <div class="periode">
            Periode: {{ $periodeLabel }}
        </div>
    @endif

    @if ($gajiList->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
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
                @foreach ($gajiList as $index => $gaji)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $gaji->karyawan->NIK ?? '-' }}</td>
                        <td>{{ $gaji->karyawan->nama ?? '-' }}</td>
                        <td>{{ $gaji->karyawan->jabatan ?? '-' }}</td>
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
        </table>

        @if (isset($summary))
            <div class="summary">
                <h3>Ringkasan</h3>
                <table style="width: auto;">
                    <tr>
                        <th>Total Karyawan</th>
                        <td>{{ $summary['total_karyawan'] ?? $gajiList->count() }}</td>
                    </tr>
                    <tr>
                        <th>Total Gapok</th>
                        <td style="text-align: right">
                            {{ number_format($summary['total_gapok'] ?? $gajiList->sum('gapok'), 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Lembur</th>
                        <td style="text-align: right">
                            {{ number_format($summary['total_lembur'] ?? $gajiList->sum('total_lembur'), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total Tunjangan</th>
                        <td style="text-align: right">
                            {{ number_format($summary['total_tunjangan'] ?? $gajiList->sum('total_tunjangan'), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total Pendapatan</th>
                        <td style="text-align: right">
                            {{ number_format($summary['total_pendapatan'] ?? $gajiList->sum('total_pendapatan'), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total Potongan</th>
                        <td style="text-align: right">
                            {{ number_format($summary['total_potongan'] ?? $gajiList->sum('total_potongan'), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total PPH21</th>
                        <td style="text-align: right">
                            {{ number_format($summary['total_pph21'] ?? $gajiList->sum('pph21'), 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th><strong>Total Gaji Bersih</strong></th>
                        <td style="text-align: right; font-weight: bold;">
                            {{ number_format($summary['total_gaji_bersih'] ?? $gajiList->sum('gaji_bersih'), 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        @endif
    @else
        <p>Tidak ada data gaji karyawan.</p>
    @endif

    <div class="footer">
        <p>Dicetak oleh sistem PT Migas</p>
    </div>
</body>

</html>
