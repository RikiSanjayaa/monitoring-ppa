<?php

namespace Database\Seeders;

use App\Models\Satker;
use Illuminate\Database\Seeder;

class SatkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satkers = [
            ['nama' => 'Subdit 1 Perempuan', 'tipe' => 'subdit', 'kode' => 'SUBDIT-1', 'urutan' => 1],
            ['nama' => 'Subdit 2 Anak', 'tipe' => 'subdit', 'kode' => 'SUBDIT-2', 'urutan' => 2],
            ['nama' => 'Subdit 3 TPPO', 'tipe' => 'subdit', 'kode' => 'SUBDIT-3', 'urutan' => 3],
            ['nama' => 'Polres Mataram', 'tipe' => 'polres', 'kode' => 'POLRES-MATARAM', 'urutan' => 4],
            ['nama' => 'Polres Lombok Barat', 'tipe' => 'polres', 'kode' => 'POLRES-LBAR', 'urutan' => 5],
            ['nama' => 'Polres Lombok Utara', 'tipe' => 'polres', 'kode' => 'POLRES-LUTARA', 'urutan' => 6],
            ['nama' => 'Polres Lombok Tengah', 'tipe' => 'polres', 'kode' => 'POLRES-LTENGAH', 'urutan' => 7],
            ['nama' => 'Polres Lombok Timur', 'tipe' => 'polres', 'kode' => 'POLRES-LTIMUR', 'urutan' => 8],
            ['nama' => 'Polres Sumbawa Barat', 'tipe' => 'polres', 'kode' => 'POLRES-SUMBAWA-BARAT', 'urutan' => 9],
            ['nama' => 'Polres Sumbawa', 'tipe' => 'polres', 'kode' => 'POLRES-SUMBAWA', 'urutan' => 10],
            ['nama' => 'Polres Dompu', 'tipe' => 'polres', 'kode' => 'POLRES-DOMPU', 'urutan' => 11],
            ['nama' => 'Polres Bima', 'tipe' => 'polres', 'kode' => 'POLRES-BIMA', 'urutan' => 12],
            ['nama' => 'Polres Bima Kota', 'tipe' => 'polres', 'kode' => 'POLRES-BIMAKOTA', 'urutan' => 13],
        ];

        foreach ($satkers as $satker) {
            Satker::query()->updateOrCreate(
                ['kode' => $satker['kode']],
                [
                    'nama' => $satker['nama'],
                    'tipe' => $satker['tipe'],
                    'urutan' => $satker['urutan'],
                ],
            );
        }
    }
}
