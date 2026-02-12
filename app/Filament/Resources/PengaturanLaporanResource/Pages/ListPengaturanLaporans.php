<?php

namespace App\Filament\Resources\PengaturanLaporanResource\Pages;

use App\Filament\Resources\PengaturanLaporanResource;
use App\Models\PengaturanLaporan;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPengaturanLaporans extends ListRecords
{
    protected static string $resource = PengaturanLaporanResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        abort_unless($user, 403);

        $record = PengaturanLaporan::query()->firstOrCreate(
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

        $this->redirect(static::getResource()::getUrl('edit', ['record' => $record]));
    }
}
