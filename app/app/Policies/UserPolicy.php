<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

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

    public function access(User $user): bool
    {
        dd($user->getAllAbilities->where('ability', '!=', null)->pluck('ability'));
        return $user->getAllAbilities->where('ability', '!=', null)->pluck('ability')->contains(Route::current()->getName());
    }

    public function fullAccess(User $user, User $userToEdit): bool
    {
        if (!$user->hasFullAccess()) {
            return $user->id === $userToEdit->id;
        } else {
            return true;
        }
    }

    public function allowedUnits(User $user): bool
    {
        if ($user->canManageNested()) {
            $allowedUnits = $user->units()->get()->flatten()->pluck('id')->union(
                $user->units()->get()->map->getAllChildren()->flatten()->pluck('id')
            );
        } else {
            $allowedUnits = $user->units->pluck('id');
        }

        return $allowedUnits->intersect($user->units->pluck('id'))->count() > 0;
    }
}
