<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

// RedirectResponse.

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function fullAccess(User $user, User $userToEdit): bool
    {
        if (!$user->hasFullAccess()) {
            return $user->id === $userToEdit->id;
        } else {
            return true;
        }
    }

    public function allowedUnits(User $user, User $userToEdit): bool
    {
        if (!$user->canManageNested()) {
            $allowedUnits = $user->units->pluck('id');
        } else {
            $allowedUnits = $user->units()->get()->flatten()->pluck('id')->union(
                $user->units()->get()->map->getAllChildren()->flatten()->pluck('id')
            );
        }

        return $allowedUnits->intersect($userToEdit->units->pluck('id'))->count() > 0;
    }
}
