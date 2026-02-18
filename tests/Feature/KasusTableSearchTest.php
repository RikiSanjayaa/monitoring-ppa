<?php

namespace Tests\Feature;

use App\Enums\DokumenStatus;
use App\Enums\UserRole;
use App\Filament\Resources\KasusResource\Pages\ListKasuses;
use App\Models\Kasus;
use App\Models\Perkara;
use App\Models\Satker;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KasusTableSearchTest extends TestCase
{
    use RefreshDatabase;

    private int $lpCounter = 1;

    public function test_kasus_table_search_uses_nomor_lp_korban_and_tersangka_names(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'satker_id' => null,
        ]));

        $satker = Satker::query()->create([
            'nama' => 'Satker UX',
            'tipe' => 'subdit',
            'kode' => 'SATKER-UX',
        ]);
        $perkara = Perkara::query()->create([
            'nama' => 'Perkara UX Search',
            'is_active' => true,
        ]);

        $kasusByNomorLp = $this->createKasus($satker, $perkara, [
            'nomor_lp' => 'LP/UX/001',
        ]);

        $kasusByKorban = $this->createKasus($satker, $perkara, [
            'nomor_lp' => 'LP/UX/002',
        ]);
        $kasusByKorban->korbans()->create([
            'nama' => 'Nadia Korban',
        ]);

        $kasusByTersangka = $this->createKasus($satker, $perkara, [
            'nomor_lp' => 'LP/UX/003',
        ]);
        $kasusByTersangka->tersangkas()->create([
            'nama' => 'Rizky Tersangka',
        ]);

        $kasusLain = $this->createKasus($satker, $perkara, [
            'nomor_lp' => 'LP/UX/004',
        ]);
        $kasusLain->korbans()->create([
            'nama' => 'Nama Lain',
        ]);
        $kasusLain->tersangkas()->create([
            'nama' => 'Pelaku Lain',
        ]);

        Livewire::test(ListKasuses::class)
            ->searchTable('LP/UX/001')
            ->assertCanSeeTableRecords([$kasusByNomorLp])
            ->assertCanNotSeeTableRecords([$kasusByKorban, $kasusByTersangka, $kasusLain]);

        Livewire::test(ListKasuses::class)
            ->searchTable('Nadia Korban')
            ->assertCanSeeTableRecords([$kasusByKorban])
            ->assertCanNotSeeTableRecords([$kasusByNomorLp, $kasusByTersangka, $kasusLain]);

        Livewire::test(ListKasuses::class)
            ->searchTable('Rizky Tersangka')
            ->assertCanSeeTableRecords([$kasusByTersangka])
            ->assertCanNotSeeTableRecords([$kasusByNomorLp, $kasusByKorban, $kasusLain]);

        Livewire::test(ListKasuses::class)
            ->searchTable('Perkara UX Search')
            ->assertCanNotSeeTableRecords([$kasusByNomorLp, $kasusByKorban, $kasusByTersangka, $kasusLain]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createKasus(Satker $satker, Perkara $perkara, array $overrides = []): Kasus
    {
        return Kasus::query()->create(array_merge([
            'satker_id' => $satker->id,
            'nomor_lp' => sprintf('LP/%s/%03d/2026', $satker->kode, $this->lpCounter++),
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
            'penyelesaian_id' => null,
        ], $overrides));
    }
}
