<?php

namespace Database\Seeders;

use App\Enums\DokumenStatus;
use App\Enums\UserRole;
use App\Models\Kasus;
use App\Models\Penyelesaian;
use App\Models\Perkara;
use App\Models\Petugas;
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
                        'perkara_id' => $perkara?->id,
                        'tindak_pidana_pasal' => 'Pasal 81 UU Perlindungan Anak',
                        'hubungan_pelaku_dengan_korban' => $index % 2 === 0 ? 'Keluarga' : 'Tetangga',
                        'proses_pidana' => $index % 2 === 0 ? 'Penyidikan berjalan' : 'Pelimpahan berkas tahap awal',
                        'kronologi_kejadian' => sprintf('Kronologi kejadian kasus %s nomor %d.', $satker->kode, $index),
                        'laporan_polisi' => sprintf('Uraian laporan polisi kasus %s nomor %d.', $satker->kode, $index),
                        'dokumen_status' => $dokumenStatus,
                        'penyelesaian_id' => $penyelesaian?->id,
                        'created_by' => $admin?->id,
                    ],
                );

                $assignedPetugas = $petugasIds->slice(0, $index + 1)->values()->all();
                $kasus->petugas()->sync($assignedPetugas);

                $kasus->korbans()->delete();
                $kasus->korbans()->createMany([
                    [
                        'nama' => sprintf('Korban %s %dA', $satker->kode, $index),
                        'tempat_lahir' => 'Mataram',
                        'tanggal_lahir' => Carbon::now()->subYears(20 + $index)->toDateString(),
                        'alamat' => sprintf('Alamat korban A %s kasus %d', $satker->nama, $index),
                        'hp' => '08122'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                    ],
                    [
                        'nama' => sprintf('Korban %s %dB', $satker->kode, $index),
                        'tempat_lahir' => 'Lombok Tengah',
                        'tanggal_lahir' => Carbon::now()->subYears(18 + $index)->toDateString(),
                        'alamat' => sprintf('Alamat korban B %s kasus %d', $satker->nama, $index),
                        'hp' => '08123'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                    ],
                ]);

                $kasus->tersangkas()->delete();
                $kasus->tersangkas()->createMany([
                    [
                        'nama' => sprintf('Tersangka %s %dA', $satker->kode, $index),
                        'tempat_lahir' => 'Mataram',
                        'tanggal_lahir' => Carbon::now()->subYears(30 + $index)->toDateString(),
                        'alamat' => sprintf('Alamat tersangka A %s kasus %d', $satker->nama, $index),
                        'hp' => '08211'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                    ],
                    [
                        'nama' => sprintf('Tersangka %s %dB', $satker->kode, $index),
                        'tempat_lahir' => 'Lombok Timur',
                        'tanggal_lahir' => Carbon::now()->subYears(28 + $index)->toDateString(),
                        'alamat' => sprintf('Alamat tersangka B %s kasus %d', $satker->nama, $index),
                        'hp' => '08212'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                    ],
                ]);

                $kasus->saksis()->delete();
                $kasus->saksis()->createMany([
                    [
                        'nama' => sprintf('Saksi %s %dA', $satker->kode, $index),
                        'tempat_lahir' => 'Mataram',
                        'tanggal_lahir' => Carbon::now()->subYears(25 + $index)->toDateString(),
                        'alamat' => sprintf('Alamat saksi A %s kasus %d', $satker->nama, $index),
                        'hp' => '08311'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                    ],
                    [
                        'nama' => sprintf('Saksi %s %dB', $satker->kode, $index),
                        'tempat_lahir' => 'Lombok Barat',
                        'tanggal_lahir' => Carbon::now()->subYears(23 + $index)->toDateString(),
                        'alamat' => sprintf('Alamat saksi B %s kasus %d', $satker->nama, $index),
                        'hp' => '08312'.str_pad((string) ($satker->id * 10 + $index), 8, '0', STR_PAD_LEFT),
                    ],
                ]);
            }
        });
    }
}
