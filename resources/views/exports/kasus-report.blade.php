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
            page-break-inside: auto;
            break-inside: auto;
            margin: 8px 0 4px;
        }

        .main-title-caption {
            caption-side: top;
            text-align: center;
            font-weight: 700;
            margin: 8px 0;
            text-transform: uppercase;
            page-break-after: avoid;
        }

        .main-title-caption .detail-line {
            margin-top: 4px;
            text-align: left;
        }

        .detail-title {
            margin: 0 0 4px;
            font-weight: 700;
            page-break-after: avoid;
        }

        .detail-section table thead {
            display: table-header-group;
        }

        .detail-section table tr {
            page-break-inside: avoid;
        }

        .detail-table {
            table-layout: fixed;
        }

        .detail-table th,
        .detail-table td {
            padding: 3px;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .detail-table {
            font-size: 8.8px;
            line-height: 1.25;
        }

        .detail-table th {
            font-size: 8px;
            line-height: 1.15;
        }

        .detail-table .vertical-head,
        .detail-table .vertical-cell {
            padding: 1px 0.5px;
        }

        .detail-table .vertical-head>span {
            font-size: 6px;
            line-height: 1.05;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .detail-table .small {
            font-size: 8px;
            line-height: 1.2;
        }

        .detail-identitas-subhead {
            font-size: 7px;
            line-height: 1.05;
            overflow-wrap: normal;
            word-break: normal;
        }

        .detail-identitas-cell {
            font-size: 8.4px;
            line-height: 1.25;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .detail-kronologi-head,
        .detail-kronologi-cell {
            overflow-wrap: anywhere;
            word-break: break-word;
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

        .vertical-head>span {
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

        .rekap-notes {
            margin-top: 6px;
            font-size: 8px;
            line-height: 1.35;
        }

        .rekap-notes-title {
            font-weight: 700;
            margin-bottom: 2px;
        }

        .rekap-notes ol {
            margin: 0;
            padding-left: 16px;
        }

        .rekap-notes li {
            margin-bottom: 2px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .rekap-notes li:last-child {
            margin-bottom: 0;
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

    @php
        $recordsByJenis = $records->groupBy(fn($record) => $record->perkara?->nama ?? 'Lainnya');
        $satkers = collect($satkers ?? [])->values();

        if ($satkers->isEmpty()) {
            $satkers = $records
                ->pluck('satker')
                ->filter()
                ->unique('id')
                ->sortBy('nama')
                ->values();
        }

        $jenisGroups = $recordsByJenis->isEmpty()
            ? collect(['Nihil' => collect()])
            : $recordsByJenis;
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
        $dokumenGiatColumns = collect([['key' => 'lidik', 'label' => 'LIDIK'], ['key' => 'sidik', 'label' => 'SIDIK']]);
        $rekapPenyelesaianColumns = $penyelesaianColumns->map(
            fn($column) => [
                'key' => $column['key'],
                'label' => strtoupper((string) $column['label']),
                'id' => $column['id'],
            ],
        );
        $totalPenyelesaianPerkara = collect($recapData['totals']['penyelesaian_counts'] ?? [])->sum();
        $totalKasusKeseluruhan = (int) $records->count();
        $persentasePenyelesaianPerkara =
            $totalKasusKeseluruhan > 0
                ? number_format(($totalPenyelesaianPerkara / $totalKasusKeseluruhan) * 100, 2, ',', '.')
                : '0,00';

        $detailPenyelesaianColumnCount = max(1, $rekapPenyelesaianColumns->count());
        $detailVerticalColumnCount = $dokumenGiatColumns->count() + $detailPenyelesaianColumnCount;
        $detailNoWidth = 3.0;
        $detailSatuanWidth = 4.2;
        $detailLpWidth = 4.0;
        $detailTpPasalWidth = 5.8;
        $detailKorbanWidth = 15.0;
        $detailTersangkaWidth = 15.0;
        $detailHubunganWidth = 4.0;
        $detailKetWidth = 3.8;
        $detailVerticalWidth = 1.0;
        $detailFixedWidth = $detailNoWidth
            + $detailSatuanWidth
            + $detailLpWidth
            + $detailTpPasalWidth
            + $detailKorbanWidth
            + $detailTersangkaWidth
            + $detailHubunganWidth
            + $detailKetWidth
            + ($detailVerticalColumnCount * $detailVerticalWidth);
        $detailKronologiWidth = max(18.0, 100 - $detailFixedWidth);
        $detailIdentitasWidth = $detailKorbanWidth + $detailTersangkaWidth;
        $detailDokumenWidth = $dokumenGiatColumns->count() * $detailVerticalWidth;
        $detailPenyelesaianWidth = $detailPenyelesaianColumnCount * $detailVerticalWidth;

        $pasalInlineLimit = 45;
        $tpPasalNotes = [];
        $tpPasalNoteNumberByText = [];
        $rekapRows = collect($recapData['rows'] ?? [])
            ->values()
            ->map(function ($row) use ($pasalInlineLimit, &$tpPasalNotes, &$tpPasalNoteNumberByText): array {
                $row = (array) $row;
                $fullPasal = trim((string) ($row['pasal'] ?? ''));

                if ($fullPasal === '' || $fullPasal === '-') {
                    $row['pasal_display'] = '-';

                    return $row;
                }

                if (\Illuminate\Support\Str::length($fullPasal) <= $pasalInlineLimit) {
                    $row['pasal_display'] = $fullPasal;

                    return $row;
                }

                $normalizedPasal = preg_replace('/\s+/u', ' ', $fullPasal);
                $normalizedPasal = is_string($normalizedPasal) ? trim($normalizedPasal) : $fullPasal;

                if (! array_key_exists($normalizedPasal, $tpPasalNoteNumberByText)) {
                    $noteNumber = count($tpPasalNotes) + 1;
                    $tpPasalNoteNumberByText[$normalizedPasal] = $noteNumber;
                    $tpPasalNotes[$noteNumber] = $fullPasal;
                }

                $noteNumber = $tpPasalNoteNumberByText[$normalizedPasal];
                $row['pasal_display'] = \Illuminate\Support\Str::limit($fullPasal, $pasalInlineLimit, '…').' ['.$noteNumber.']';

                return $row;
            });
    @endphp

    @foreach ($jenisGroups as $jenisKasus => $groupedRecords)
        <div class="detail-section">
            @if (! $loop->first)
                <div class="detail-title">{{ strtoupper($jenisKasus) }}</div>
            @endif
            <table class="detail-table">
                @if ($loop->first)
                    <caption class="main-title-caption">
                        <div>{{ $mainTitle }}</div>
                        <div class="detail-line">{{ strtoupper($jenisKasus) }}</div>
                    </caption>
                @endif
                <thead>
                    <tr>
                        <th rowspan="2" class="num" style="width: {{ $detailNoWidth }}%;">NO</th>
                        <th rowspan="2" style="width: {{ $detailSatuanWidth }}%;">SATUAN</th>
                        <th rowspan="2" style="width: {{ $detailLpWidth }}%;">LAPORAN<br>POLISI/TGL</th>
                        <th rowspan="2" class="detail-kronologi-head" style="width: {{ $detailKronologiWidth }}%;">KRONOLOGIS KEJADIAN</th>
                        <th rowspan="2" style="width: {{ $detailTpPasalWidth }}%;">TINDAK<br>PIDANA/PASAL</th>
                        <th colspan="2" style="width: {{ $detailIdentitasWidth }}%;">IDENTITAS</th>
                        <th rowspan="2" style="width: {{ $detailHubunganWidth }}%;">HUB T.<br>DG. K</th>
                        <th colspan="{{ $dokumenGiatColumns->count() }}" style="width: {{ $detailDokumenWidth }}%;">DOKUMEN/<br>GIAT</th>
                        <th colspan="{{ max(1, $rekapPenyelesaianColumns->count()) }}" style="width: {{ $detailPenyelesaianWidth }}%;">PENYELESAIAN PERKARA</th>
                        @if ($rekapPenyelesaianColumns->isEmpty())
                            <th rowspan="2" style="width: {{ $detailVerticalWidth }}%;">-</th>
                        @endif
                        <th rowspan="2" class="vertical-head" style="width: {{ $detailKetWidth }}%;"><span>KET</span></th>
                    </tr>
                    <tr>
                        <th class="detail-identitas-subhead" style="width: {{ $detailKorbanWidth }}%;">KORBAN</th>
                        <th class="detail-identitas-subhead" style="width: {{ $detailTersangkaWidth }}%;">TERSANGKA</th>
                        @foreach ($dokumenGiatColumns as $column)
                            <th class="vertical-head" style="width: {{ $detailVerticalWidth }}%;"><span>{{ strtoupper((string) $column['label']) }}</span></th>
                        @endforeach
                        @forelse ($rekapPenyelesaianColumns as $column)
                            <th class="vertical-head" style="width: {{ $detailVerticalWidth }}%;"><span>{{ strtoupper((string) $column['label']) }}</span></th>
                        @empty
                            <th class="vertical-head" style="width: {{ $detailVerticalWidth }}%;"><span>-</span></th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @php
                        $recordsBySatker = $groupedRecords->groupBy(fn($record) => (int) ($record->satker_id ?? 0));
                        $rowNumber = 1;
                    @endphp
                    @foreach ($satkers as $satker)
                        @php
                            $satkerRecords = $recordsBySatker->get((int) $satker->id, collect());
                        @endphp

                        @if ($satkerRecords->isEmpty())
                            <tr>
                                <td class="num">{{ $rowNumber++ }}</td>
                                <td>{{ $satker->nama }}</td>
                                <td class="center">NIHIL</td>
                                <td class="center detail-kronologi-cell">NIHIL</td>
                                <td class="center">NIHIL</td>
                                <td class="center detail-identitas-cell">NIHIL</td>
                                <td class="center detail-identitas-cell">NIHIL</td>
                                <td class="center">NIHIL</td>
                                <td class="vertical-cell">-</td>
                                <td class="vertical-cell">-</td>
                                @foreach ($rekapPenyelesaianColumns as $column)
                                    <td class="vertical-cell">-</td>
                                @endforeach
                                @if ($rekapPenyelesaianColumns->isEmpty())
                                    <td class="vertical-cell">-</td>
                                @endif
                                <td class="small center">NIHIL</td>
                            </tr>
                        @else
                            @foreach ($satkerRecords as $record)
                                @php
                                    $korbanText = $record->korbans->pluck('nama')->join(', ');
                                    $tersangkaText = $record->tersangkas->pluck('nama')->join(', ');
                                    $penyelesaianId = (int) ($record->penyelesaian_id ?? 0);
                                @endphp
                                <tr>
                                    <td class="num">{{ $rowNumber++ }}</td>
                                    <td>{{ $record->satker?->nama ?? $satker->nama }}</td>
                                    <td class="small">{{ $record->nomor_lp }}<br>{{ $record->tanggal_lp?->format('d-m-Y') }}
                                    </td>
                                    <td class="detail-kronologi-cell">{{ $record->kronologi_kejadian ?: '-' }}</td>
                                    <td>{{ $record->tindak_pidana_pasal ?: '-' }}</td>
                                    <td class="detail-identitas-cell">{{ $korbanText !== '' ? $korbanText : '-' }}</td>
                                    <td class="detail-identitas-cell">{{ $tersangkaText !== '' ? $tersangkaText : '-' }}</td>
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
                                            {{ $record->latestRtl->tanggal?->format('d-m-Y') ?? '-' }} -
                                            {{ $record->latestRtl->keterangan ?: '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

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
                @foreach ($rekapRows as $row)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $row['jenis'] }}</td>
                        <td>{{ $row['pasal_display'] ?? '-' }}</td>
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
                <tr>
                    <td colspan="8"></td>
                    <td class="center" colspan="{{ max(1, $rekapPenyelesaianColumns->count()) + 1 }}">
                        <strong>TOTAL PENYELESAIAN: {{ $totalPenyelesaianPerkara }} &nbsp;&nbsp; PERSENTASE:
                            {{ $persentasePenyelesaianPerkara }}%</strong>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        @if ($tpPasalNotes !== [])
            <div class="rekap-notes">
                <div class="rekap-notes-title">Catatan TP.Pasal:</div>
                <ol>
                    @foreach ($tpPasalNotes as $noteNumber => $pasalText)
                        <li><strong>[{{ $noteNumber }}]</strong> {{ $pasalText }}</li>
                    @endforeach
                </ol>
            </div>
        @endif
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
