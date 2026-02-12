<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $changes
     * @param  array<string, mixed>|null  $meta
     */
    public static function log(
        string $module,
        string $action,
        string $summary,
        ?Model $auditable = null,
        ?int $satkerId = null,
        ?array $changes = null,
        ?array $meta = null,
        ?User $actor = null,
    ): AuditLog {
        /** @var User|null $resolvedActor */
        $resolvedActor = $actor ?? Auth::user();

        if (! $satkerId && $auditable && isset($auditable->satker_id)) {
            $satkerId = (int) ($auditable->satker_id ?: 0) ?: null;
        }

        return AuditLog::query()->create([
            'actor_id' => $resolvedActor?->id,
            'satker_id' => $satkerId,
            'module' => $module,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'summary' => $summary,
            'changes' => $changes,
            'meta' => $meta,
        ]);
    }
}
