<?php

namespace App\Policies;

use App\Models\Kasus;
use App\Models\User;

class KasusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isAtasan();
    }

    public function view(User $user, Kasus $kasus): bool
    {
        if ($user->isSuperAdmin() || $user->isAtasan()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $kasus->satker_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, Kasus $kasus): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $kasus->satker_id);
    }

    public function delete(User $user, Kasus $kasus): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $kasus->satker_id);
    }

    public function restore(User $user, Kasus $kasus): bool
    {
        return $this->delete($user, $kasus);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function forceDelete(User $user, Kasus $kasus): bool
    {
        return $this->delete($user, $kasus);
    }

    private function isAdminInSameSatker(User $user, int $satkerId): bool
    {
        return $user->isAdmin() && $user->satker_id === $satkerId;
    }
}
