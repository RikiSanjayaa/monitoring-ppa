<?php

namespace App\Exports;

use App\Models\Kasus;
use App\Support\ExportDocumentTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KasusExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  Collection<int, Kasus>  $records
     */
    public function __construct(
        private readonly Collection $records,
    ) {}

    public static function fromQuery(Builder $query): self
    {
        $records = (clone $query)
            ->with([
                'satker:id,nama',
                'perkara:id,nama',
                'penyelesaian:id,nama',
                'petugas:id,nama',
                'korbans:id,kasus_id,nama,tempat_lahir,tanggal_lahir,alamat,hp',
                'tersangkas:id,kasus_id,nama,tempat_lahir,tanggal_lahir,alamat,hp',
                'saksis:id,kasus_id,nama,tempat_lahir,tanggal_lahir,hp',
                'latestRtl',
            ])
            ->get();

        return new self($records);
    }

    /**
     * @return Collection<int, Kasus>
     */
    public function collection(): Collection
    {
        return $this->records;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nomor LP',
            'Tanggal LP',
            'Satker',
            'Nama Korban',
            'Tempat Lahir Korban',
            'Tanggal Lahir Korban',
            'TTL',
            'Alamat',
            'HP',
            'Nama Tersangka',
            'Tempat Lahir Tersangka',
            'Tanggal Lahir Tersangka',
            'Alamat Tersangka',
            'HP Tersangka',
            'Daftar Saksi',
            'Jenis Kasus',
            'Tindak Pidana/Pasal',
            'Hubungan Tersangka dengan Korban',
            'Proses Pidana',
            'Kronologi Kejadian',
            'Dokumen/Giat',
            'Petugas',
            'Penyelesaian',
            'RTL Terbaru',
        ];
    }

    /**
     * @param  Kasus  $row
     * @return array<int, string>
     */
    public function map($row): array
    {
        $korbanUtama = $row->korbans->first();
        $tersangkaUtama = $row->tersangkas->first();
        $ttl = trim(implode(', ', array_filter([
            $korbanUtama?->tempat_lahir,
            $korbanUtama?->tanggal_lahir?->format('d-m-Y'),
        ])));

        $korbanList = $row->korbans->pluck('nama')->join(', ');
        $tersangkaList = $row->tersangkas->pluck('nama')->join(', ');

        return [
            $row->nomor_lp,
            $row->tanggal_lp?->format('d-m-Y') ?? '-',
            $row->satker?->nama ?? '-',
            $korbanList !== '' ? $korbanList : '-',
            (string) ($korbanUtama?->tempat_lahir ?? '-'),
            $korbanUtama?->tanggal_lahir?->format('d-m-Y') ?? '-',
            $ttl !== '' ? $ttl : '-',
            (string) ($korbanUtama?->alamat ?? '-'),
            (string) ($korbanUtama?->hp ?? '-'),
            (string) ($tersangkaList !== '' ? $tersangkaList : '-'),
            (string) ($tersangkaUtama?->tempat_lahir ?? '-'),
            $tersangkaUtama?->tanggal_lahir?->format('d-m-Y') ?? '-',
            (string) ($tersangkaUtama?->alamat ?? '-'),
            (string) ($tersangkaUtama?->hp ?? '-'),
            $row->saksis->map(function ($saksi): string {
                $tanggalLahir = $saksi->tanggal_lahir?->format('d-m-Y') ?? '-';

                return implode(' | ', array_filter([
                    $saksi->nama,
                    $saksi->tempat_lahir,
                    $tanggalLahir,
                    $saksi->hp,
                ]));
            })->join('; '),
            $row->perkara?->nama ?? '-',
            (string) ($row->tindak_pidana_pasal ?? '-'),
            (string) ($row->hubungan_pelaku_dengan_korban ?? '-'),
            (string) ($row->proses_pidana ?? '-'),
            (string) ($row->kronologi_kejadian ?? '-'),
            strtoupper($row->dokumen_status?->value ?? '-'),
            $row->petugas->pluck('nama')->join(', '),
            $row->penyelesaian?->nama ?? '-',
            $row->latestRtl?->keterangan ?? '-',
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));

                foreach (ExportDocumentTemplate::kopSuratLines() as $index => $line) {
                    $row = $index + 1;
                    $cell = 'A'.$row;

                    $sheet->setCellValue($cell, $line);
                    $sheet->mergeCells(sprintf('A%d:%s%d', $row, $lastColumn, $row));
                }

                $sheet->setCellValue('A6', 'LAPORAN MONITORING KASUS');
                $sheet->mergeCells(sprintf('A6:%s6', $lastColumn));

                $sheet->getStyle(sprintf('A1:%s4', $lastColumn))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle(sprintf('A1:%s1', $lastColumn))->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle(sprintf('A2:%s4', $lastColumn))->getFont()->setBold(true);
                $sheet->getStyle(sprintf('A6:%s6', $lastColumn))->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle(sprintf('A6:%s6', $lastColumn))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $clipStartColumnIndex = count($this->headings()) + 2;
                $clipParafColumn = Coordinate::stringFromColumnIndex($clipStartColumnIndex);
                $clipTtdColumn = Coordinate::stringFromColumnIndex($clipStartColumnIndex + 1);

                [$parafLabel, $ttdLabel] = ExportDocumentTemplate::clipLabels();

                $sheet->setCellValue($clipParafColumn.'8', 'PARAF');
                $sheet->setCellValue($clipTtdColumn.'8', 'TTD');

                for ($row = 9; $row <= 30; $row++) {
                    $sheet->setCellValue($clipParafColumn.$row, $parafLabel);
                    $sheet->setCellValue($clipTtdColumn.$row, $ttdLabel);
                }

                $sheet->getStyle(sprintf('%s8:%s30', $clipParafColumn, $clipTtdColumn))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle(sprintf('%s8:%s30', $clipParafColumn, $clipTtdColumn))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle(sprintf('%s8:%s30', $clipParafColumn, $clipTtdColumn))
                    ->getFont()
                    ->setSize(8);
            },
        ];
    }
}
