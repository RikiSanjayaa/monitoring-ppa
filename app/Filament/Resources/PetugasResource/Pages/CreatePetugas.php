<?php

namespace App\Filament\Resources\PetugasResource\Pages;

use App\Filament\Resources\PetugasResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePetugas extends CreateRecord
{
    protected static string $resource = PetugasResource::class;

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

        return $data;
    }
}
