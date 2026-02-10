<?php

namespace App\Filament\Resources\PerkaraResource\Pages;

use App\Filament\Resources\PerkaraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerkaras extends ListRecords
{
    protected static string $resource = PerkaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
