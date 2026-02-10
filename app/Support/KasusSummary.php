<?php

namespace App\Support;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KasusSummary
{
    /**
     * @return Collection<int, array<string, int|string>>
     */
    public static function fromQuery(Builder $query): Collection
    {
        $records = (clone $query)
            ->with(['satker:id,nama', 'penyelesaian:id,nama'])
            ->get();

        return self::fromCollection($records);
    }

    /**
     * @param Collection<int, Kasus> $records
     * @return Collection<int, array<string, int|string>>
     */
    public static function fromCollection(Collection $records): Collection
    {
        $summary = $records
            ->groupBy(fn (Kasus $kasus): string => $kasus->satker?->nama ?? '-')
            ->map(function (Collection $items, string $unitKerja): array {
                return [
                    'unit_kerja' => $unitKerja,
                    'jumlah' => $items->count(),
                    'lidik' => $items->filter(fn (Kasus $kasus): bool => $kasus->dokumen_status?->value === DokumenStatus::Lidik->value)->count(),
                    'sidik' => $items->filter(fn (Kasus $kasus): bool => $kasus->dokumen_status?->value === DokumenStatus::Sidik->value)->count(),
                    'henti_lidik' => self::countByPenyelesaian($items, 'Henti Lidik'),
                    'p21' => self::countByPenyelesaian($items, 'P21'),
                    'sp3' => self::countByPenyelesaian($items, 'SP3'),
                    'diversi' => self::countByPenyelesaian($items, 'Diversi'),
                    'rj' => self::countByPenyelesaian($items, 'RJ'),
                    'limpah' => self::countByPenyelesaian($items, 'Limpah'),
                ];
            })
            ->sortBy('unit_kerja')
            ->values();

        $totals = [
            'unit_kerja' => 'TOTAL',
            'jumlah' => $summary->sum('jumlah'),
            'lidik' => $summary->sum('lidik'),
            'sidik' => $summary->sum('sidik'),
            'henti_lidik' => $summary->sum('henti_lidik'),
            'p21' => $summary->sum('p21'),
            'sp3' => $summary->sum('sp3'),
            'diversi' => $summary->sum('diversi'),
            'rj' => $summary->sum('rj'),
            'limpah' => $summary->sum('limpah'),
        ];

        return $summary->push($totals);
    }

    /**
     * @param Collection<int, Kasus> $items
     */
    private static function countByPenyelesaian(Collection $items, string $value): int
    {
        return $items->filter(function (Kasus $kasus) use ($value): bool {
            $name = $kasus->penyelesaian?->nama;

            if (! $name) {
                return false;
            }

            return Str::lower($name) === Str::lower($value);
        })->count();
    }
}
