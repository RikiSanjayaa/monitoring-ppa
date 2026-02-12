<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class KasusDashboardFilters
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['from_date']),
                fn (Builder $builder): Builder => $builder->whereDate('tanggal_lp', '>=', (string) $filters['from_date'])
            )
            ->when(
                ! empty($filters['to_date']),
                fn (Builder $builder): Builder => $builder->whereDate('tanggal_lp', '<=', (string) $filters['to_date'])
            )
            ->when(
                ! empty($filters['perkara_id']),
                fn (Builder $builder): Builder => $builder->where('perkara_id', (int) $filters['perkara_id'])
            )
            ->when(
                ! empty($filters['dokumen_status']),
                fn (Builder $builder): Builder => $builder->where('dokumen_status', (string) $filters['dokumen_status'])
            )
            ->when(
                ! empty($filters['satker_id']),
                fn (Builder $builder): Builder => $builder->where('satker_id', (int) $filters['satker_id'])
            );
    }
}
