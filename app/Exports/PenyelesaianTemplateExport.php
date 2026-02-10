<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenyelesaianTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nomor LP',
            'Penyelesaian',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['LP/001/I/2026', 'P21'],
        ];
    }
}
