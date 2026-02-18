<?php

namespace App\Support\Notifications;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class MonitoringDatabaseNotifier
{
    public static function send(
        string $title,
        string $body,
        string $level,
        ?int $satkerId = null,
        ?int $actorId = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        $recipients = NotificationRecipientResolver::resolve(
            satkerId: $satkerId,
            excludeUserId: $actorId,
        );

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->body($body);

        match ($level) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'danger' => $notification->danger(),
            default => $notification->info(),
        };

        if ($actionUrl) {
            $notification->actions([
                Action::make('view')
                    ->label($actionLabel ?: 'Lihat Detail')
                    ->url($actionUrl),
            ]);
        }

        $databaseNotification = $notification->toDatabase();

        foreach ($recipients as $recipient) {
            $recipient->notifyNow($databaseNotification);
        }
    }
}
