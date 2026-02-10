<?php

namespace App\Policies;

use App\Models\Petugas;
use App\Models\User;

class PetugasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isAtasan();
    }

    public function view(User $user, Petugas $petugas): bool
    {
        if ($user->isSuperAdmin() || $user->isAtasan()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $petugas->satker_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, Petugas $petugas): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $petugas->satker_id);
    }

    public function delete(User $user, Petugas $petugas): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->isAdminInSameSatker($user, $petugas->satker_id);
    }

    public function restore(User $user, Petugas $petugas): bool
    {
        return $this->delete($user, $petugas);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function forceDelete(User $user, Petugas $petugas): bool
    {
        return $this->delete($user, $petugas);
    }

    private function isAdminInSameSatker(User $user, int $satkerId): bool
    {
        return $user->isAdmin() && $user->satker_id === $satkerId;
    }
}
