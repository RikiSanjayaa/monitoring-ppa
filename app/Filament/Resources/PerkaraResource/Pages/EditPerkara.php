<?php

namespace App\Filament\Resources\PerkaraResource\Pages;

use App\Filament\Resources\PerkaraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerkara extends EditRecord
{
    protected static string $resource = PerkaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
