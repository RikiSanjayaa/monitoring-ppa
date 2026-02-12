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
     * @param  Collection<int, array{key: string, label: string, id: int}>  $penyelesaianColumns
     */
    public function __construct(
        private readonly Collection $summary,
        private readonly Collection $penyelesaianColumns,
    ) {}

    public static function fromQuery(Builder $query): self
    {
        $records = (clone $query)
            ->with('satker:id,nama')
            ->get();
        $penyelesaianColumns = KasusSummary::penyelesaianColumns($records);

        return new self(
            KasusSummary::fromCollection($records, $penyelesaianColumns),
            $penyelesaianColumns,
        );
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
        return array_merge([
            'Unit Kerja',
            'Jumlah',
            'Lidik',
            'Sidik',
        ], $this->penyelesaianColumns->pluck('label')->all());
    }

    /**
     * @param  array<string, int|string>  $row
     * @return array<int, int|string>
     */
    public function map($row): array
    {
        $mapped = [
            $row['unit_kerja'],
            $row['jumlah'],
            $row['lidik'],
            $row['sidik'],
        ];

        foreach ($this->penyelesaianColumns as $column) {
            $mapped[] = $row[$column['key']] ?? 0;
        }

        return $mapped;
    }
}
