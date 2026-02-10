<?php

namespace App\Policies;

use App\Models\Rtl;
use App\Models\User;

class RtlPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isAtasan();
    }

    public function view(User $user, Rtl $rtl): bool
    {
        if ($user->isSuperAdmin() || $user->isAtasan()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $rtl->kasus?->satker_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, Rtl $rtl): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $rtl->kasus?->satker_id);
    }

    public function delete(User $user, Rtl $rtl): bool
    {
        return $this->update($user, $rtl);
    }

    public function restore(User $user, Rtl $rtl): bool
    {
        return $this->update($user, $rtl);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function forceDelete(User $user, Rtl $rtl): bool
    {
        return $this->update($user, $rtl);
    }

    private function isAdminInSameSatker(User $user, ?int $satkerId): bool
    {
        return $user->isAdmin() && $user->satker_id && $satkerId && $user->satker_id === $satkerId;
    }
}
