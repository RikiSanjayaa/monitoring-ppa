<?php

namespace Tests\Feature;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\Perkara;
use App\Models\Satker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class KasusNomorLpUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_rejects_duplicate_nomor_lp_globally(): void
    {
        [$satkerA, $satkerB, $perkara] = $this->baseData();

        Kasus::query()->create([
            'satker_id' => $satkerA->id,
            'nomor_lp' => 'LP/A/001/II/2026/SPKT.SATRESKRIM/POLRES MATARAM/POLDA NTB',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
        ]);

        $this->expectException(ValidationException::class);

        Kasus::query()->create([
            'satker_id' => $satkerB->id,
            'nomor_lp' => 'LP/A/001/II/2026/SPKT.SATRESKRIM/POLRES MATARAM/POLDA NTB',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Sidik->value,
        ]);
    }

    public function test_update_keeps_legacy_duplicate_if_nomor_lp_is_not_changed(): void
    {
        [$satkerA, $satkerB, $perkara] = $this->baseData();

        $kasusA = Kasus::query()->create([
            'satker_id' => $satkerA->id,
            'nomor_lp' => 'LP/B/010/II/2026/SPKT.DITRES PPA DAN PPO/POLDA NTB',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
            'tindak_pidana_pasal' => 'Pasal Lama',
        ]);

        DB::table('kasus')->insert([
            'satker_id' => $satkerB->id,
            'nomor_lp' => 'LP/B/010/II/2026/SPKT.DITRES PPA DAN PPO/POLDA NTB',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Sidik->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kasusA->tindak_pidana_pasal = 'Pasal Baru';
        $kasusA->save();

        $this->assertSame('Pasal Baru', $kasusA->fresh()?->tindak_pidana_pasal);
    }

    public function test_update_rejects_when_changing_to_existing_nomor_lp(): void
    {
        [$satkerA, $satkerB, $perkara] = $this->baseData();

        Kasus::query()->create([
            'satker_id' => $satkerA->id,
            'nomor_lp' => 'LP/A/001/II/2026/SPKT.SATRESKRIM/POLRES MATARAM/POLDA NTB',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
        ]);

        $kasusB = Kasus::query()->create([
            'satker_id' => $satkerB->id,
            'nomor_lp' => 'LP/B/011/II/2026/SPKT.DITRES PPA DAN PPO/POLDA NTB',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Sidik->value,
        ]);

        $this->expectException(ValidationException::class);

        $kasusB->nomor_lp = 'LP/A/001/II/2026/SPKT.SATRESKRIM/POLRES MATARAM/POLDA NTB';
        $kasusB->save();
    }

    private function baseData(): array
    {
        $satkerA = Satker::query()->create([
            'nama' => 'Subdit 1',
            'tipe' => 'subdit',
            'kode' => 'SUBDIT-1',
        ]);
        $satkerB = Satker::query()->create([
            'nama' => 'Polres Mataram',
            'tipe' => 'polres',
            'kode' => 'POLRES-MATARAM',
        ]);
        $perkara = Perkara::query()->create([
            'nama' => 'KTP',
            'is_active' => true,
        ]);

        return [$satkerA, $satkerB, $perkara];
    }
}
