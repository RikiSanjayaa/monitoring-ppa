<?php

namespace App\Providers;

use App\Filament\Widgets\KasusPerJenisChartWidget;
use App\Filament\Widgets\KasusTrendChartWidget;
use App\Filament\Widgets\LatestKasusWidget;
use App\Filament\Widgets\SatkerPerformanceChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SummaryTableWidget;
use App\Models\Kasus;
use App\Observers\KasusObserver;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        Kasus::observe(KasusObserver::class);

        Event::listen(Login::class, function (Login $event): void {
            AuditLogger::log(
                module: 'auth',
                action: 'login',
                summary: sprintf('User %s login.', $event->user->name),
                satkerId: $event->user->satker_id,
                actor: $event->user,
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if (! $event->user) {
                return;
            }

            AuditLogger::log(
                module: 'auth',
                action: 'logout',
                summary: sprintf('User %s logout.', $event->user->name),
                satkerId: $event->user->satker_id,
                actor: $event->user,
            );
        });

        Livewire::component('app.filament.widgets.stats-overview-widget', StatsOverviewWidget::class);
        Livewire::component('app.filament.widgets.kasus-trend-chart-widget', KasusTrendChartWidget::class);
        Livewire::component('app.filament.widgets.kasus-per-jenis-chart-widget', KasusPerJenisChartWidget::class);
        Livewire::component('app.filament.widgets.satker-performance-chart-widget', SatkerPerformanceChartWidget::class);
        Livewire::component('app.filament.widgets.summary-table-widget', SummaryTableWidget::class);
        Livewire::component('app.filament.widgets.latest-kasus-widget', LatestKasusWidget::class);
    }
}
