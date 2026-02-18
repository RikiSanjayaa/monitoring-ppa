<?php

namespace App\Observers;

use App\Filament\Resources\KasusResource;
use App\Models\Kasus;
use App\Models\Rtl;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use App\Support\Notifications\MonitoringDatabaseNotifier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

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

        $this->sendNotification(
            rtl: $rtl,
            kasus: $kasus,
            body: sprintf('RTL pada kasus %s ditambahkan.', $kasus?->nomor_lp ?? '#'.$rtl->kasus_id),
            level: 'success',
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

        $this->sendNotification(
            rtl: $rtl,
            kasus: $kasus,
            body: sprintf('RTL pada kasus %s diperbarui.', $kasus?->nomor_lp ?? '#'.$rtl->kasus_id),
            level: 'warning',
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

        $this->sendNotification(
            rtl: $rtl,
            kasus: $kasus,
            body: sprintf('RTL pada kasus %s dihapus.', $kasus?->nomor_lp ?? '#'.$rtl->kasus_id),
            level: 'danger',
        );
    }

    private function resolveKasus(Rtl $rtl): ?Kasus
    {
        return $rtl->kasus()->withoutGlobalScopes()->first();
    }

    private function sendNotification(Rtl $rtl, ?Kasus $kasus, string $body, string $level): void
    {
        MonitoringDatabaseNotifier::send(
            title: 'Timeline RTL',
            body: $body,
            level: $level,
            satkerId: $kasus?->satker_id,
            actorId: Auth::id(),
            actionUrl: $kasus ? KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin') : KasusResource::getUrl(panel: 'admin'),
            actionLabel: $kasus ? 'Lihat Kasus' : 'Lihat Daftar Kasus',
        );
    }
}
