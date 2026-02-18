<?php

namespace App\Observers;

use App\Filament\Resources\KasusResource;
use App\Models\Kasus;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use App\Support\Notifications\MonitoringDatabaseNotifier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class KasusObserver
{
    /**
     * @var list<string>
     */
    private const IMPORTANT_FIELDS = [
        'nomor_lp',
        'tanggal_lp',
        'dokumen_status',
        'penyelesaian_id',
        'perkara_id',
        'satker_id',
    ];

    public function created(Kasus $kasus): void
    {
        AuditLogger::log(
            module: 'kasus',
            action: 'create',
            summary: sprintf('Kasus %s dibuat.', $kasus->nomor_lp),
            auditable: $kasus,
            satkerId: $kasus->satker_id,
        );

        MonitoringDatabaseNotifier::send(
            title: 'Data Kasus',
            body: sprintf('Kasus %s dibuat.', $kasus->nomor_lp),
            level: 'success',
            satkerId: $kasus->satker_id,
            actorId: Auth::id(),
            actionUrl: KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin'),
            actionLabel: 'Lihat Kasus',
        );
    }

    public function updated(Kasus $kasus): void
    {
        $changes = Arr::except($kasus->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $formattedChanges = AuditChangeFormatter::format($kasus, $changes);

        $importantChanged = collect(array_keys($changes))
            ->intersect(self::IMPORTANT_FIELDS)
            ->isNotEmpty();

        AuditLogger::log(
            module: 'kasus',
            action: 'update',
            summary: sprintf('Kasus %s diperbarui.', $kasus->nomor_lp),
            auditable: $kasus,
            satkerId: $kasus->satker_id,
            changes: $formattedChanges,
            meta: ['important_fields_changed' => $importantChanged],
        );

        MonitoringDatabaseNotifier::send(
            title: 'Data Kasus',
            body: sprintf('Kasus %s diperbarui.', $kasus->nomor_lp),
            level: 'warning',
            satkerId: $kasus->satker_id,
            actorId: Auth::id(),
            actionUrl: KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin'),
            actionLabel: 'Lihat Kasus',
        );
    }

    public function deleted(Kasus $kasus): void
    {
        AuditLogger::log(
            module: 'kasus',
            action: 'delete',
            summary: sprintf('Kasus %s dihapus.', $kasus->nomor_lp),
            auditable: null,
            satkerId: $kasus->satker_id,
        );

        MonitoringDatabaseNotifier::send(
            title: 'Data Kasus',
            body: sprintf('Kasus %s dihapus.', $kasus->nomor_lp),
            level: 'danger',
            satkerId: $kasus->satker_id,
            actorId: Auth::id(),
            actionUrl: KasusResource::getUrl(panel: 'admin'),
            actionLabel: 'Lihat Daftar Kasus',
        );
    }
}
