<?php

namespace Tests\Feature;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\Perkara;
use App\Models\Satker;
use App\Support\KasusRecapSummary;
use App\Support\KasusTemplateSpreadsheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasusExportSatkerCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_and_pdf_exports_keep_all_satker_rows_in_configured_order(): void
    {
        $satkerA = Satker::query()->create([
            'nama' => 'Satker A',
            'tipe' => 'subdit',
            'kode' => 'SATKER-A',
            'urutan' => 1,
        ]);

        $satkerB = Satker::query()->create([
            'nama' => 'Satker B',
            'tipe' => 'subdit',
            'kode' => 'SATKER-B',
            'urutan' => 2,
        ]);

        $perkara = Perkara::query()->create([
            'nama' => 'KTP',
            'is_active' => true,
        ]);

        Kasus::query()->create([
            'satker_id' => $satkerB->id,
            'nomor_lp' => 'LP/1',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
        ]);

        $records = Kasus::query()
            ->with([
                'satker:id,nama',
                'perkara:id,nama',
                'penyelesaian:id,nama',
                'petugas:id,nama',
                'korbans:id,kasus_id,nama',
                'tersangkas:id,kasus_id,nama',
                'saksis:id,kasus_id,nama',
                'latestRtl',
            ])
            ->get();

        $satkers = Satker::query()->ordered()->get();

        $spreadsheet = KasusTemplateSpreadsheet::build($records, $satkers);
        $sheet = $spreadsheet->getSheet(0);

        $this->assertSame('Satker A', $sheet->getCell('B9')->getValue());
        $this->assertSame('NIHIL', $sheet->getCell('C9')->getValue());
        $this->assertSame('Satker B', $sheet->getCell('B10')->getValue());
        $this->assertSame('LP/1'."\n".now()->format('d-m-Y'), $sheet->getCell('C10')->getValue());

        $html = view('exports.kasus-report', [
            'records' => $records,
            'printedAt' => now()->format('d-m-Y H:i'),
            'kopSuratLines' => ['A', 'B', 'C'],
            'mainTitle' => 'Laporan',
            'recapTitle' => 'Rekap',
            'signatureBlock' => [
                'line1' => 'L1',
                'line2' => 'L2',
                'name' => 'Nama',
                'rank' => 'Pangkat',
            ],
            'recapData' => KasusRecapSummary::fromCollection($records),
            'satkers' => $satkers,
        ])->render();

        $this->assertStringContainsString('Satker A', $html);
        $this->assertStringContainsString('Satker B', $html);
        $this->assertStringContainsString('NIHIL', $html);

        $this->assertLessThan(
            strpos($html, 'Satker B'),
            strpos($html, 'Satker A'),
        );
    }
}
