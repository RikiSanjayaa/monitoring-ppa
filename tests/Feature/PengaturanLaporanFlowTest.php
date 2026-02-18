<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\PengaturanLaporanResource\Pages\ListPengaturanLaporans;
use App\Models\PengaturanLaporan;
use App\Models\Satker;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PengaturanLaporanFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_pengaturan_laporan_when_another_admin_in_same_satker_exists(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $satker = Satker::query()->create([
            'nama' => 'Polres Mataram',
            'tipe' => 'polres',
            'kode' => 'POLRES-MTRM',
        ]);

        $existingAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'satker_id' => $satker->id,
        ]);

        $newAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'satker_id' => $satker->id,
        ]);

        PengaturanLaporan::query()->create([
            'user_id' => $existingAdmin->id,
            'satker_id' => $satker->id,
            'kop_baris_1' => 'KEPOLISIAN NEGARA REPUBLIK INDONESIA',
            'kop_baris_2' => 'DAERAH NUSA TENGGARA BARAT',
            'kop_baris_3' => 'DIREKTORAT RESERSE PPA DAN PPO',
            'judul_utama' => 'AUTO_TITLE',
            'judul_rekap' => 'AUTO_RECAP_TITLE',
            'ttd_baris_1' => 'An. KEPALA KEPOLISIAN DAERAH NUSA TENGGARA BARAT',
            'ttd_baris_2' => 'DIRRES PPA DAN PPO POLDA NTB',
            'ttd_nama' => 'BAMBANG PAMUNGKAS,S.I.K.,M.M.',
            'ttd_pangkat_nrp' => 'KOMBESPOL NRP 12345678',
        ]);

        $this->actingAs($newAdmin);

        Livewire::test(ListPengaturanLaporans::class)
            ->assertRedirect();

        $this->assertDatabaseHas('pengaturan_laporans', [
            'user_id' => $newAdmin->id,
            'satker_id' => $satker->id,
        ]);

        $this->assertSame(
            2,
            PengaturanLaporan::query()
                ->where('satker_id', $satker->id)
                ->count(),
        );
    }
}
