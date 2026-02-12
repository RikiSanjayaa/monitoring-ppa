<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Support\KasusDashboardFilters;
use App\Support\KasusSummary;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class SummaryTableWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.summary-table-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $query = KasusDashboardFilters::apply(Kasus::query(), $this->filters ?? []);

        return [
            'summaryRows' => KasusSummary::fromQuery($query),
        ];
    }
}
