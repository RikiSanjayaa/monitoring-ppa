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

        .detail-section {
            page-break-inside: avoid;
            break-inside: avoid-page;
            margin: 8px 0 4px;
        }

        .detail-title {
            margin: 0 0 4px;
            font-weight: 700;
        }

        .detail-section table thead {
            display: table-header-group;
        }

        .detail-section table tr {
            page-break-inside: avoid;
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
            width: 90px;
        }

        .rekap .tp-pasal {
            width: 145px;
        }

        .rekap .jenis {
            width: 95px;
        }

        .rekap .num-col {
            width: 42px;
        }

        .rekap td:nth-child(2),
        .rekap td:nth-child(3) {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .vertical-head {
            width: 36px;
            padding: 2px 1px;
            vertical-align: middle;
        }

        .vertical-head > span {
            display: block;
            transform: none;
            white-space: normal;
            line-height: 1.1;
            font-size: 9px;
            text-align: center;
        }

        .vertical-cell {
            text-align: center;
            width: 36px;
            padding: 2px 1px;
            font-weight: 600;
        }

        .check-mark {
            display: inline-block;
            position: relative;
            width: 11px;
            height: 11px;
            line-height: 11px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
            font-weight: 700;
        }

        .check-mark-stem,
        .check-mark-kick {
            position: absolute;
            display: block;
            width: 2px;
            background: #111827;
        }

        .check-mark-stem {
            left: 2px;
            top: 6px;
            height: 4px;
            transform: rotate(-45deg);
        }

        .check-mark-kick {
            left: 6px;
            top: 1px;
            height: 9px;
            transform: rotate(35deg);
        }

        .rekap-section {
            page-break-before: always;
        }

        .rekap thead {
            display: table-header-group;
        }

        .rekap tr {
            page-break-inside: avoid;
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
        $dokumenGiatColumns = collect([
            ['key' => 'lidik', 'label' => 'LIDIK'],
            ['key' => 'sidik', 'label' => 'SIDIK'],
        ]);
        $rekapPenyelesaianColumns = $penyelesaianColumns->map(fn($column) => [
            'key' => $column['key'],
            'label' => strtoupper((string) $column['label']),
            'id' => $column['id'],
        ]);
    @endphp

    @forelse ($recordsByJenis as $jenisKasus => $groupedRecords)
        <div class="detail-section">
        <div class="detail-title">{{ strtoupper($jenisKasus) }}</div>
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
                    <th colspan="{{ $dokumenGiatColumns->count() }}">DOKUMEN/<br>GIAT</th>
                    <th colspan="{{ max(1, $rekapPenyelesaianColumns->count()) }}">PENYELESAIAN PERKARA</th>
                    @if ($rekapPenyelesaianColumns->isEmpty())
                        <th rowspan="2">-</th>
                    @endif
                    <th rowspan="2" class="vertical-head"><span>KET</span></th>
                </tr>
                <tr>
                    <th>KORBAN</th>
                    <th>TERSANGKA</th>
                    @foreach ($dokumenGiatColumns as $column)
                        <th class="vertical-head"><span>{{ strtoupper((string) $column['label']) }}</span></th>
                    @endforeach
                    @forelse ($rekapPenyelesaianColumns as $column)
                        <th class="vertical-head"><span>{{ strtoupper((string) $column['label']) }}</span></th>
                    @empty
                        <th class="vertical-head"><span>-</span></th>
                    @endforelse
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
                        <td class="vertical-cell">
                            <span class="check-mark">
                                @if ($record->dokumen_status?->value === 'lidik')
                                    <span class="check-mark-stem"></span><span class="check-mark-kick"></span>
                                @endif
                            </span>
                        </td>
                        <td class="vertical-cell">
                            <span class="check-mark">
                                @if ($record->dokumen_status?->value === 'sidik')
                                    <span class="check-mark-stem"></span><span class="check-mark-kick"></span>
                                @endif
                            </span>
                        </td>
                        @foreach ($rekapPenyelesaianColumns as $column)
                            <td class="vertical-cell">
                                <span class="check-mark">
                                    @if ($penyelesaianId === (int) $column['id'])
                                        <span class="check-mark-stem"></span><span class="check-mark-kick"></span>
                                    @endif
                                </span>
                            </td>
                        @endforeach
                        @if ($rekapPenyelesaianColumns->isEmpty())
                            <td class="vertical-cell"></td>
                        @endif
                        <td class="small">
                            @if ($record->latestRtl)
                                {{ $record->latestRtl->tanggal?->format('d-m-Y') ?? '-' }} - {{ $record->latestRtl->keterangan ?: '-' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @empty
        <table>
            <tbody>
                <tr>
                    <td class="center">Tidak ada data.</td>
                </tr>
            </tbody>
        </table>
    @endforelse

    <div class="rekap-section">
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
            @foreach ($rekapPenyelesaianColumns as $column)
                <col class="num-col">
            @endforeach
            @if ($rekapPenyelesaianColumns->isEmpty())
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
                <th colspan="{{ $dokumenGiatColumns->count() }}">DOKUMEN/<br>GIAT</th>
                <th colspan="{{ max(1, $rekapPenyelesaianColumns->count()) }}">PENYELESAIAN PERKARA</th>
                <th rowspan="2" class="vertical-head"><span>JML</span></th>
                <th rowspan="2" class="vertical-head"><span>KET</span></th>
            </tr>
            <tr>
                <th class="vertical-head"><span>KORBAN</span></th>
                <th class="vertical-head"><span>TERSANGKA</span></th>
                <th class="vertical-head"><span>SAKSI</span></th>
                <th class="vertical-head"><span>LIDIK</span></th>
                <th class="vertical-head"><span>SIDIK</span></th>
                @forelse ($rekapPenyelesaianColumns as $column)
                    <th class="vertical-head"><span>{{ strtoupper((string) $column['label']) }}</span></th>
                @empty
                    <th class="vertical-head"><span>-</span></th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @foreach ($recapData['rows'] as $row)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $row['jenis'] }}</td>
                    <td>{{ $row['pasal'] }}</td>
                    <td class="vertical-cell">{{ $row['jumlah_korban'] }}</td>
                    <td class="vertical-cell">{{ $row['jumlah_tersangka'] }}</td>
                    <td class="vertical-cell">{{ $row['jumlah_saksi'] }}</td>
                    <td class="vertical-cell">{{ $row['lidik'] }}</td>
                    <td class="vertical-cell">{{ $row['sidik'] }}</td>
                    @forelse ($rekapPenyelesaianColumns as $column)
                        <td class="vertical-cell">{{ $row['penyelesaian_counts'][$column['key']] ?? 0 }}</td>
                    @empty
                        <td class="vertical-cell">0</td>
                    @endforelse
                    <td class="vertical-cell">{{ $row['jumlah'] }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr>
                <td class="center" colspan="3"><strong>JUMLAH TOTAL</strong></td>
                <td class="vertical-cell"><strong>{{ $recapData['totals']['jumlah_korban'] }}</strong></td>
                <td class="vertical-cell"><strong>{{ $recapData['totals']['jumlah_tersangka'] }}</strong></td>
                <td class="vertical-cell"><strong>{{ $recapData['totals']['jumlah_saksi'] }}</strong></td>
                <td class="vertical-cell"><strong>{{ $recapData['totals']['lidik'] }}</strong></td>
                <td class="vertical-cell"><strong>{{ $recapData['totals']['sidik'] }}</strong></td>
                @forelse ($rekapPenyelesaianColumns as $column)
                    <td class="vertical-cell">
                        <strong>{{ $recapData['totals']['penyelesaian_counts'][$column['key']] ?? 0 }}</strong>
                    </td>
                @empty
                    <td class="vertical-cell"><strong>0</strong></td>
                @endforelse
                <td class="vertical-cell"><strong>{{ $recapData['totals']['jumlah'] }}</strong></td>
                <td></td>
            </tr>
        </tbody>
        </table>
    </div>

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
