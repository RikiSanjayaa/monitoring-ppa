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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        $firstKorban = $this->firstIdentity($data, 'korbans');
        $firstTersangka = $this->firstIdentity($data, 'tersangkas');

        if ($user?->isAdmin()) {
            $data['satker_id'] = $user->satker_id;
        }

        $data['nama_korban'] = $firstKorban['nama'] ?? ($data['nama_korban'] ?? $this->record->nama_korban);
        $data['tempat_lahir_korban'] = $firstKorban['tempat_lahir'] ?? ($data['tempat_lahir_korban'] ?? $this->record->tempat_lahir_korban);
        $data['tanggal_lahir_korban'] = $firstKorban['tanggal_lahir'] ?? ($data['tanggal_lahir_korban'] ?? $this->record->tanggal_lahir_korban);
        $data['alamat_korban'] = $firstKorban['alamat'] ?? ($data['alamat_korban'] ?? $this->record->alamat_korban);
        $data['hp_korban'] = $firstKorban['hp'] ?? ($data['hp_korban'] ?? $this->record->hp_korban);

        $data['nama_pelaku'] = $firstTersangka['nama'] ?? ($data['nama_pelaku'] ?? $this->record->nama_pelaku);
        $data['tempat_lahir_pelaku'] = $firstTersangka['tempat_lahir'] ?? ($data['tempat_lahir_pelaku'] ?? $this->record->tempat_lahir_pelaku);
        $data['tanggal_lahir_pelaku'] = $firstTersangka['tanggal_lahir'] ?? ($data['tanggal_lahir_pelaku'] ?? $this->record->tanggal_lahir_pelaku);
        $data['alamat_pelaku'] = $firstTersangka['alamat'] ?? ($data['alamat_pelaku'] ?? $this->record->alamat_pelaku);
        $data['hp_pelaku'] = $firstTersangka['hp'] ?? ($data['hp_pelaku'] ?? $this->record->hp_pelaku);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function firstIdentity(array $data, string $key): array
    {
        $items = $data[$key] ?? [];

        if (! is_array($items) || $items === []) {
            return [];
        }

        $first = collect($items)->first();

        return is_array($first) ? $first : [];
    }
}
