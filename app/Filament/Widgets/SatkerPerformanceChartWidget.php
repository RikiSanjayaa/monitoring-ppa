<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Support\KasusDashboardFilters;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class SatkerPerformanceChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Satker Berdasarkan jumlah Kasus';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    protected function getData(): array
    {
        $records = KasusDashboardFilters::apply(Kasus::query(), $this->filters ?? [])
            ->with('satker:id,nama')
            ->get()
            ->groupBy(fn(Kasus $kasus): string => $kasus->satker?->nama ?? '-')
            ->map(fn($items): int => $items->count())
            ->sortDesc()
            ->take(8);

        return [
            'datasets' => [
                [
                    'label' => '',
                    'data' => $records->values()->all(),
                    'backgroundColor' => '#6366f1',
                ],
            ],
            'labels' => $records->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
