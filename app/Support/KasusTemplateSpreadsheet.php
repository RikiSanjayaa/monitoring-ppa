<?php

namespace App\Support;

use App\Models\Kasus;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KasusTemplateSpreadsheet
{
    /**
     * @param  array{main: string, recap: string}|null  $titles
     */
    public static function build(
        Collection $records,
        ?int $satkerId = null,
        ?int $userId = null,
        ?array $titles = null,
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet;
        $recapData = KasusRecapSummary::fromCollection($records);
        $penyelesaianColumns = $recapData['penyelesaian_columns'];
        $recordsByJenis = $records->groupBy(fn (Kasus $record): string => $record->perkara?->nama ?? 'Lainnya');

        $usedSheetTitles = [];
        $sheetIndex = 0;

        if ($recordsByJenis->isEmpty()) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(self::nextSheetTitle('Detail', $usedSheetTitles));
            self::fillDetailSheet($sheet, collect(), 'Tidak Ada Data', $penyelesaianColumns, $satkerId, $userId, $titles);
            $sheetIndex++;
        } else {
            foreach ($recordsByJenis as $jenisKasus => $groupedRecords) {
                $sheet = $sheetIndex === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
                $sheet->setTitle(self::nextSheetTitle((string) $jenisKasus, $usedSheetTitles));
                self::fillDetailSheet($sheet, $groupedRecords, (string) $jenisKasus, $penyelesaianColumns, $satkerId, $userId, $titles);
                $sheetIndex++;
            }
        }

        $recapSheet = $spreadsheet->createSheet();
        $recapSheet->setTitle(self::nextSheetTitle('Rekap', $usedSheetTitles));
        self::fillRecapSheet($recapSheet, $records, $recapData, $satkerId, $userId, $titles);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private static function fillDetailSheet(
        Worksheet $sheet,
        Collection $records,
        string $jenisKasus,
        Collection $penyelesaianColumns,
        ?int $satkerId,
        ?int $userId,
        ?array $titles,
    ): void {
        self::fillKopAndTitle($sheet, $records, $satkerId, $userId, false, $titles);
        $sheet->setCellValue('A6', 'JENIS KASUS: '.strtoupper($jenisKasus));

        $headerRow = 8;
        $dataStartRow = 9;

        $staticHeaders = [
            1 => 'NO',
            2 => 'SATUAN',
            3 => 'LAPORAN POLISI/TGL',
            4 => 'KRONOLOGIS KEJADIAN',
            5 => 'TINDAK PIDANA/PASAL',
            6 => 'KORBAN',
            7 => 'TERSANGKA',
            8 => 'HUB. TERSANGKA DENGAN KORBAN',
            9 => 'DOKUMEN/GIAT (LIDIK)',
            10 => 'DOKUMEN/GIAT (SIDIK)',
        ];

        foreach ($staticHeaders as $columnIndex => $label) {
            self::setCell($sheet, $columnIndex, $headerRow, $label);
        }

        $currentColumn = 11;
        foreach ($penyelesaianColumns as $column) {
            self::setCell($sheet, $currentColumn, $headerRow, strtoupper((string) $column['label']));
            $currentColumn++;
        }

        self::setCell($sheet, $currentColumn, $headerRow, 'KET');

        $row = $dataStartRow;
        foreach ($records as $index => $record) {
            $korban = $record->korbans->pluck('nama')->join(', ');
            $tersangka = $record->tersangkas->pluck('nama')->join(', ');

            self::setCell($sheet, 1, $row, $index + 1);
            self::setCell($sheet, 2, $row, $record->satker?->nama ?? '-');
            self::setCell($sheet, 3, $row, trim(($record->nomor_lp ?? '-')."\n".($record->tanggal_lp?->format('d-m-Y') ?? '-')));
            self::setCell($sheet, 4, $row, $record->kronologi_kejadian ?: '-');
            self::setCell($sheet, 5, $row, $record->tindak_pidana_pasal ?: '-');
            self::setCell($sheet, 6, $row, $korban !== '' ? $korban : '-');
            self::setCell($sheet, 7, $row, $tersangka !== '' ? $tersangka : '-');
            self::setCell($sheet, 8, $row, $record->hubungan_pelaku_dengan_korban ?: '-');
            self::setCell($sheet, 9, $row, $record->dokumen_status?->value === 'lidik' ? 1 : '');
            self::setCell($sheet, 10, $row, $record->dokumen_status?->value === 'sidik' ? 1 : '');

            $penyelesaianColumnIndex = 11;
            foreach ($penyelesaianColumns as $column) {
                self::setCell(
                    $sheet,
                    $penyelesaianColumnIndex,
                    $row,
                    (int) ($record->penyelesaian_id ?? 0) === (int) $column['id'] ? 1 : '',
                );
                $penyelesaianColumnIndex++;
            }

            $ket = '-';

            if ($record->latestRtl) {
                $ket = sprintf(
                    '%s - %s',
                    $record->latestRtl->tanggal?->format('d-m-Y') ?? '-',
                    $record->latestRtl->keterangan ?? '-',
                );
            }

            self::setCell($sheet, $penyelesaianColumnIndex, $row, $ket);
            $row++;
        }

        $lastColumnIndex = $currentColumn;
        self::applyBasicTableStyle($sheet, $headerRow, max($dataStartRow, $row - 1), $lastColumnIndex, true);
        self::fillSignature($sheet, $satkerId, $userId, $row + 2, $lastColumnIndex);
    }

    private static function fillRecapSheet(
        Worksheet $sheet,
        Collection $records,
        array $recapData,
        ?int $satkerId,
        ?int $userId,
        ?array $titles,
    ): void {
        self::fillKopAndTitle($sheet, $records, $satkerId, $userId, true, $titles);

        $headerRow = 8;
        $dataStartRow = 9;
        $penyelesaianColumns = $recapData['penyelesaian_columns'];

        $headers = [
            'NO',
            'JENIS TP',
            'TP/PASAL',
            'KORBAN',
            'TERSANGKA',
            'SAKSI',
            'DOKUMEN/GIAT (LIDIK)',
            'DOKUMEN/GIAT (SIDIK)',
        ];

        foreach ($penyelesaianColumns as $column) {
            $headers[] = strtoupper((string) $column['label']);
        }

        $headers[] = 'JML';
        $headers[] = 'KET';

        foreach ($headers as $index => $header) {
            self::setCell($sheet, $index + 1, $headerRow, $header);
        }

        $row = $dataStartRow;
        foreach ($recapData['rows'] as $index => $group) {
            self::setCell($sheet, 1, $row, $index + 1);
            self::setCell($sheet, 2, $row, $group['jenis']);
            self::setCell($sheet, 3, $row, $group['pasal']);
            self::setCell($sheet, 4, $row, $group['jumlah_korban']);
            self::setCell($sheet, 5, $row, $group['jumlah_tersangka']);
            self::setCell($sheet, 6, $row, $group['jumlah_saksi']);
            self::setCell($sheet, 7, $row, $group['lidik']);
            self::setCell($sheet, 8, $row, $group['sidik']);

            $penyelesaianColumnIndex = 9;
            foreach ($penyelesaianColumns as $column) {
                self::setCell(
                    $sheet,
                    $penyelesaianColumnIndex,
                    $row,
                    (int) ($group['penyelesaian_counts'][$column['key']] ?? 0),
                );
                $penyelesaianColumnIndex++;
            }

            self::setCell($sheet, $penyelesaianColumnIndex, $row, $group['jumlah']);
            self::setCell($sheet, $penyelesaianColumnIndex + 1, $row, '');
            $row++;
        }

        $totalRow = $row;
        self::setCell($sheet, 2, $totalRow, 'JUMLAH TOTAL');

        $totals = $recapData['totals'];
        self::setCell($sheet, 4, $totalRow, $totals['jumlah_korban']);
        self::setCell($sheet, 5, $totalRow, $totals['jumlah_tersangka']);
        self::setCell($sheet, 6, $totalRow, $totals['jumlah_saksi']);
        self::setCell($sheet, 7, $totalRow, $totals['lidik']);
        self::setCell($sheet, 8, $totalRow, $totals['sidik']);

        $penyelesaianColumnIndex = 9;
        foreach ($penyelesaianColumns as $column) {
            self::setCell(
                $sheet,
                $penyelesaianColumnIndex,
                $totalRow,
                (int) ($totals['penyelesaian_counts'][$column['key']] ?? 0),
            );
            $penyelesaianColumnIndex++;
        }

        self::setCell($sheet, $penyelesaianColumnIndex, $totalRow, $totals['jumlah']);
        self::setCell($sheet, $penyelesaianColumnIndex + 1, $totalRow, '');

        $lastColumnIndex = $penyelesaianColumnIndex + 1;
        self::applyBasicTableStyle($sheet, $headerRow, $totalRow, $lastColumnIndex, false);
        self::fillSignature($sheet, $satkerId, $userId, $totalRow + 2, $lastColumnIndex);
    }

    private static function fillKopAndTitle(
        Worksheet $sheet,
        Collection $records,
        ?int $satkerId,
        ?int $userId,
        bool $useRecapTitle,
        ?array $titles = null,
    ): void {
        $kop = ExportDocumentTemplate::kopSuratLines($userId, $satkerId);
        $titles ??= ExportDocumentTemplate::automaticTitles($records, $userId, $satkerId);

        $sheet->setCellValue('A1', $kop[0] ?? '');
        $sheet->setCellValue('A2', $kop[1] ?? '');
        $sheet->setCellValue('A3', $kop[2] ?? '');
        $sheet->setCellValue('A5', $useRecapTitle ? $titles['recap'] : $titles['main']);
    }

    private static function fillSignature(
        Worksheet $sheet,
        ?int $satkerId,
        ?int $userId,
        int $startRow,
        int $lastColumnIndex,
    ): void {
        $signature = ExportDocumentTemplate::signatureBlock($userId, $satkerId);
        $signatureStartColumn = max(1, $lastColumnIndex - 4);

        self::setCell($sheet, $signatureStartColumn, $startRow, $signature['line1']);
        self::setCell($sheet, $signatureStartColumn, $startRow + 1, $signature['line2']);
        self::setCell($sheet, $signatureStartColumn, $startRow + 4, $signature['name']);
        self::setCell($sheet, $signatureStartColumn, $startRow + 5, $signature['rank']);
    }

    private static function applyBasicTableStyle(
        Worksheet $sheet,
        int $headerRow,
        int $lastDataRow,
        int $lastColumnIndex,
        bool $isDetailSheet,
    ): void {
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
        $tableRange = "A{$headerRow}:{$lastColumn}{$lastDataRow}";
        $headerRange = "A{$headerRow}:{$lastColumn}{$headerRow}";

        $sheet->freezePane('A'.($headerRow + 1));

        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF1F2937'],
                ],
            ],
        ]);

        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFE5E7EB');
        $sheet->getStyle($headerRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle($tableRange)->getAlignment()->setWrapText(true);
        $sheet->getStyle($tableRange)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle("A{$headerRow}:A{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("I{$headerRow}:{$lastColumn}{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $widths = $isDetailSheet
            ? self::detailColumnWidths($lastColumnIndex)
            : self::recapColumnWidths($lastColumnIndex);

        foreach ($widths as $columnIndex => $width) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(false);
            $sheet->getColumnDimension($columnLetter)->setWidth($width);
        }

        $sheet->getRowDimension($headerRow)->setRowHeight(24);
    }

    private static function detailColumnWidths(int $lastColumnIndex): array
    {
        $widths = [
            1 => 6,
            2 => 20,
            3 => 24,
            4 => 38,
            5 => 30,
            6 => 24,
            7 => 24,
            8 => 22,
            9 => 8,
            10 => 8,
        ];

        for ($column = 11; $column < $lastColumnIndex; $column++) {
            $widths[$column] = 10;
        }

        $widths[$lastColumnIndex] = 42;

        return $widths;
    }

    private static function recapColumnWidths(int $lastColumnIndex): array
    {
        $widths = [
            1 => 6,
            2 => 32,
            3 => 28,
            4 => 10,
            5 => 12,
            6 => 10,
            7 => 8,
            8 => 8,
        ];

        for ($column = 9; $column < $lastColumnIndex; $column++) {
            $widths[$column] = 10;
        }

        $widths[$lastColumnIndex] = 22;

        return $widths;
    }

    private static function setCell(Worksheet $sheet, int $columnIndex, int $row, mixed $value): void
    {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex).$row, $value);
    }

    private static function nextSheetTitle(string $baseTitle, array &$usedTitles): string
    {
        $normalized = self::normalizeSheetTitle($baseTitle);
        $candidate = $normalized;
        $counter = 2;

        while (in_array($candidate, $usedTitles, true)) {
            $suffix = ' '.$counter;
            $maxBaseLength = 31 - strlen($suffix);
            $candidate = substr($normalized, 0, $maxBaseLength).$suffix;
            $counter++;
        }

        $usedTitles[] = $candidate;

        return $candidate;
    }

    private static function normalizeSheetTitle(string $title): string
    {
        $value = preg_replace('#[\\/*?:\[\]]+#', '', $title);
        $value = trim((string) $value);

        if ($value === '') {
            return 'Sheet';
        }

        return substr($value, 0, 31);
    }
}
