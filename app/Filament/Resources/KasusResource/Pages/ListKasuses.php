<?php

namespace App\Filament\Resources\KasusResource\Pages;

use App\Exports\KasusTemplateExport;
use App\Filament\Resources\KasusResource;
use App\Imports\KasusImport;
use App\Models\Satker;
use App\Support\ExportDocumentTemplate;
use App\Support\KasusTemplateSpreadsheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
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
            Actions\ActionGroup::make([
                Actions\Action::make('downloadKasusTemplate')
                    ->label('Download Template Kasus')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => Excel::download(new KasusTemplateExport, 'template-import-kasus.xlsx')),
                Actions\Action::make('importKasus')
                    ->label('Import Kasus')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form($this->getImportForm())
                    ->action(function (array $data): void {
                        $satkerId = $this->resolveSatkerId($data);
                        $user = Auth::user();
                        $path = Storage::disk('local')->path($data['file']);

                        Excel::import(new KasusImport($satkerId, $user->id), $path);

                        Notification::make()
                            ->title('Import kasus selesai')
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(fn (): bool => Auth::user()?->isSuperAdmin() || Auth::user()?->isAdmin())
                ->button(),
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

                    $satkerId = $this->resolveExportSatkerId($records);
                    $userId = Auth::id();
                    $titles = ExportDocumentTemplate::automaticTitles($records, $userId, $satkerId);

                    $pdf = Pdf::loadView('exports.kasus-report', [
                        'records' => $records,
                        'printedAt' => now()->format('d-m-Y H:i'),
                        'kopSuratLines' => ExportDocumentTemplate::kopSuratLines($userId, $satkerId),
                        'mainTitle' => $titles['main'],
                        'recapTitle' => $titles['recap'],
                        'signatureBlock' => ExportDocumentTemplate::signatureBlock($userId, $satkerId),
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
                        ])
                        ->get();

                    $satkerId = $this->resolveExportSatkerId($records);
                    $userId = Auth::id();

                    $spreadsheet = KasusTemplateSpreadsheet::build($records, $satkerId, $userId);
                    $fileName = 'kasus-'.now()->format('Ymd_His').'.xlsx';

                    return response()->streamDownload(function () use ($spreadsheet): void {
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, $fileName);
                }),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function getImportForm(): array
    {
        return [
            Forms\Components\Select::make('satker_id')
                ->label('Satker')
                ->options(fn (): array => Satker::query()->orderBy('nama')->pluck('nama', 'id')->all())
                ->searchable()
                ->visible(fn (): bool => Auth::user()?->isSuperAdmin() ?? false)
                ->required(fn (): bool => Auth::user()?->isSuperAdmin() ?? false),
            Forms\Components\FileUpload::make('file')
                ->label('File Import')
                ->acceptedFileTypes([
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv',
                ])
                ->directory('imports')
                ->disk('local')
                ->preserveFilenames()
                ->required(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSatkerId(array $data): int
    {
        $user = Auth::user();

        if ($user?->isAdmin() && $user->satker_id) {
            return $user->satker_id;
        }

        $satkerId = (int) ($data['satker_id'] ?? 0);

        if (! $satkerId) {
            throw ValidationException::withMessages([
                'satker_id' => 'Satker wajib dipilih.',
            ]);
        }

        return $satkerId;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Kasus>  $records
     */
    private function resolveExportSatkerId($records): ?int
    {
        $user = Auth::user();

        if ($user?->isAdmin() && $user->satker_id) {
            return (int) $user->satker_id;
        }

        $satkerIds = $records->pluck('satker_id')->filter()->unique()->values();

        if ($satkerIds->count() === 1) {
            return (int) $satkerIds->first();
        }

        return null;
    }
}
