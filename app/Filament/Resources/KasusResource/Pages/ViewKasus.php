<?php

namespace App\Filament\Resources\KasusResource\Pages;

use App\Filament\Resources\KasusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKasus extends ViewRecord
{
    protected static string $resource = KasusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
