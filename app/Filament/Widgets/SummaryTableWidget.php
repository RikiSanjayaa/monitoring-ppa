<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Support\KasusSummary;
use Filament\Widgets\Widget;

class SummaryTableWidget extends Widget
{
    protected static string $view = 'filament.widgets.summary-table-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'summaryRows' => KasusSummary::fromQuery(Kasus::query()),
        ];
    }
}
