<?php

namespace App\Filament\Resources\PenyelesaianResource\Pages;

use App\Filament\Resources\PenyelesaianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenyelesaian extends EditRecord
{
    protected static string $resource = PenyelesaianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
