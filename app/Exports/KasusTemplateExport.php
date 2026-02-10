<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KasusTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nomor LP',
            'Tanggal LP',
            'Korban',
            'TTL',
            'Alamat',
            'HP',
            'Perkara',
            'Dokumen/Giat',
            'Petugas',
            'Penyelesaian',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [
                'LP/001/I/2026',
                '2026-01-10',
                'Nama Korban',
                'Mataram, 2000-01-01',
                'Alamat lengkap korban',
                '081234567890',
                'TPKS',
                'Lidik',
                'Petugas A, Petugas B',
                'P21',
            ],
        ];
    }
}
