<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KasusTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
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
            'Tempat Lahir Korban',
            'Tanggal Lahir Korban',
            'TTL',
            'Alamat',
            'HP',
            'Nama Tersangka',
            'Tempat Lahir Tersangka',
            'Tanggal Lahir Tersangka',
            'Alamat Tersangka',
            'HP Tersangka',
            'Daftar Saksi',
            'Jenis Kasus',
            'Tindak Pidana/Pasal',
            'Hubungan Tersangka dengan Korban',
            'Proses Pidana',
            'Kronologi Kejadian',
            'Laporan Polisi',
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
                'Mataram',
                '2000-01-01',
                'Mataram, 2000-01-01',
                'Alamat lengkap korban',
                '081234567890',
                'Nama Tersangka',
                'Lombok Barat',
                '1995-05-12',
                'Alamat tersangka',
                '081200000001',
                'Saksi A; Saksi B',
                'Kasus Kekerasan terhadap Perempuan',
                'Pasal 81 UU Perlindungan Anak',
                'Ayah kandung',
                'Penyidikan berjalan',
                'Kronologi singkat kejadian...',
                'Isi ringkas laporan polisi...',
                'Lidik',
                'Petugas A, Petugas B',
                'P21',
            ],
        ];
    }
}
