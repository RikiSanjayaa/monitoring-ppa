<?php

namespace App\Filament\Widgets;

use App\Models\Kasus;
use App\Models\Satker;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SatkerPerformanceChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.satker-performance-chart-widget';

    protected int|string|array $columnSpan = 'full';

    public string $periode = 'bulan_ini';

    public static function canView(): bool
    {
        return Auth::user()?->isSuperAdmin() || Auth::user()?->isAtasan();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'chartData' => $this->getChartData(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChartData(): array
    {
        $satkers = Satker::ordered()->get();

        $query = Kasus::query();

        $now = Carbon::now();

        switch ($this->periode) {
            case 'bulan_lalu':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $query->whereDate('tanggal_lp', '>=', $start)
                    ->whereDate('tanggal_lp', '<=', $end);
                break;

            case '1_tahun':
                $start = $now->copy()->subYear()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $query->whereDate('tanggal_lp', '>=', $start)
                    ->whereDate('tanggal_lp', '<=', $end);
                break;

            case 'bulan_ini':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $query->whereDate('tanggal_lp', '>=', $start)
                    ->whereDate('tanggal_lp', '<=', $end);
                break;
        }

        $kasusCounts = $query
            ->selectRaw('satker_id, COUNT(*) as total')
            ->groupBy('satker_id')
            ->pluck('total', 'satker_id');

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($satkers as $satker) {
            $count = $kasusCounts->get($satker->id, 0);
            $labels[] = $satker->nama;
            $data[] = $count;
            $colors[] = $count > 0 ? '#6366f1' : '#374151';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    /**
     * Called from Alpine via $wire to fetch new chart data for a given period.
     */
    public function fetchChartData(string $periode): array
    {
        $this->periode = $periode;

        return $this->getChartData();
    }
}
