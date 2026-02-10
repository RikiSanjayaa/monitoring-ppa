<?php

namespace App\Policies;

use App\Models\Perkara;
use App\Models\User;

class PerkaraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Perkara $perkara): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Perkara $perkara): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Perkara $perkara): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Perkara $perkara): bool
    {
        return $user->isSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Perkara $perkara): bool
    {
        return $user->isSuperAdmin();
    }
}
