<?php

namespace App\Filament\Resources\PengaturanLaporanResource\Pages;

use App\Filament\Resources\PengaturanLaporanResource;
use App\Models\PengaturanLaporan;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPengaturanLaporan extends EditRecord
{
    protected static string $resource = PengaturanLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function resolveRecord(int|string $key): PengaturanLaporan
    {
        $record = parent::resolveRecord($key);

        abort_unless($record->user_id === Auth::id(), 403);

        return $record;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        $data['user_id'] = $user?->id;
        $data['satker_id'] = $user?->isAdmin() ? $user->satker_id : null;

        $data['judul_utama'] = 'AUTO_TITLE';
        $data['judul_rekap'] = 'AUTO_RECAP_TITLE';

        return $data;
    }
}
