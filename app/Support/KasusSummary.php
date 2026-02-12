<?php

namespace App\Support;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\Penyelesaian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KasusSummary
{
    /**
     * @param  Collection<int, array{key: string, label: string, id: int}>|null  $penyelesaianColumns
     * @return Collection<int, array<string, int|string>>
     */
    public static function fromQuery(Builder $query, ?Collection $penyelesaianColumns = null): Collection
    {
        $records = (clone $query)
            ->with('satker:id,nama')
            ->get();

        return self::fromCollection($records, $penyelesaianColumns);
    }

    /**
     * @param  Collection<int, Kasus>  $records
     * @param  Collection<int, array{key: string, label: string, id: int}>|null  $penyelesaianColumns
     * @return Collection<int, array<string, int|string>>
     */
    public static function fromCollection(Collection $records, ?Collection $penyelesaianColumns = null): Collection
    {
        $penyelesaianColumns ??= self::penyelesaianColumns($records);

        $summary = $records
            ->groupBy(fn (Kasus $kasus): string => $kasus->satker?->nama ?? '-')
            ->map(function (Collection $items, string $unitKerja) use ($penyelesaianColumns): array {
                $row = [
                    'unit_kerja' => $unitKerja,
                    'jumlah' => $items->count(),
                    'lidik' => $items->filter(
                        fn (Kasus $kasus): bool => $kasus->dokumen_status?->value === DokumenStatus::Lidik->value
                    )->count(),
                    'sidik' => $items->filter(
                        fn (Kasus $kasus): bool => $kasus->dokumen_status?->value === DokumenStatus::Sidik->value
                    )->count(),
                ];

                $countsByPenyelesaianId = $items->countBy(fn (Kasus $kasus): int => (int) ($kasus->penyelesaian_id ?? 0));

                foreach ($penyelesaianColumns as $column) {
                    $row[$column['key']] = (int) $countsByPenyelesaianId->get($column['id'], 0);
                }

                return $row;
            })
            ->sortBy('unit_kerja')
            ->values();

        $totals = [
            'unit_kerja' => 'TOTAL',
            'jumlah' => $summary->sum('jumlah'),
            'lidik' => $summary->sum('lidik'),
            'sidik' => $summary->sum('sidik'),
        ];

        foreach ($penyelesaianColumns as $column) {
            $totals[$column['key']] = $summary->sum($column['key']);
        }

        return $summary->push($totals);
    }

    /**
     * @param  Collection<int, Kasus>|null  $records
     * @return Collection<int, array{key: string, label: string, id: int}>
     */
    public static function penyelesaianColumns(?Collection $records = null): Collection
    {
        $usedIds = ($records ?? collect())
            ->pluck('penyelesaian_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $penyelesaians = Penyelesaian::query()
            ->select(['id', 'nama', 'is_active'])
            ->when(
                $usedIds->isNotEmpty(),
                fn (Builder $builder): Builder => $builder->where(function (Builder $statusQuery) use ($usedIds): void {
                    $statusQuery
                        ->where('is_active', true)
                        ->orWhereIn('id', $usedIds->all());
                }),
                fn (Builder $builder): Builder => $builder->where('is_active', true)
            )
            ->orderBy('id')
            ->get();

        return $penyelesaians
            ->reject(fn (Penyelesaian $penyelesaian): bool => in_array(
                Str::lower(trim($penyelesaian->nama)),
                [DokumenStatus::Lidik->value, DokumenStatus::Sidik->value],
                true,
            ))
            ->map(fn (Penyelesaian $penyelesaian): array => [
                'id' => (int) $penyelesaian->id,
                'key' => self::keyForPenyelesaian((int) $penyelesaian->id),
                'label' => (string) $penyelesaian->nama,
            ])
            ->values();
    }

    private static function keyForPenyelesaian(int $id): string
    {
        return 'penyelesaian_'.$id;
    }
}
