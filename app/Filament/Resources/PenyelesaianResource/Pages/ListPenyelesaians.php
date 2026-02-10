<?php

namespace App\Filament\Resources\PenyelesaianResource\Pages;

use App\Filament\Resources\PenyelesaianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenyelesaians extends ListRecords
{
    protected static string $resource = PenyelesaianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
