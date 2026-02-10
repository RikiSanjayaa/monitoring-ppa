<?php

namespace Database\Seeders;

use App\Enums\DokumenStatus;
use App\Enums\UserRole;
use App\Models\Kasus;
use App\Models\Perkara;
use App\Models\Petugas;
use App\Models\Penyelesaian;
use App\Models\Satker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class KasusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perkaras = Perkara::query()->where('is_active', true)->orderBy('id')->get();
        $penyelesaians = Penyelesaian::query()->where('is_active', true)->orderBy('id')->get();

        Satker::query()->orderBy('id')->get()->each(function (Satker $satker) use ($perkaras, $penyelesaians): void {
            $admin = User::query()
                ->where('role', UserRole::Admin)
                ->where('satker_id', $satker->id)
                ->first();

            $petugasIds = Petugas::query()
                ->where('satker_id', $satker->id)
                ->orderBy('id')
                ->pluck('id')
                ->values();

            foreach (range(1, 2) as $index) {
                $tanggalLp = Carbon::now()->subDays(($satker->id * 4) + $index);
                $nomorLp = sprintf('LP/%s/%03d/2026', $satker->kode, $index);
                $perkara = $perkaras->get(($satker->id + $index) % max(1, $perkaras->count()));
                $dokumenStatus = $index % 2 === 0 ? DokumenStatus::Sidik->value : DokumenStatus::Lidik->value;

                $penyelesaian = null;
                if ($index === 2 && $penyelesaians->isNotEmpty()) {
                    $penyelesaian = $penyelesaians->get(($satker->id + $index) % $penyelesaians->count());
                }

                $kasus = Kasus::query()->updateOrCreate(
                    [
                        'satker_id' => $satker->id,
                        'nomor_lp' => $nomorLp,
                    ],
                    [
                        'tanggal_lp' => $tanggalLp->toDateString(),
                        'nama_korban' => sprintf('Korban %s %d', $satker->kode, $index),
                        'tempat_lahir_korban' => 'Mataram',
                        'tanggal_lahir_korban' => Carbon::now()->subYears(20 + $index)->toDateString(),
                        'alamat_korban' => sprintf('Alamat korban %s kasus %d', $satker->nama, $index),
                        'hp_korban' => '08122'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                        'perkara_id' => $perkara?->id,
                        'dokumen_status' => $dokumenStatus,
                        'penyelesaian_id' => $penyelesaian?->id,
                        'created_by' => $admin?->id,
                    ],
                );

                $assignedPetugas = $petugasIds->slice(0, $index + 1)->values()->all();
                $kasus->petugas()->sync($assignedPetugas);
            }
        });
    }
}
