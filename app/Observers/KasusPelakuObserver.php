<?php

namespace App\Observers;

use App\Models\Kasus;
use App\Models\KasusPelaku;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;

class KasusPelakuObserver
{
    public function created(KasusPelaku $pelaku): void
    {
        $kasus = $this->resolveKasus($pelaku);

        AuditLogger::log(
            module: 'kasus_pelaku',
            action: 'create',
            summary: sprintf('Data tersangka pada kasus %s ditambahkan: %s.', $kasus?->nomor_lp ?? '#'.$pelaku->kasus_id, $pelaku->nama),
            auditable: $pelaku,
            satkerId: $kasus?->satker_id,
        );
    }

    public function updated(KasusPelaku $pelaku): void
    {
        $changes = Arr::except($pelaku->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $kasus = $this->resolveKasus($pelaku);

        AuditLogger::log(
            module: 'kasus_pelaku',
            action: 'update',
            summary: sprintf('Data tersangka pada kasus %s diperbarui: %s.', $kasus?->nomor_lp ?? '#'.$pelaku->kasus_id, $pelaku->nama),
            auditable: $pelaku,
            satkerId: $kasus?->satker_id,
            changes: AuditChangeFormatter::format($pelaku, $changes),
        );
    }

    public function deleted(KasusPelaku $pelaku): void
    {
        $kasus = $this->resolveKasus($pelaku);

        AuditLogger::log(
            module: 'kasus_pelaku',
            action: 'delete',
            summary: sprintf('Data tersangka pada kasus %s dihapus: %s.', $kasus?->nomor_lp ?? '#'.$pelaku->kasus_id, $pelaku->nama),
            auditable: null,
            satkerId: $kasus?->satker_id,
        );
    }

    private function resolveKasus(KasusPelaku $pelaku): ?Kasus
    {
        return $pelaku->kasus()->withoutGlobalScopes()->first();
    }
}
