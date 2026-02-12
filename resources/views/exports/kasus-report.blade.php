<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Kasus</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .kop {
            text-align: center;
            margin-bottom: 6px;
        }

        .kop .line {
            font-weight: 700;
            line-height: 1.2;
        }

        .kop .line:nth-child(1) {
            font-size: 12px;
        }

        .kop .line:nth-child(2),
        .kop .line:nth-child(3) {
            font-size: 11px;
        }

        .kop hr {
            border: 0;
            border-top: 2px solid #111827;
            margin-top: 6px;
        }

        .title {
            text-align: center;
            font-weight: 700;
            margin: 8px 0;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #374151;
            padding: 4px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: center;
            font-weight: 700;
        }

        .num {
            text-align: center;
            width: 28px;
        }

        .center {
            text-align: center;
        }

        .small {
            font-size: 9px;
        }

        .spacer {
            height: 12px;
        }

        .signature {
            margin-top: 18px;
            width: 100%;
        }

        .signature .right {
            width: 42%;
            margin-left: auto;
            text-align: center;
        }

        .signature .name {
            margin-top: 48px;
            font-weight: 700;
            text-decoration: underline;
        }

        .rekap {
            table-layout: fixed;
        }

        .rekap th,
        .rekap td {
            padding: 3px;
        }

        .rekap .ket {
            width: 120px;
        }

        .rekap .tp-pasal {
            width: 170px;
        }

        .rekap .jenis {
            width: 85px;
        }

        .rekap .num-col {
            width: 56px;
        }
    </style>
</head>

<body>
    <div class="kop">
        @foreach ($kopSuratLines as $line)
            <div class="line">{{ $line }}</div>
        @endforeach
        <hr>
    </div>

    <div class="title">{{ $mainTitle }}</div>

    @php
        $recordsByJenis = $records->groupBy(fn($record) => $record->perkara?->nama ?? 'Lainnya');
        $recapData = $recapData ?? [
            'penyelesaian_columns' => collect(),
            'rows' => collect(),
            'totals' => [
                'jumlah_korban' => 0,
                'jumlah_tersangka' => 0,
                'jumlah_saksi' => 0,
                'lidik' => 0,
                'sidik' => 0,
                'penyelesaian_counts' => [],
                'jumlah' => 0,
            ],
        ];
        $penyelesaianColumns = $recapData['penyelesaian_columns'];
        $hasPenyelesaianColumns = $penyelesaianColumns->isNotEmpty();
    @endphp

    @forelse ($recordsByJenis as $jenisKasus => $groupedRecords)
        <div style="margin: 8px 0 4px; font-weight: 700;">{{ strtoupper($jenisKasus) }}</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="num">NO</th>
                    <th rowspan="2">SATUAN</th>
                    <th rowspan="2">LAPORAN POLISI/TGL</th>
                    <th rowspan="2">KRONOLOGIS KEJADIAN</th>
                    <th rowspan="2">TINDAK PIDANA/PASAL</th>
                    <th colspan="2">IDENTITAS</th>
                    <th rowspan="2">HUB. TERSANGKA DENGAN KORBAN</th>
                    <th rowspan="2">LIDIK</th>
                    <th rowspan="2">SIDIK</th>
                    @forelse ($penyelesaianColumns as $column)
                        <th rowspan="2">{{ strtoupper((string) $column['label']) }}</th>
                    @empty
                        <th rowspan="2">-</th>
                    @endforelse
                    <th rowspan="2">KET</th>
                </tr>
                <tr>
                    <th>KORBAN</th>
                    <th>TERSANGKA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedRecords as $record)
                    @php
                        $korbanText = $record->korbans->pluck('nama')->join(', ');
                        $tersangkaText = $record->tersangkas->pluck('nama')->join(', ');
                        $penyelesaianId = (int) ($record->penyelesaian_id ?? 0);
                    @endphp
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $record->satker?->nama }}</td>
                        <td class="small">{{ $record->nomor_lp }}<br>{{ $record->tanggal_lp?->format('d-m-Y') }}</td>
                        <td>{{ $record->kronologi_kejadian ?: '-' }}</td>
                        <td>{{ $record->tindak_pidana_pasal ?: '-' }}</td>
                        <td>{{ $korbanText !== '' ? $korbanText : '-' }}</td>
                        <td>{{ $tersangkaText !== '' ? $tersangkaText : '-' }}</td>
                        <td>{{ $record->hubungan_pelaku_dengan_korban ?: '-' }}</td>
                        <td class="center">{{ $record->dokumen_status?->value === 'lidik' ? '1' : '' }}</td>
                        <td class="center">{{ $record->dokumen_status?->value === 'sidik' ? '1' : '' }}</td>
                        @forelse ($penyelesaianColumns as $column)
                            <td class="center">{{ $penyelesaianId === (int) $column['id'] ? '1' : '' }}</td>
                        @empty
                            <td class="center"></td>
                        @endforelse
                        <td class="small">{{ $record->latestRtl?->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <table>
            <tbody>
                <tr>
                    <td class="center">Tidak ada data.</td>
                </tr>
            </tbody>
        </table>
    @endforelse

    <div class="spacer"></div>
    <div class="title">{{ $recapTitle }}</div>

    <table class="rekap">
        <colgroup>
            <col style="width: 34px;">
            <col class="jenis">
            <col class="tp-pasal">
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            @foreach ($penyelesaianColumns as $column)
                <col class="num-col">
            @endforeach
            @if (! $hasPenyelesaianColumns)
                <col class="num-col">
            @endif
            <col class="num-col">
            <col class="ket">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" class="num">NO</th>
                <th rowspan="2">JENIS TP</th>
                <th rowspan="2">TP.PASAL</th>
                <th colspan="3">JUMLAH</th>
                <th colspan="2">DOKUMEN/GIAT</th>
                <th colspan="{{ max(1, $penyelesaianColumns->count()) }}">PENYELESAIAN PERKARA</th>
                <th rowspan="2">JML</th>
                <th rowspan="2">KET</th>
            </tr>
            <tr>
                <th>KORBAN</th>
                <th>TERSANGKA</th>
                <th>SAKSI</th>
                <th>LIDIK</th>
                <th>SIDIK</th>
                @forelse ($penyelesaianColumns as $column)
                    <th>{{ strtoupper((string) $column['label']) }}</th>
                @empty
                    <th>-</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @foreach ($recapData['rows'] as $row)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $row['jenis'] }}</td>
                    <td>{{ $row['pasal'] }}</td>
                    <td class="center">{{ $row['jumlah_korban'] }}</td>
                    <td class="center">{{ $row['jumlah_tersangka'] }}</td>
                    <td class="center">{{ $row['jumlah_saksi'] }}</td>
                    <td class="center">{{ $row['lidik'] }}</td>
                    <td class="center">{{ $row['sidik'] }}</td>
                    @forelse ($penyelesaianColumns as $column)
                        <td class="center">{{ $row['penyelesaian_counts'][$column['key']] ?? 0 }}</td>
                    @empty
                        <td class="center">0</td>
                    @endforelse
                    <td class="center">{{ $row['jumlah'] }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr>
                <td class="center" colspan="3"><strong>JUMLAH TOTAL</strong></td>
                <td class="center"><strong>{{ $recapData['totals']['jumlah_korban'] }}</strong></td>
                <td class="center"><strong>{{ $recapData['totals']['jumlah_tersangka'] }}</strong></td>
                <td class="center"><strong>{{ $recapData['totals']['jumlah_saksi'] }}</strong></td>
                <td class="center"><strong>{{ $recapData['totals']['lidik'] }}</strong></td>
                <td class="center"><strong>{{ $recapData['totals']['sidik'] }}</strong></td>
                @forelse ($penyelesaianColumns as $column)
                    <td class="center">
                        <strong>{{ $recapData['totals']['penyelesaian_counts'][$column['key']] ?? 0 }}</strong>
                    </td>
                @empty
                    <td class="center"><strong>0</strong></td>
                @endforelse
                <td class="center"><strong>{{ $recapData['totals']['jumlah'] }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <div class="right">
            <div>{{ $signatureBlock['line1'] }}</div>
            <div>{{ $signatureBlock['line2'] }}</div>
            <div class="name">{{ $signatureBlock['name'] }}</div>
            <div>{{ $signatureBlock['rank'] }}</div>
        </div>
    </div>
</body>

</html>
