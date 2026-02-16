<?php

namespace App\Support;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use Illuminate\Support\Collection;

class KasusRecapSummary
{
    /**
     * @param  Collection<int, Kasus>  $records
     * @param  Collection<int, array{key: string, label: string, id: int}>|null  $penyelesaianColumns
     * @return array{
     *     penyelesaian_columns: Collection<int, array{key: string, label: string, id: int}>,
     *     rows: Collection<int, array<string, mixed>>,
     *     totals: array<string, mixed>
     * }
     */
    public static function fromCollection(Collection $records, ?Collection $penyelesaianColumns = null): array
    {
        $penyelesaianColumns ??= KasusSummary::penyelesaianColumns($records);

        $rows = $records
            ->groupBy(fn (Kasus $record): string => $record->perkara?->nama ?? 'Lainnya')
            ->map(function (Collection $items, string $jenis) use ($penyelesaianColumns): array {
                /** @var Kasus|null $first */
                $first = $items->first();
                $lidik = $items->filter(
                    fn (Kasus $kasus): bool => $kasus->dokumen_status?->value === DokumenStatus::Lidik->value
                )->count();
                $sidik = $items->filter(
                    fn (Kasus $kasus): bool => $kasus->dokumen_status?->value === DokumenStatus::Sidik->value
                )->count();
                $penyelesaianCounts = self::penyelesaianCounts($items, $penyelesaianColumns);

                return [
                    'jenis' => $jenis,
                    'pasal' => self::joinPasal($items),
                    'jumlah_korban' => self::totalKorban($items),
                    'jumlah_tersangka' => self::totalTersangka($items),
                    'jumlah_saksi' => $items->sum(fn (Kasus $kasus): int => $kasus->saksis->count()),
                    'lidik' => $lidik,
                    'sidik' => $sidik,
                    'penyelesaian_counts' => $penyelesaianCounts,
                    'jumlah' => array_sum($penyelesaianCounts),
                ];
            })
            ->values();

        $totalPenyelesaianCounts = self::emptyPenyelesaianCounts($penyelesaianColumns);

        foreach ($penyelesaianColumns as $column) {
            $key = $column['key'];
            $totalPenyelesaianCounts[$key] = $rows->sum(
                fn (array $row): int => (int) ($row['penyelesaian_counts'][$key] ?? 0)
            );
        }

        $totals = [
            'jumlah_korban' => $rows->sum('jumlah_korban'),
            'jumlah_tersangka' => $rows->sum('jumlah_tersangka'),
            'jumlah_saksi' => $rows->sum('jumlah_saksi'),
            'lidik' => $rows->sum('lidik'),
            'sidik' => $rows->sum('sidik'),
            'penyelesaian_counts' => $totalPenyelesaianCounts,
            'jumlah' => $rows->sum('jumlah'),
        ];

        return [
            'penyelesaian_columns' => $penyelesaianColumns,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * @param  Collection<int, Kasus>  $records
     * @param  Collection<int, array{key: string, label: string, id: int}>  $penyelesaianColumns
     * @return array<string, int>
     */
    private static function penyelesaianCounts(Collection $records, Collection $penyelesaianColumns): array
    {
        $counts = self::emptyPenyelesaianCounts($penyelesaianColumns);
        $countsById = $records->countBy(fn (Kasus $kasus): int => (int) ($kasus->penyelesaian_id ?? 0));

        foreach ($penyelesaianColumns as $column) {
            $counts[$column['key']] = (int) $countsById->get($column['id'], 0);
        }

        return $counts;
    }

    /**
     * @param  Collection<int, array{key: string, label: string, id: int}>  $penyelesaianColumns
     * @return array<string, int>
     */
    private static function emptyPenyelesaianCounts(Collection $penyelesaianColumns): array
    {
        $counts = [];

        foreach ($penyelesaianColumns as $column) {
            $counts[$column['key']] = 0;
        }

        return $counts;
    }

    /**
     * @param  Collection<int, Kasus>  $records
     */
    private static function totalKorban(Collection $records): int
    {
        return $records->sum(fn (Kasus $kasus): int => $kasus->korbans->count());
    }

    /**
     * @param  Collection<int, Kasus>  $records
     */
    private static function totalTersangka(Collection $records): int
    {
        return $records->sum(fn (Kasus $kasus): int => $kasus->tersangkas->count());
    }

    /**
     * @param  Collection<int, Kasus>  $records
     */
    private static function joinPasal(Collection $records): string
    {
        $pasals = $records
            ->pluck('tindak_pidana_pasal')
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values();

        return $pasals->isEmpty() ? '-' : $pasals->join(', ');
    }
}
