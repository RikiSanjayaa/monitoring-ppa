<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Filament\Resources\KasusResource;
use App\Models\Kasus;
use App\Models\User;
use App\Support\AuditLogger;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

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
    }

    public function updated(Kasus $kasus): void
    {
        $changes = Arr::except($kasus->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $original = $kasus->getOriginal();

        $formattedChanges = collect($changes)
            ->mapWithKeys(fn ($newValue, string $field): array => [
                $field => [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ],
            ])
            ->all();

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

        if (! $importantChanged) {
            return;
        }

        $this->sendImportantChangeNotification($kasus);
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
    }

    private function sendImportantChangeNotification(Kasus $kasus): void
    {
        $recipients = User::query()
            ->where(function ($query) use ($kasus): void {
                $query->where('role', UserRole::SuperAdmin->value)
                    ->orWhere(function ($inner) use ($kasus): void {
                        $inner->where('role', UserRole::Admin->value)
                            ->where('satker_id', $kasus->satker_id);
                    });
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Perubahan penting data kasus')
            ->body(sprintf('Kasus %s mengalami perubahan pada field penting.', $kasus->nomor_lp))
            ->warning()
            ->actions([
                Action::make('view')
                    ->label('Lihat Kasus')
                    ->url(KasusResource::getUrl('view', ['record' => $kasus])),
            ])
            ->sendToDatabase($recipients);
    }
}
