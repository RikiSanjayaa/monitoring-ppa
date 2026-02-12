<?php

namespace App\Observers;

use App\Models\Kasus;
use App\Models\KasusSaksi;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;

class KasusSaksiObserver
{
    public function created(KasusSaksi $saksi): void
    {
        $kasus = $this->resolveKasus($saksi);

        AuditLogger::log(
            module: 'kasus_saksi',
            action: 'create',
            summary: sprintf('Data saksi pada kasus %s ditambahkan: %s.', $kasus?->nomor_lp ?? '#'.$saksi->kasus_id, $saksi->nama),
            auditable: $saksi,
            satkerId: $kasus?->satker_id,
        );
    }

    public function updated(KasusSaksi $saksi): void
    {
        $changes = Arr::except($saksi->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $kasus = $this->resolveKasus($saksi);

        AuditLogger::log(
            module: 'kasus_saksi',
            action: 'update',
            summary: sprintf('Data saksi pada kasus %s diperbarui: %s.', $kasus?->nomor_lp ?? '#'.$saksi->kasus_id, $saksi->nama),
            auditable: $saksi,
            satkerId: $kasus?->satker_id,
            changes: AuditChangeFormatter::format($saksi, $changes),
        );
    }

    public function deleted(KasusSaksi $saksi): void
    {
        $kasus = $this->resolveKasus($saksi);

        AuditLogger::log(
            module: 'kasus_saksi',
            action: 'delete',
            summary: sprintf('Data saksi pada kasus %s dihapus: %s.', $kasus?->nomor_lp ?? '#'.$saksi->kasus_id, $saksi->nama),
            auditable: null,
            satkerId: $kasus?->satker_id,
        );
    }

    private function resolveKasus(KasusSaksi $saksi): ?Kasus
    {
        return $saksi->kasus()->withoutGlobalScopes()->first();
    }
}
