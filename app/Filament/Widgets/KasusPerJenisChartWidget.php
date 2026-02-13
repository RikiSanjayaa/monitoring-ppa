<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Support\KasusDashboardFilters;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class KasusPerJenisChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Distribusi Jenis Kasus';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $grouped = KasusDashboardFilters::apply(Kasus::query(), $this->filters ?? [])
            ->with('perkara:id,nama')
            ->get()
            ->groupBy(fn (Kasus $kasus): string => $kasus->perkara?->nama ?? 'Lainnya')
            ->map(fn ($items): int => $items->count())
            ->sortDesc()
            ->take(8);

        return [
            'datasets' => [
                [
                    'label' => '',
                    'data' => $grouped->values()->all(),
                    'backgroundColor' => [
                        '#ef4444',
                        '#3b82f6',
                        '#f59e0b',
                        '#22c55e',
                        '#dc2626',
                        '#2563eb',
                        '#d97706',
                        '#16a34a',
                    ],
                    'borderWidth' => 0,
                    'borderColor' => 'transparent',
                    'hoverBorderWidth' => 0,
                ],
            ],
            'labels' => $grouped->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '68%',
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 14,
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
