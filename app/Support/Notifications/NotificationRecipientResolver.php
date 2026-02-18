<?php

namespace App\Support\Notifications;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationRecipientResolver
{
    /**
     * @return Collection<int, User>
     */
    public static function resolve(?int $satkerId, ?int $excludeUserId = null): Collection
    {
        return User::query()
            ->where(function ($query) use ($satkerId): void {
                $query->where('role', UserRole::SuperAdmin->value)
                    ->orWhere('role', UserRole::Atasan->value);

                if ($satkerId) {
                    $query->orWhere(function ($inner) use ($satkerId): void {
                        $inner->where('role', UserRole::Admin->value)
                            ->where('satker_id', $satkerId);
                    });
                }
            })
            ->when(
                $excludeUserId,
                fn ($query) => $query->whereKeyNot($excludeUserId),
            )
            ->get();
    }
}
