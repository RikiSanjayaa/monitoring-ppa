<?php

namespace Database\Seeders;

use App\Models\Perkara;
use Illuminate\Database\Seeder;

class PerkaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perkaras = [
            'TPKS',
            'Pemerkosaan',
            'Perzinaan',
            'TPPO',
        ];

        foreach ($perkaras as $nama) {
            Perkara::query()->updateOrCreate(
                ['nama' => $nama],
                ['is_active' => true],
            );
        }
    }
}
