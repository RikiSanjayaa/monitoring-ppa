<?php

namespace App\Observers;

use App\Models\Kasus;
use App\Models\Rtl;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;

class RtlObserver
{
    public function created(Rtl $rtl): void
    {
        $kasus = $this->resolveKasus($rtl);

        AuditLogger::log(
            module: 'rtl',
            action: 'create',
            summary: sprintf('RTL kasus %s ditambahkan.', $kasus?->nomor_lp ?? '#'.$rtl->kasus_id),
            auditable: $rtl,
            satkerId: $kasus?->satker_id,
        );
    }

    public function updated(Rtl $rtl): void
    {
        $changes = Arr::except($rtl->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $kasus = $this->resolveKasus($rtl);

        AuditLogger::log(
            module: 'rtl',
            action: 'update',
            summary: sprintf('RTL kasus %s diperbarui.', $kasus?->nomor_lp ?? '#'.$rtl->kasus_id),
            auditable: $rtl,
            satkerId: $kasus?->satker_id,
            changes: AuditChangeFormatter::format($rtl, $changes),
        );
    }

    public function deleted(Rtl $rtl): void
    {
        $kasus = $this->resolveKasus($rtl);

        AuditLogger::log(
            module: 'rtl',
            action: 'delete',
            summary: sprintf('RTL kasus %s dihapus.', $kasus?->nomor_lp ?? '#'.$rtl->kasus_id),
            auditable: null,
            satkerId: $kasus?->satker_id,
        );
    }

    private function resolveKasus(Rtl $rtl): ?Kasus
    {
        return $rtl->kasus()->withoutGlobalScopes()->first();
    }
}
