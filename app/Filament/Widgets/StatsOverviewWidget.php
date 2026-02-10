<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Models\Petugas;
use App\Models\Satker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        return [
            Stat::make('Total Kasus', (string) Kasus::query()->count())
                ->description('Data kasus sesuai hak akses')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('danger'),
            Stat::make(
                'Total Satker',
                (string) ($user?->isAdmin() ? 1 : Satker::query()->count())
            )
                ->description('Jumlah unit kerja')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),
            Stat::make('Total Petugas', (string) Petugas::query()->count())
                ->description('Petugas aktif tercatat')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
