<?php

namespace App\Filament\Resources\KasusResource\Pages;

use App\Filament\Resources\KasusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKasus extends EditRecord
{
    protected static string $resource = KasusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        if ($user?->isAdmin()) {
            $data['satker_id'] = $user->satker_id;
        }

        return $data;
    }
}
