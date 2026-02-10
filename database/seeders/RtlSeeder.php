<?php

namespace Database\Seeders;

use App\Models\Kasus;
use App\Models\Rtl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RtlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kasus::query()->orderBy('id')->get()->each(function (Kasus $kasus): void {
            $tanggalDasar = $kasus->tanggal_lp instanceof Carbon
                ? $kasus->tanggal_lp
                : Carbon::parse($kasus->tanggal_lp);

            $timeline = [
                [
                    'tanggal' => $tanggalDasar->copy()->addDay()->toDateString(),
                    'keterangan' => 'Penerimaan laporan dan klarifikasi awal korban.',
                ],
                [
                    'tanggal' => $tanggalDasar->copy()->addDays(3)->toDateString(),
                    'keterangan' => $kasus->penyelesaian_id
                        ? 'Update penyelesaian: '.$kasus->penyelesaian?->nama.'.'
                        : 'Proses pendalaman perkara dan pemeriksaan saksi berjalan.',
                ],
            ];

            foreach ($timeline as $row) {
                Rtl::query()->updateOrCreate(
                    [
                        'kasus_id' => $kasus->id,
                        'tanggal' => $row['tanggal'],
                    ],
                    [
                        'keterangan' => $row['keterangan'],
                    ],
                );
            }
        });
    }
}
