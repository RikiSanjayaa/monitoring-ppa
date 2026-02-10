<?php

namespace Database\Seeders;

use App\Models\Petugas;
use App\Models\Satker;
use Illuminate\Database\Seeder;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Satker::query()->orderBy('id')->get()->each(function (Satker $satker): void {
            foreach (range(1, 3) as $index) {
                $nrp = sprintf('%s-%03d', $satker->kode, $index);

                Petugas::query()->updateOrCreate(
                    [
                        'satker_id' => $satker->id,
                        'nrp' => $nrp,
                    ],
                    [
                        'nama' => sprintf('Petugas %s %d', $satker->kode, $index),
                        'pangkat' => match ($index) {
                            1 => 'IPTU',
                            2 => 'IPDA',
                            default => 'AIPDA',
                        },
                        'no_hp' => '08123'.str_pad((string) ($satker->id * 100 + $index), 7, '0', STR_PAD_LEFT),
                    ],
                );
            }
        });
    }
}
