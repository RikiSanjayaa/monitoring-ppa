<?php

namespace App\Policies;

use App\Models\Satker;
use App\Models\User;

class SatkerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Satker $satker): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Satker $satker): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Satker $satker): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Satker $satker): bool
    {
        return $user->isSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Satker $satker): bool
    {
        return $user->isSuperAdmin();
    }
}
