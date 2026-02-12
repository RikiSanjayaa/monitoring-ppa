<?php

namespace App\Filament\Pages;

use App\Enums\DokumenStatus;
use App\Filament\Widgets\KasusPerJenisChartWidget;
use App\Filament\Widgets\KasusTrendChartWidget;
use App\Filament\Widgets\LatestKasusWidget;
use App\Filament\Widgets\SatkerPerformanceChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SummaryTableWidget;
use App\Models\Perkara;
use App\Models\Satker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

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

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('from_date')
                    ->label('Dari Tanggal LP'),
                DatePicker::make('to_date')
                    ->label('Sampai Tanggal LP'),
                Select::make('perkara_id')
                    ->label('Jenis Kasus')
                    ->options(fn (): array => Perkara::query()->where('is_active', true)->orderBy('nama')->pluck('nama', 'id')->all())
                    ->searchable(),
                Select::make('dokumen_status')
                    ->label('Dokumen/Giat')
                    ->options(DokumenStatus::options())
                    ->native(false),
                Select::make('satker_id')
                    ->label('Satker')
                    ->options(fn (): array => Satker::query()->orderBy('nama')->pluck('nama', 'id')->all())
                    ->searchable()
                    ->visible(fn (): bool => Auth::user()?->isSuperAdmin() || Auth::user()?->isAtasan()),
            ])
            ->columns(5);
    }
}
