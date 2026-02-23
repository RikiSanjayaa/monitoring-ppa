<?php

namespace App\Filament\Resources\KasusResource\Pages;

use App\Filament\Resources\KasusResource;
use App\Support\ExportDocumentTemplate;
use App\Support\KasusRecapSummary;
use App\Support\KasusTemplateSpreadsheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ListKasuses extends ListRecords
{
    protected static string $resource = KasusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-plus'),
            Actions\Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->button()
                ->action(function () {
                    $records = (clone $this->getFilteredTableQuery())
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
                    $recapData = KasusRecapSummary::fromCollection($records);

                    $satkerId = $this->resolveExportSatkerId($records);
                    $userId = Auth::id();
                    $periodDate = $this->resolveExportPeriodDate();
                    $titles = ExportDocumentTemplate::automaticTitles($records, $userId, $satkerId, $periodDate);

                    $pdf = Pdf::loadView('exports.kasus-report', [
                        'records' => $records,
                        'printedAt' => now()->format('d-m-Y H:i'),
                        'kopSuratLines' => ExportDocumentTemplate::kopSuratLines($userId, $satkerId),
                        'mainTitle' => $titles['main'],
                        'recapTitle' => $titles['recap'],
                        'signatureBlock' => ExportDocumentTemplate::signatureBlock($userId, $satkerId),
                        'recapData' => $recapData,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        static fn () => print ($pdf->output()),
                        'laporan-kasus-'.now()->format('Ymd_His').'.pdf',
                    );
                }),
            Actions\Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->button()
                ->action(function () {
                    $records = (clone $this->getFilteredTableQuery())
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

                    $satkerId = $this->resolveExportSatkerId($records);
                    $userId = Auth::id();
                    $periodDate = $this->resolveExportPeriodDate();
                    $titles = ExportDocumentTemplate::automaticTitles($records, $userId, $satkerId, $periodDate);

                    $spreadsheet = KasusTemplateSpreadsheet::build($records, $satkerId, $userId, $titles);
                    $fileName = 'kasus-'.now()->format('Ymd_His').'.xlsx';

                    return response()->streamDownload(function () use ($spreadsheet): void {
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, $fileName);
                }),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Kasus>  $records
     */
    private function resolveExportSatkerId($records): ?int
    {
        $user = Auth::user();

        if (data_get($user, 'role') === 'admin' && data_get($user, 'satker_id')) {
            return (int) data_get($user, 'satker_id');
        }

        $satkerIds = $records->pluck('satker_id')->filter()->unique()->values();

        if ($satkerIds->count() === 1) {
            return (int) $satkerIds->first();
        }

        return null;
    }

    private function resolveExportPeriodDate(): ?Carbon
    {
        $filterState = (array) data_get($this->tableFilters, 'periode_tanggal', []);
        $preset = data_get($filterState, 'preset');

        if ($preset === 'bulan_ini') {
            return now()->startOfMonth();
        }

        if ($preset === 'bulan_lalu') {
            return now()->subMonthNoOverflow()->startOfMonth();
        }

        $fromDate = data_get($filterState, 'from_date');

        if (is_string($fromDate) && $fromDate !== '') {
            return Carbon::parse($fromDate)->startOfDay();
        }

        $toDate = data_get($filterState, 'to_date');

        if (is_string($toDate) && $toDate !== '') {
            return Carbon::parse($toDate)->startOfDay();
        }

        return null;
    }
}
