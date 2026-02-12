<?php

namespace App\Providers;

use App\Filament\Widgets\KasusPerJenisChartWidget;
use App\Filament\Widgets\KasusTrendChartWidget;
use App\Filament\Widgets\LatestKasusWidget;
use App\Filament\Widgets\SatkerPerformanceChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SummaryTableWidget;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('app.filament.widgets.stats-overview-widget', StatsOverviewWidget::class);
        Livewire::component('app.filament.widgets.kasus-trend-chart-widget', KasusTrendChartWidget::class);
        Livewire::component('app.filament.widgets.kasus-per-jenis-chart-widget', KasusPerJenisChartWidget::class);
        Livewire::component('app.filament.widgets.satker-performance-chart-widget', SatkerPerformanceChartWidget::class);
        Livewire::component('app.filament.widgets.summary-table-widget', SummaryTableWidget::class);
        Livewire::component('app.filament.widgets.latest-kasus-widget', LatestKasusWidget::class);
    }
}
