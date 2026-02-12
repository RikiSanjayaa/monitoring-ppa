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

        $firstKorban = $this->firstIdentity($data, 'korbans');
        $firstTersangka = $this->firstIdentity($data, 'tersangkas');

        if ($user?->isAdmin()) {
            $data['satker_id'] = $user->satker_id;
        }

        $data['nama_korban'] = $firstKorban['nama'] ?? ($data['nama_korban'] ?? '-');
        $data['tempat_lahir_korban'] = $firstKorban['tempat_lahir'] ?? ($data['tempat_lahir_korban'] ?? null);
        $data['tanggal_lahir_korban'] = $firstKorban['tanggal_lahir'] ?? ($data['tanggal_lahir_korban'] ?? null);
        $data['alamat_korban'] = $firstKorban['alamat'] ?? ($data['alamat_korban'] ?? null);
        $data['hp_korban'] = $firstKorban['hp'] ?? ($data['hp_korban'] ?? null);

        $data['nama_pelaku'] = $firstTersangka['nama'] ?? ($data['nama_pelaku'] ?? null);
        $data['tempat_lahir_pelaku'] = $firstTersangka['tempat_lahir'] ?? ($data['tempat_lahir_pelaku'] ?? null);
        $data['tanggal_lahir_pelaku'] = $firstTersangka['tanggal_lahir'] ?? ($data['tanggal_lahir_pelaku'] ?? null);
        $data['alamat_pelaku'] = $firstTersangka['alamat'] ?? ($data['alamat_pelaku'] ?? null);
        $data['hp_pelaku'] = $firstTersangka['hp'] ?? ($data['hp_pelaku'] ?? null);

        $data['created_by'] = $user?->id;

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
