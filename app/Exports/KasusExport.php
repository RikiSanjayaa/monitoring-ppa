<?php

namespace App\Exports;

use App\Models\Kasus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KasusExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    /**
     * @param Collection<int, Kasus> $records
     */
    public function __construct(
        private readonly Collection $records,
    ) {
    }

    public static function fromQuery(Builder $query): self
    {
        $records = (clone $query)
            ->with(['satker:id,nama', 'perkara:id,nama', 'penyelesaian:id,nama', 'petugas:id,nama', 'latestRtl'])
            ->get();

        return new self($records);
    }

    /**
     * @return Collection<int, Kasus>
     */
    public function collection(): Collection
    {
        return $this->records;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nomor LP',
            'Tanggal LP',
            'Satker',
            'Nama Korban',
            'TTL',
            'Alamat',
            'HP',
            'Perkara',
            'Dokumen/Giat',
            'Petugas',
            'Penyelesaian',
            'RTL Terbaru',
        ];
    }

    /**
     * @param Kasus $row
     * @return array<int, string>
     */
    public function map($row): array
    {
        $ttl = trim(implode(', ', array_filter([
            $row->tempat_lahir_korban,
            $row->tanggal_lahir_korban?->format('d-m-Y'),
        ])));

        return [
            $row->nomor_lp,
            $row->tanggal_lp?->format('d-m-Y') ?? '-',
            $row->satker?->nama ?? '-',
            $row->nama_korban,
            $ttl,
            (string) $row->alamat_korban,
            (string) $row->hp_korban,
            $row->perkara?->nama ?? '-',
            strtoupper($row->dokumen_status?->value ?? '-'),
            $row->petugas->pluck('nama')->join(', '),
            $row->penyelesaian?->nama ?? '-',
            $row->latestRtl?->keterangan ?? '-',
        ];
    }
}
