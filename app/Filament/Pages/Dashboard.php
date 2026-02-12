<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\KasusPerJenisChartWidget;
use App\Filament\Widgets\KasusTrendChartWidget;
use App\Filament\Widgets\LatestKasusWidget;
use App\Filament\Widgets\SatkerPerformanceChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SummaryTableWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            KasusTrendChartWidget::class,
            KasusPerJenisChartWidget::class,
            SatkerPerformanceChartWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SummaryTableWidget::class,
            LatestKasusWidget::class,
        ];
    }
}
