<?php

namespace App\Policies;

use App\Models\Penyelesaian;
use App\Models\User;

class PenyelesaianPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Penyelesaian $penyelesaian): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Penyelesaian $penyelesaian): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Penyelesaian $penyelesaian): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Penyelesaian $penyelesaian): bool
    {
        return $user->isSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Penyelesaian $penyelesaian): bool
    {
        return $user->isSuperAdmin();
    }
}
