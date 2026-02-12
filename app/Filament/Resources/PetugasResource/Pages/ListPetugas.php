<?php

namespace App\Filament\Resources\PetugasResource\Pages;

use App\Exports\PetugasExport;
use App\Exports\PetugasTemplateExport;
use App\Filament\Resources\PetugasResource;
use App\Imports\PetugasImport;
use App\Models\Satker;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ListPetugas extends ListRecords
{
    protected static string $resource = PetugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-plus'),
            Actions\ActionGroup::make([
                Actions\Action::make('downloadPetugasTemplate')
                    ->label('Download Template Petugas')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => Excel::download(new PetugasTemplateExport, 'template-import-petugas.xlsx')),
                Actions\Action::make('importPetugas')
                    ->label('Import Petugas')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form($this->getImportForm())
                    ->action(function (array $data): void {
                        $satkerId = $this->resolveSatkerId($data);
                        $path = Storage::disk('local')->path($data['file']);

                        Excel::import(new PetugasImport($satkerId), $path);

                        Notification::make()
                            ->title('Import petugas selesai')
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(fn (): bool => Auth::user()?->isSuperAdmin() || Auth::user()?->isAdmin())
                ->button(),
            Actions\Action::make('exportPetugas')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->button()
                ->action(fn () => Excel::download(PetugasExport::fromQuery(clone $this->getFilteredTableQuery()), 'petugas-'.now()->format('Ymd_His').'.xlsx')),
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
            return (int) $user->satker_id;
        }

        $satkerId = (int) ($data['satker_id'] ?? 0);

        if (! $satkerId) {
            throw ValidationException::withMessages([
                'satker_id' => 'Satker wajib dipilih.',
            ]);
        }

        return $satkerId;
    }
}
