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
            'Lidik',
            'Henti Lidik',
            'Sidik',
            'SP3',
            'P21',
            'Vonis',
            'Pelimpahan',
        ];

        foreach ($items as $nama) {
            Penyelesaian::query()->updateOrCreate(
                ['nama' => $nama],
                ['is_active' => true],
            );
        }
    }
}
