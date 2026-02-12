<?php

namespace App\Exports;

use App\Models\Petugas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PetugasExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  Collection<int, Petugas>  $records
     */
    public function __construct(
        private readonly Collection $records,
    ) {}

    public static function fromQuery(Builder $query): self
    {
        $records = (clone $query)
            ->with('satker:id,nama')
            ->orderBy('nama')
            ->get();

        return new self($records);
    }

    /**
     * @return Collection<int, Petugas>
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
            'Satker',
            'Nama',
            'NRP',
            'Pangkat',
            'No HP',
        ];
    }

    /**
     * @param  Petugas  $row
     * @return array<int, string>
     */
    public function map($row): array
    {
        return [
            $row->satker?->nama ?? '-',
            (string) $row->nama,
            (string) ($row->nrp ?? '-'),
            (string) ($row->pangkat ?? '-'),
            (string) ($row->no_hp ?? '-'),
        ];
    }
}
