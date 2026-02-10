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
            ['nama' => 'Subdit 1 Perempuan', 'tipe' => 'subdit', 'kode' => 'SUBDIT-1'],
            ['nama' => 'Subdit 2 Anak', 'tipe' => 'subdit', 'kode' => 'SUBDIT-2'],
            ['nama' => 'Subdit 3 TPPO', 'tipe' => 'subdit', 'kode' => 'SUBDIT-3'],
            ['nama' => 'Polres Lombok Barat', 'tipe' => 'polres', 'kode' => 'POLRES-LBAR'],
            ['nama' => 'Polres Lombok Tengah', 'tipe' => 'polres', 'kode' => 'POLRES-LTENGAH'],
            ['nama' => 'Polres Lombok Timur', 'tipe' => 'polres', 'kode' => 'POLRES-LTIMUR'],
            ['nama' => 'Polres KSB', 'tipe' => 'polres', 'kode' => 'POLRES-KSB'],
            ['nama' => 'Polres Sumbawa', 'tipe' => 'polres', 'kode' => 'POLRES-SUMBAWA'],
            ['nama' => 'Polres Dompu', 'tipe' => 'polres', 'kode' => 'POLRES-DOMPU'],
            ['nama' => 'Polres Bima', 'tipe' => 'polres', 'kode' => 'POLRES-BIMA'],
            ['nama' => 'Polres Bima Kota', 'tipe' => 'polres', 'kode' => 'POLRES-BIMAKOTA'],
        ];

        foreach ($satkers as $satker) {
            Satker::query()->updateOrCreate(
                ['kode' => $satker['kode']],
                [
                    'nama' => $satker['nama'],
                    'tipe' => $satker['tipe'],
                ],
            );
        }
    }
}
