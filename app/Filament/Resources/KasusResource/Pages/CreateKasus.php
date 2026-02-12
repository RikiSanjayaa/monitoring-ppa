<?php

namespace App\Filament\Resources\KasusResource\Pages;

use App\Filament\Resources\KasusResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateKasus extends CreateRecord
{
    protected static string $resource = KasusResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user?->isAdmin()) {
            $data['satker_id'] = $user->satker_id;
        }

        $data['created_by'] = $user?->id;

        return $data;
    }
}
