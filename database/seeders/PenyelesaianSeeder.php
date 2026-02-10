<?php

namespace Database\Seeders;

use App\Models\Penyelesaian;
use Illuminate\Database\Seeder;

class PenyelesaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'P21',
            'Henti Lidik',
            'SP3',
            'Diversi',
            'RJ',
            'Limpah',
        ];

        foreach ($items as $nama) {
            Penyelesaian::query()->updateOrCreate(
                ['nama' => $nama],
                ['is_active' => true],
            );
        }
    }
}
