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
            'Kasus Kekerasan terhadap Perempuan (KTP)',
            'Kasus Kekerasan terhadap Anak (KTA)',
            'Kasus Anak Berhadapan dengan Hukum (ABH)',
            'Kasus Tindak Pidana Perdagangan Orang (TPPO)',
        ];

        foreach ($perkaras as $nama) {
            Perkara::query()->updateOrCreate(
                ['nama' => $nama],
                ['is_active' => true],
            );
        }
    }
}
