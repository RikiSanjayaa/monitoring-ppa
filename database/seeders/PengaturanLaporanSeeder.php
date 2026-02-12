<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\PengaturanLaporan;
use App\Models\User;
use Illuminate\Database\Seeder;

class PengaturanLaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->whereIn('role', [UserRole::SuperAdmin, UserRole::Admin])
            ->orderBy('id')
            ->get()
            ->each(function (User $user): void {
                PengaturanLaporan::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'satker_id' => $user->isAdmin() ? $user->satker_id : null,
                        'kop_baris_1' => 'KEPOLISIAN NEGARA REPUBLIK INDONESIA',
                        'kop_baris_2' => 'DAERAH NUSA TENGGARA BARAT',
                        'kop_baris_3' => 'DIREKTORAT RESERSE PPA DAN PPO',
                        'judul_utama' => 'AUTO_TITLE',
                        'judul_rekap' => 'AUTO_RECAP_TITLE',
                        'ttd_baris_1' => 'An. KEPALA KEPOLISIAN DAERAH NUSA TENGGARA BARAT',
                        'ttd_baris_2' => 'DIRRES PPA DAN PPO POLDA NTB',
                        'ttd_nama' => 'BAMBANG PAMUNGKAS,S.I.K.,M.M.',
                        'ttd_pangkat_nrp' => 'KOMBESPOL NRP 12345678',
                    ],
                );
            });
    }
}
