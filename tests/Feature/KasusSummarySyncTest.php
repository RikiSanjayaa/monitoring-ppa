<?php

namespace Tests\Feature;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\Penyelesaian;
use App\Models\Perkara;
use App\Models\Satker;
use App\Support\KasusRecapSummary;
use App\Support\KasusSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasusSummarySyncTest extends TestCase
{
    use RefreshDatabase;

    private int $lpCounter = 1;

    public function test_summary_uses_dynamic_penyelesaian_columns_from_master_table(): void
    {
        $satkerA = Satker::query()->create([
            'nama' => 'Satker A',
            'tipe' => 'subdit',
            'kode' => 'SATKER-A',
        ]);
        $satkerB = Satker::query()->create([
            'nama' => 'Satker B',
            'tipe' => 'subdit',
            'kode' => 'SATKER-B',
        ]);
        $perkara = Perkara::query()->create([
            'nama' => 'KTP',
            'is_active' => true,
        ]);

        Penyelesaian::query()->create(['nama' => 'Lidik', 'is_active' => true]);
        Penyelesaian::query()->create(['nama' => 'Sidik', 'is_active' => true]);
        $henti = Penyelesaian::query()->create(['nama' => 'Henti Lidik', 'is_active' => true]);
        $rj = Penyelesaian::query()->create(['nama' => 'Restorative Justice', 'is_active' => true]);
        $pelimpahan = Penyelesaian::query()->create(['nama' => 'Pelimpahan', 'is_active' => false]);

        $this->createKasus($satkerA, $perkara, [
            'dokumen_status' => DokumenStatus::Lidik->value,
        ]);
        $this->createKasus($satkerA, $perkara, [
            'dokumen_status' => DokumenStatus::Sidik->value,
            'penyelesaian_id' => $henti->id,
        ]);
        $this->createKasus($satkerA, $perkara, [
            'dokumen_status' => DokumenStatus::Sidik->value,
            'penyelesaian_id' => $pelimpahan->id,
        ]);
        $this->createKasus($satkerB, $perkara, [
            'dokumen_status' => DokumenStatus::Lidik->value,
            'penyelesaian_id' => $rj->id,
        ]);

        $records = Kasus::query()
            ->with('satker:id,nama')
            ->get();

        $columns = KasusSummary::penyelesaianColumns($records);
        $summary = KasusSummary::fromCollection($records, $columns);

        $this->assertSame(
            ['Henti Lidik', 'Restorative Justice', 'Pelimpahan'],
            $columns->pluck('label')->all(),
        );

        $keyByLabel = $columns->mapWithKeys(fn (array $column): array => [$column['label'] => $column['key']]);
        $hentiKey = $keyByLabel->get('Henti Lidik');
        $rjKey = $keyByLabel->get('Restorative Justice');
        $pelimpahanKey = $keyByLabel->get('Pelimpahan');

        $rowSatkerA = $summary->firstWhere('unit_kerja', 'Satker A');
        $rowSatkerB = $summary->firstWhere('unit_kerja', 'Satker B');
        $rowTotal = $summary->firstWhere('unit_kerja', 'TOTAL');

        $this->assertSame(3, $rowSatkerA['jumlah']);
        $this->assertSame(1, $rowSatkerA['lidik']);
        $this->assertSame(2, $rowSatkerA['sidik']);
        $this->assertSame(1, $rowSatkerA[$hentiKey]);
        $this->assertSame(0, $rowSatkerA[$rjKey]);
        $this->assertSame(1, $rowSatkerA[$pelimpahanKey]);

        $this->assertSame(1, $rowSatkerB['jumlah']);
        $this->assertSame(1, $rowSatkerB['lidik']);
        $this->assertSame(0, $rowSatkerB['sidik']);
        $this->assertSame(0, $rowSatkerB[$hentiKey]);
        $this->assertSame(1, $rowSatkerB[$rjKey]);
        $this->assertSame(0, $rowSatkerB[$pelimpahanKey]);

        $this->assertSame(4, $rowTotal['jumlah']);
        $this->assertSame(2, $rowTotal['lidik']);
        $this->assertSame(2, $rowTotal['sidik']);
        $this->assertSame(1, $rowTotal[$hentiKey]);
        $this->assertSame(1, $rowTotal[$rjKey]);
        $this->assertSame(1, $rowTotal[$pelimpahanKey]);
    }

    public function test_recap_summary_uses_same_dynamic_penyelesaian_columns(): void
    {
        $satker = Satker::query()->create([
            'nama' => 'Satker C',
            'tipe' => 'subdit',
            'kode' => 'SATKER-C',
        ]);

        $perkaraKtp = Perkara::query()->create([
            'nama' => 'KTP',
            'is_active' => true,
        ]);
        $perkaraKta = Perkara::query()->create([
            'nama' => 'KTA',
            'is_active' => true,
        ]);

        $henti = Penyelesaian::query()->create(['nama' => 'Henti Lidik', 'is_active' => true]);
        $pelimpahan = Penyelesaian::query()->create(['nama' => 'Pelimpahan', 'is_active' => true]);

        $kasusKtp1 = $this->createKasus($satker, $perkaraKtp, [
            'dokumen_status' => DokumenStatus::Lidik->value,
            'penyelesaian_id' => $henti->id,
        ]);
        $kasusKtp1->korbans()->createMany([
            ['nama' => 'Korban 1'],
            ['nama' => 'Korban 2'],
        ]);
        $kasusKtp1->tersangkas()->createMany([
            ['nama' => 'Tersangka 1'],
        ]);
        $kasusKtp1->saksis()->createMany([
            ['nama' => 'Saksi 1'],
            ['nama' => 'Saksi 2'],
        ]);

        $kasusKtp2 = $this->createKasus($satker, $perkaraKtp, [
            'dokumen_status' => DokumenStatus::Sidik->value,
            'penyelesaian_id' => $pelimpahan->id,
        ]);
        $kasusKtp2->korbans()->createMany([
            ['nama' => 'Korban Tambahan'],
        ]);
        $kasusKtp2->tersangkas()->createMany([
            ['nama' => 'Tersangka Tambahan'],
        ]);

        $kasusKta1 = $this->createKasus($satker, $perkaraKta, [
            'dokumen_status' => DokumenStatus::Sidik->value,
            'penyelesaian_id' => null,
        ]);
        $kasusKta1->korbans()->createMany([
            ['nama' => 'Korban KTA'],
        ]);
        $kasusKta1->tersangkas()->createMany([
            ['nama' => 'Pelaku KTA 1'],
            ['nama' => 'Pelaku KTA 2'],
        ]);
        $kasusKta1->saksis()->createMany([
            ['nama' => 'Saksi KTA'],
        ]);

        $records = Kasus::query()
            ->with([
                'perkara:id,nama',
                'korbans:id,kasus_id,nama',
                'tersangkas:id,kasus_id,nama',
                'saksis:id,kasus_id,nama',
            ])
            ->get();

        $columns = KasusSummary::penyelesaianColumns($records);
        $recap = KasusRecapSummary::fromCollection($records, $columns);

        $this->assertSame(['Henti Lidik', 'Pelimpahan'], $columns->pluck('label')->all());

        $keyByLabel = $columns->mapWithKeys(fn (array $column): array => [$column['label'] => $column['key']]);
        $hentiKey = $keyByLabel->get('Henti Lidik');
        $pelimpahanKey = $keyByLabel->get('Pelimpahan');

        $rowKtp = $recap['rows']->firstWhere('jenis', 'KTP');
        $rowKta = $recap['rows']->firstWhere('jenis', 'KTA');
        $totals = $recap['totals'];

        $this->assertSame(3, $rowKtp['jumlah_korban']);
        $this->assertSame(2, $rowKtp['jumlah_tersangka']);
        $this->assertSame(2, $rowKtp['jumlah_saksi']);
        $this->assertSame(1, $rowKtp['lidik']);
        $this->assertSame(1, $rowKtp['sidik']);
        $this->assertSame(1, $rowKtp['penyelesaian_counts'][$hentiKey]);
        $this->assertSame(1, $rowKtp['penyelesaian_counts'][$pelimpahanKey]);
        $this->assertSame(4, $rowKtp['jumlah']);

        $this->assertSame(1, $rowKta['jumlah_korban']);
        $this->assertSame(2, $rowKta['jumlah_tersangka']);
        $this->assertSame(1, $rowKta['jumlah_saksi']);
        $this->assertSame(0, $rowKta['lidik']);
        $this->assertSame(1, $rowKta['sidik']);
        $this->assertSame(0, $rowKta['penyelesaian_counts'][$hentiKey]);
        $this->assertSame(0, $rowKta['penyelesaian_counts'][$pelimpahanKey]);
        $this->assertSame(1, $rowKta['jumlah']);

        $this->assertSame(4, $totals['jumlah_korban']);
        $this->assertSame(4, $totals['jumlah_tersangka']);
        $this->assertSame(3, $totals['jumlah_saksi']);
        $this->assertSame(1, $totals['lidik']);
        $this->assertSame(2, $totals['sidik']);
        $this->assertSame(1, $totals['penyelesaian_counts'][$hentiKey]);
        $this->assertSame(1, $totals['penyelesaian_counts'][$pelimpahanKey]);
        $this->assertSame(5, $totals['jumlah']);
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
