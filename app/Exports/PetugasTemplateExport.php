<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PetugasTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nama',
            'NRP',
            'Pangkat',
            'No HP',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [
                'Contoh Nama Petugas',
                '7407XXXX',
                'IPTU',
                '08123XXXXXXX',
            ],
        ];
    }
}
