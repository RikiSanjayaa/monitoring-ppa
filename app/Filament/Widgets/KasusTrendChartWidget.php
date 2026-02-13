<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Support\KasusDashboardFilters;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class KasusTrendChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Jumlah Kasus per Bulan';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $records = KasusDashboardFilters::apply(Kasus::query(), $this->filters ?? [])
            ->select(['tanggal_lp'])
            ->whereNotNull('tanggal_lp')
            ->orderBy('tanggal_lp')
            ->get();

        $grouped = $records
            ->groupBy(fn (Kasus $kasus): string => Carbon::parse($kasus->tanggal_lp)->format('Y-m'))
            ->map(fn ($items): int => $items->count())
            ->sortKeys();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kasus',
                    'data' => $grouped->values()->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.22)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointBackgroundColor' => '#f59e0b',
                    'pointBorderColor' => '#f59e0b',
                ],
            ],
            'labels' => $grouped
                ->keys()
                ->map(fn (string $month): string => Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y'))
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
