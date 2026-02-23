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
            // 'Lidik',
            // 'Sidik',
            'SP2lid',
            'SP3',
            'P21',
            'Vonis',
            'Pelimpahan',
            'Diversi',
            'RJ',
        ];

        foreach ($items as $index => $nama) {
            Penyelesaian::query()->updateOrCreate(
                ['nama' => $nama],
                [
                    'is_active' => true,
                    'urutan' => $index + 1,
                ],
            );
        }
    }
}
