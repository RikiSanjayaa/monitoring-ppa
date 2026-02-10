<?php

namespace App\Filament\Resources\SatkerResource\Pages;

use App\Filament\Resources\SatkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSatkers extends ListRecords
{
    protected static string $resource = SatkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
