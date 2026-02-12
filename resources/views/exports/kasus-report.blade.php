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
                    <th rowspan="2">HENTI LIDIK</th>
                    <th rowspan="2">SIDIK</th>
                    <th rowspan="2">SP3</th>
                    <th rowspan="2">P21</th>
                    <th rowspan="2">VONIS</th>
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
                        $status = strtolower((string) ($record->penyelesaian?->nama ?? ''));
                        $korbanText = $record->korbans->pluck('nama')->join(', ');
                        $tersangkaText = $record->tersangkas->pluck('nama')->join(', ');
                    @endphp
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $record->satker?->nama }}</td>
                        <td class="small">{{ $record->nomor_lp }}<br>{{ $record->tanggal_lp?->format('d-m-Y') }}</td>
                        <td>{{ $record->kronologi_kejadian ?: '-' }}</td>
                        <td>{{ $record->tindak_pidana_pasal ?: '-' }}</td>
                        <td>{{ $korbanText !== '' ? $korbanText : ($record->nama_korban ?: '-') }}</td>
                        <td>{{ $tersangkaText !== '' ? $tersangkaText : ($record->nama_pelaku ?: '-') }}</td>
                        <td>{{ $record->hubungan_pelaku_dengan_korban ?: '-' }}</td>
                        <td class="center">{{ $record->dokumen_status?->value === 'lidik' ? '1' : '' }}</td>
                        <td class="center">{{ str_contains($status, 'henti') ? '1' : '' }}</td>
                        <td class="center">{{ $record->dokumen_status?->value === 'sidik' ? '1' : '' }}</td>
                        <td class="center">{{ str_contains($status, 'sp3') ? '1' : '' }}</td>
                        <td class="center">{{ str_contains($status, 'p21') ? '1' : '' }}</td>
                        <td class="center"></td>
                        <td class="small">{{ $record->saksis->pluck('nama')->join(', ') }}</td>
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
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            <col class="num-col">
            <col class="ket">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" class="num">NO</th>
                <th rowspan="2">JENIS TP</th>
                <th rowspan="2">TP.PASAL</th>
                <th colspan="3">JUMLAH</th>
                <th colspan="7">PENYELESAIAN PERKARA</th>
                <th rowspan="2">JML</th>
                <th rowspan="2">KET</th>
            </tr>
            <tr>
                <th>KORBAN</th>
                <th>TERSANGKA</th>
                <th>SAKSI</th>
                <th>LIDIK</th>
                <th>HENTI LIDIK</th>
                <th>SIDIK</th>
                <th>SP3</th>
                <th>P21</th>
                <th>DICABUT</th>
                <th>LIMPAH</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groups = $records->groupBy(fn($record) => $record->perkara?->nama ?? 'Lainnya')->values();
            @endphp
            @foreach ($groups as $group)
                @php
                    $first = $group->first();
                    $jumlahKorban = $group->sum(
                        fn($k) => $k->korbans->count() > 0 ? $k->korbans->count() : (filled($k->nama_korban) ? 1 : 0),
                    );
                    $jumlahTersangka = $group->sum(
                        fn($k) => $k->tersangkas->count() > 0 ? $k->tersangkas->count() : (filled($k->nama_pelaku) ? 1 : 0),
                    );
                    $jumlahSaksi = $group->sum(fn($k) => $k->saksis->count());
                    $lidik = $group->where('dokumen_status.value', 'lidik')->count();
                    $hentiLidik = $group
                        ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'henti'))
                        ->count();
                    $sidik = $group->where('dokumen_status.value', 'sidik')->count();
                    $sp3 = $group
                        ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'sp3'))
                        ->count();
                    $p21 = $group
                        ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'p21'))
                        ->count();
                    $dicabut = $group
                        ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'cabut'))
                        ->count();
                    $limpah = $group
                        ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'limpah'))
                        ->count();
                    $jumlah = $lidik + $hentiLidik + $sidik + $sp3 + $p21 + $dicabut + $limpah;
                @endphp
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $first?->perkara?->nama ?? 'Lainnya' }}</td>
                    <td>{{ $first?->tindak_pidana_pasal ?: '-' }}</td>
                    <td class="center">{{ $jumlahKorban }}</td>
                    <td class="center">{{ $jumlahTersangka }}</td>
                    <td class="center">{{ $jumlahSaksi }}</td>
                    <td class="center">{{ $lidik }}</td>
                    <td class="center">{{ $hentiLidik }}</td>
                    <td class="center">{{ $sidik }}</td>
                    <td class="center">{{ $sp3 }}</td>
                    <td class="center">{{ $p21 }}</td>
                    <td class="center">{{ $dicabut }}</td>
                    <td class="center">{{ $limpah }}</td>
                    <td class="center">{{ $jumlah }}</td>
                    <td></td>
                </tr>
            @endforeach
            @php
                $totalKorban = $records->sum(
                    fn($k) => $k->korbans->count() > 0 ? $k->korbans->count() : (filled($k->nama_korban) ? 1 : 0),
                );
                $totalTersangka = $records->sum(
                    fn($k) => $k->tersangkas->count() > 0 ? $k->tersangkas->count() : (filled($k->nama_pelaku) ? 1 : 0),
                );
                $totalSaksi = $records->sum(fn($k) => $k->saksis->count());
                $totalLidik = $records->where('dokumen_status.value', 'lidik')->count();
                $totalHentiLidik = $records
                    ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'henti'))
                    ->count();
                $totalSidik = $records->where('dokumen_status.value', 'sidik')->count();
                $totalSp3 = $records
                    ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'sp3'))
                    ->count();
                $totalP21 = $records
                    ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'p21'))
                    ->count();
                $totalDicabut = $records
                    ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'cabut'))
                    ->count();
                $totalLimpah = $records
                    ->filter(fn($k) => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'limpah'))
                    ->count();
                $grandTotal =
                    $totalLidik + $totalHentiLidik + $totalSidik + $totalSp3 + $totalP21 + $totalDicabut + $totalLimpah;
            @endphp
            <tr>
                <td class="center" colspan="3"><strong>JUMLAH TOTAL</strong></td>
                <td class="center"><strong>{{ $totalKorban }}</strong></td>
                <td class="center"><strong>{{ $totalTersangka }}</strong></td>
                <td class="center"><strong>{{ $totalSaksi }}</strong></td>
                <td class="center"><strong>{{ $totalLidik }}</strong></td>
                <td class="center"><strong>{{ $totalHentiLidik }}</strong></td>
                <td class="center"><strong>{{ $totalSidik }}</strong></td>
                <td class="center"><strong>{{ $totalSp3 }}</strong></td>
                <td class="center"><strong>{{ $totalP21 }}</strong></td>
                <td class="center"><strong>{{ $totalDicabut }}</strong></td>
                <td class="center"><strong>{{ $totalLimpah }}</strong></td>
                <td class="center"><strong>{{ $grandTotal }}</strong></td>
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
