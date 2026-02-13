<?php

namespace Tests\Unit;

use App\Support\ExportDocumentTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExportDocumentTemplateTest extends TestCase
{
    public function test_automatic_titles_use_period_date_when_provided(): void
    {
        $records = Collection::make([
            [
                'tanggal_lp' => Carbon::parse('2026-01-15'),
                'perkara' => ['nama' => 'TPPO'],
                'satker' => ['nama' => 'Polres A'],
            ],
        ]);

        $titles = ExportDocumentTemplate::automaticTitles(
            $records,
            periodDate: Carbon::parse('2026-02-01'),
        );

        $this->assertStringContainsString('BULAN FEBRUARI TH 2026', $titles['main']);
        $this->assertStringContainsString('BULAN FEBRUARI TH 2026', $titles['recap']);
    }

    public function test_automatic_titles_use_earliest_tanggal_lp_when_period_missing(): void
    {
        $records = Collection::make([
            [
                'tanggal_lp' => Carbon::parse('2026-02-10'),
                'perkara' => ['nama' => 'TPPO'],
                'satker' => ['nama' => 'Polres A'],
            ],
            [
                'tanggal_lp' => Carbon::parse('2026-01-03'),
                'perkara' => ['nama' => 'TPPO'],
                'satker' => ['nama' => 'Polres A'],
            ],
        ]);

        $titles = ExportDocumentTemplate::automaticTitles($records);

        $this->assertStringContainsString('BULAN JANUARI TH 2026', $titles['main']);
        $this->assertStringContainsString('BULAN JANUARI TH 2026', $titles['recap']);
    }
}
