<?php

namespace App\Support;

use App\Models\Kasus;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KasusTemplateSpreadsheet
{
    private const TEMPLATE_PATH = 'doc_template/DATA KTP, KTA & ABH JANUARI 2026 7.xlsx';

    /**
     * @param  Collection<int, Kasus>  $records
     */
    public static function build(Collection $records, ?int $satkerId = null, ?int $userId = null): Spreadsheet
    {
        $spreadsheet = IOFactory::load(public_path(self::TEMPLATE_PATH));
        $sheet = $spreadsheet->getSheetByName('KTP TAHUN 2026') ?? $spreadsheet->getActiveSheet();

        self::applyTemplateOverrides($sheet, $records, $satkerId, $userId);
        self::fillDetailTable($sheet, $records);
        self::fillRecapTable($sheet, $records);

        return $spreadsheet;
    }

    /**
     * @param  Collection<int, Kasus>  $records
     */
    private static function fillDetailTable(Worksheet $sheet, Collection $records): void
    {
        $dataStartRow = 11;
        $section2TitleRow = 22;
        $templateCapacity = $section2TitleRow - $dataStartRow;
        $recordCount = $records->count();
        $extraRows = max(0, $recordCount - $templateCapacity);

        if ($extraRows > 0) {
            $sheet->insertNewRowBefore($section2TitleRow, $extraRows);
            for ($i = 0; $i < $extraRows; $i++) {
                $sourceRow = $section2TitleRow - 1;
                $targetRow = $sourceRow + $i + 1;
                $sheet->duplicateStyle($sheet->getStyle("A{$sourceRow}:O{$sourceRow}"), "A{$targetRow}:O{$targetRow}");
            }
        }

        $row = $dataStartRow;
        foreach ($records as $index => $record) {
            $penyelesaian = strtolower((string) ($record->penyelesaian?->nama ?? ''));
            $korban = $record->korbans->pluck('nama')->join(', ');
            $tersangka = $record->tersangkas->pluck('nama')->join(', ');

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $record->satker?->nama ?? '-');
            $sheet->setCellValue("C{$row}", trim(($record->nomor_lp ?? '-')."\n".($record->tanggal_lp?->format('d-m-Y') ?? '-')));
            $sheet->setCellValue("D{$row}", $record->kronologi_kejadian ?: '-');
            $sheet->setCellValue("E{$row}", $record->tindak_pidana_pasal ?: '-');
            $sheet->setCellValue("F{$row}", $korban !== '' ? $korban : ($record->nama_korban ?: '-'));
            $sheet->setCellValue("G{$row}", $tersangka !== '' ? $tersangka : ($record->nama_pelaku ?: '-'));
            $sheet->setCellValue("H{$row}", $record->hubungan_pelaku_dengan_korban ?: '-');
            $sheet->setCellValue("I{$row}", $record->dokumen_status?->value === 'lidik' ? '1' : '');
            $sheet->setCellValue("J{$row}", str_contains($penyelesaian, 'henti') ? '1' : '');
            $sheet->setCellValue("K{$row}", $record->dokumen_status?->value === 'sidik' ? '1' : '');
            $sheet->setCellValue("L{$row}", str_contains($penyelesaian, 'sp3') ? '1' : '');
            $sheet->setCellValue("M{$row}", str_contains($penyelesaian, 'p21') ? '1' : '');
            $sheet->setCellValue("N{$row}", str_contains($penyelesaian, 'vonis') ? '1' : '');
            $sheet->setCellValue("O{$row}", $record->saksis->pluck('nama')->join(', '));
            $row++;
        }

        $maxRow = $dataStartRow + $templateCapacity - 1 + $extraRows;
        for ($clearRow = $row; $clearRow <= $maxRow; $clearRow++) {
            foreach (range('A', 'O') as $column) {
                $sheet->setCellValue("{$column}{$clearRow}", '');
            }
        }
    }

    /**
     * @param  Collection<int, Kasus>  $records
     */
    private static function fillRecapTable(Worksheet $sheet, Collection $records): void
    {
        $totalRow = self::findRowByValue($sheet, 'B', 'JUMLAH TOTAL') ?? 31;
        $dataStartRow = $totalRow - 5;
        $templateCapacity = 5;

        $groups = $records
            ->groupBy(fn (Kasus $record): string => $record->perkara?->nama ?? 'Lainnya')
            ->map(function (Collection $items, string $jenis): array {
                $first = $items->first();

                return [
                    'jenis' => $jenis,
                    'pasal' => $first?->tindak_pidana_pasal ?: '-',
                    'jumlah_korban' => $items->sum(function (Kasus $k): int {
                        $count = $k->korbans->count();

                        return $count > 0 ? $count : ($k->nama_korban ? 1 : 0);
                    }),
                    'jumlah_tersangka' => $items->sum(function (Kasus $k): int {
                        $count = $k->tersangkas->count();

                        return $count > 0 ? $count : ($k->nama_pelaku ? 1 : 0);
                    }),
                    'jumlah_saksi' => $items->sum(fn (Kasus $k): int => $k->saksis->count()),
                    'lidik' => $items->where('dokumen_status.value', 'lidik')->count(),
                    'henti_lidik' => $items->filter(fn (Kasus $k): bool => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'henti'))->count(),
                    'sidik' => $items->where('dokumen_status.value', 'sidik')->count(),
                    'sp3' => $items->filter(fn (Kasus $k): bool => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'sp3'))->count(),
                    'p21' => $items->filter(fn (Kasus $k): bool => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'p21'))->count(),
                    'dicabut' => $items->filter(fn (Kasus $k): bool => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'cabut'))->count(),
                    'limpah' => $items->filter(fn (Kasus $k): bool => str_contains(strtolower((string) ($k->penyelesaian?->nama ?? '')), 'limpah'))->count(),
                ];
            })
            ->values();

        $extraRows = max(0, $groups->count() - $templateCapacity);
        if ($extraRows > 0) {
            $sheet->insertNewRowBefore($totalRow, $extraRows);
            for ($i = 0; $i < $extraRows; $i++) {
                $sourceRow = $totalRow - 1;
                $targetRow = $sourceRow + $i + 1;
                $sheet->duplicateStyle($sheet->getStyle("A{$sourceRow}:O{$sourceRow}"), "A{$targetRow}:O{$targetRow}");
            }
            $totalRow += $extraRows;
        }

        $row = $dataStartRow;
        foreach ($groups as $index => $group) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $group['jenis']);
            $sheet->setCellValue("C{$row}", $group['pasal']);
            $sheet->setCellValue("D{$row}", $group['jumlah_korban']);
            $sheet->setCellValue("E{$row}", $group['jumlah_tersangka']);
            $sheet->setCellValue("F{$row}", $group['jumlah_saksi']);
            $sheet->setCellValue("G{$row}", $group['lidik']);
            $sheet->setCellValue("H{$row}", $group['henti_lidik']);
            $sheet->setCellValue("I{$row}", $group['sidik']);
            $sheet->setCellValue("J{$row}", $group['sp3']);
            $sheet->setCellValue("K{$row}", $group['p21']);
            $sheet->setCellValue("L{$row}", $group['dicabut']);
            $sheet->setCellValue("M{$row}", $group['limpah']);
            $sheet->setCellValue("N{$row}", $group['lidik'] + $group['henti_lidik'] + $group['sidik'] + $group['sp3'] + $group['p21'] + $group['dicabut'] + $group['limpah']);
            $sheet->setCellValue("O{$row}", '');
            $row++;
        }

        $maxRow = $dataStartRow + $templateCapacity - 1 + $extraRows;
        for ($clearRow = $row; $clearRow <= $maxRow; $clearRow++) {
            foreach (range('A', 'O') as $column) {
                $sheet->setCellValue("{$column}{$clearRow}", '');
            }
        }

        $sheet->setCellValue("D{$totalRow}", $groups->sum('jumlah_korban'));
        $sheet->setCellValue("E{$totalRow}", $groups->sum('jumlah_tersangka'));
        $sheet->setCellValue("F{$totalRow}", $groups->sum('jumlah_saksi'));
        $sheet->setCellValue("G{$totalRow}", $groups->sum('lidik'));
        $sheet->setCellValue("H{$totalRow}", $groups->sum('henti_lidik'));
        $sheet->setCellValue("I{$totalRow}", $groups->sum('sidik'));
        $sheet->setCellValue("J{$totalRow}", $groups->sum('sp3'));
        $sheet->setCellValue("K{$totalRow}", $groups->sum('p21'));
        $sheet->setCellValue("L{$totalRow}", $groups->sum('dicabut'));
        $sheet->setCellValue("M{$totalRow}", $groups->sum('limpah'));
        $sheet->setCellValue("N{$totalRow}", $groups->sum(fn (array $group): int => $group['lidik'] + $group['henti_lidik'] + $group['sidik'] + $group['sp3'] + $group['p21'] + $group['dicabut'] + $group['limpah']));
        $sheet->setCellValue("O{$totalRow}", '');
    }

    /**
     * @param  Collection<int, Kasus>  $records
     */
    private static function applyTemplateOverrides(Worksheet $sheet, Collection $records, ?int $satkerId, ?int $userId): void
    {
        $kop = ExportDocumentTemplate::kopSuratLines($userId, $satkerId);
        $titles = ExportDocumentTemplate::automaticTitles($records, $userId, $satkerId);

        $sheet->setCellValue('A1', $kop[0] ?? '');
        $sheet->setCellValue('A2', $kop[1] ?? '');
        $sheet->setCellValue('A3', $kop[2] ?? '');

        $sheet->setCellValue('A5', $titles['main']);
        $sheet->setCellValue('A22', $titles['recap']);

        $signature = ExportDocumentTemplate::signatureBlock($userId, $satkerId);
        $sheet->setCellValue('I33', $signature['line1']);
        $sheet->setCellValue('I34', $signature['line2']);
        $sheet->setCellValue('G38', $signature['name']);
        $sheet->setCellValue('G39', $signature['rank']);
    }

    private static function findRowByValue(Worksheet $sheet, string $column, string $needle): ?int
    {
        $highestRow = $sheet->getHighestDataRow();
        $columnIndex = Coordinate::columnIndexFromString($column);

        for ($row = 1; $row <= $highestRow; $row++) {
            $value = strtoupper(trim((string) $sheet->getCellByColumnAndRow($columnIndex, $row)->getValue()));
            if ($value === strtoupper($needle)) {
                return $row;
            }
        }

        return null;
    }
}
