<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Kasus</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1, h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #9ca3af; padding: 5px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
        .summary th.group-red { background: #fca5a5; }
        .summary th.group-blue { background: #93c5fd; }
        .summary th.group-green { background: #86efac; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Laporan Monitoring Penanganan Laporan Polisi</h1>
    <div class="meta">
        <div>Tanggal cetak: {{ $printedAt }}</div>
        <div>Total data: {{ $records->count() }}</div>
    </div>

    <h2>Ringkasan Per Satker</h2>
    <table class="summary">
        <thead>
            <tr>
                <th>Unit Kerja</th>
                <th class="group-red">Jumlah</th>
                <th class="group-blue">Lidik</th>
                <th class="group-blue">Sidik</th>
                <th class="group-green">Henti Lidik</th>
                <th class="group-green">P21</th>
                <th class="group-green">SP3</th>
                <th class="group-green">Diversi</th>
                <th class="group-green">RJ</th>
                <th class="group-green">Limpah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summary as $row)
                <tr class="{{ $row['unit_kerja'] === 'TOTAL' ? 'total-row' : '' }}">
                    <td>{{ $row['unit_kerja'] }}</td>
                    <td class="text-right">{{ $row['jumlah'] }}</td>
                    <td class="text-right">{{ $row['lidik'] }}</td>
                    <td class="text-right">{{ $row['sidik'] }}</td>
                    <td class="text-right">{{ $row['henti_lidik'] }}</td>
                    <td class="text-right">{{ $row['p21'] }}</td>
                    <td class="text-right">{{ $row['sp3'] }}</td>
                    <td class="text-right">{{ $row['diversi'] }}</td>
                    <td class="text-right">{{ $row['rj'] }}</td>
                    <td class="text-right">{{ $row['limpah'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Detail Kasus</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor LP</th>
                <th>Tgl LP</th>
                <th>Satker</th>
                <th>Korban</th>
                <th>Perkara</th>
                <th>Dokumen</th>
                <th>Petugas</th>
                <th>Penyelesaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr>
                    <td class="text-right">{{ $loop->iteration }}</td>
                    <td>{{ $record->nomor_lp }}</td>
                    <td>{{ $record->tanggal_lp?->format('d-m-Y') }}</td>
                    <td>{{ $record->satker?->nama }}</td>
                    <td>{{ $record->nama_korban }}</td>
                    <td>{{ $record->perkara?->nama }}</td>
                    <td>{{ strtoupper($record->dokumen_status?->value ?? '-') }}</td>
                    <td>{{ $record->petugas->pluck('nama')->join(', ') }}</td>
                    <td>{{ $record->penyelesaian?->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
