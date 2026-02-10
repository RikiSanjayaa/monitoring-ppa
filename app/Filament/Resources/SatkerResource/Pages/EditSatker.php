<?php

namespace App\Filament\Resources\SatkerResource\Pages;

use App\Filament\Resources\SatkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSatker extends EditRecord
{
    protected static string $resource = SatkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
