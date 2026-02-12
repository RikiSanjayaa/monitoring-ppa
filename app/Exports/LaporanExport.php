<?php

namespace App\Exports;

use App\Support\KasusSummary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  Collection<int, array<string, int|string>>  $summary
     */
    public function __construct(
        private readonly Collection $summary,
    ) {}

    public static function fromQuery(Builder $query): self
    {
        return new self(KasusSummary::fromQuery($query));
    }

    /**
     * @return Collection<int, array<string, int|string>>
     */
    public function collection(): Collection
    {
        return $this->summary;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Unit Kerja',
            'Jumlah',
            'Lidik',
            'Sidik',
            'Henti Lidik',
            'P21',
            'SP3',
            'Diversi',
            'RJ',
            'Limpah',
        ];
    }

    /**
     * @param  array<string, int|string>  $row
     * @return array<int, int|string>
     */
    public function map($row): array
    {
        return [
            $row['unit_kerja'],
            $row['jumlah'],
            $row['lidik'],
            $row['sidik'],
            $row['henti_lidik'],
            $row['p21'],
            $row['sp3'],
            $row['diversi'],
            $row['rj'],
            $row['limpah'],
        ];
    }
}
