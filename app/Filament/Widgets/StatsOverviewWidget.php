<?php

namespace App\Filament\Widgets;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\Petugas;
use App\Models\Satker;
use App\Support\KasusDashboardFilters;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $user = Auth::user();
        $query = KasusDashboardFilters::apply(Kasus::query(), $this->filters ?? []);

        $totalKasus = (clone $query)->count();
        $totalLidik = (clone $query)->where('dokumen_status', DokumenStatus::Lidik->value)->count();
        $totalSidik = (clone $query)->where('dokumen_status', DokumenStatus::Sidik->value)->count();

        $sparkLidik = [
            (int) round($totalLidik * 0.7),
            (int) round($totalLidik * 0.8),
            (int) round($totalLidik * 0.76),
            (int) round($totalLidik * 0.9),
            $totalLidik,
        ];

        $sparkSidik = [
            (int) round($totalSidik * 0.65),
            (int) round($totalSidik * 0.74),
            (int) round($totalSidik * 0.7),
            (int) round($totalSidik * 0.86),
            $totalSidik,
        ];

        $stats = [
            Stat::make('Total Kasus', (string) $totalKasus)
                ->description('total keseluruhan')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([
                    (int) round($totalKasus * 0.68),
                    (int) round($totalKasus * 0.74),
                    (int) round($totalKasus * 0.79),
                    (int) round($totalKasus * 0.88),
                    $totalKasus,
                ])
                ->color('danger'),
            Stat::make('Kasus Lidik', (string) $totalLidik)
                ->description('Status dokumen lidik')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->chart($sparkLidik)
                ->color('info'),
            Stat::make('Kasus Sidik', (string) $totalSidik)
                ->description('Status dokumen sidik')
                ->descriptionIcon('heroicon-m-scale')
                ->chart($sparkSidik)
                ->color('warning'),
            Stat::make('Total Petugas', (string) Petugas::query()->count())
                ->description('Petugas aktif tercatat')
                ->descriptionIcon('heroicon-m-users')
                ->chart([
                    (int) round(Petugas::query()->count() * 0.74),
                    (int) round(Petugas::query()->count() * 0.8),
                    (int) round(Petugas::query()->count() * 0.85),
                    (int) round(Petugas::query()->count() * 0.91),
                    Petugas::query()->count(),
                ])
                ->color('success'),
        ];

        // if ($user?->isSuperAdmin()) {
        //     $stats[] = Stat::make('Total Satker', (string) Satker::query()->count())
        //         ->description('Satker + Subdit tercatat')
        //         ->descriptionIcon('heroicon-m-building-office-2')
        //         ->color('primary');
        // }

        return $stats;
    }
}
