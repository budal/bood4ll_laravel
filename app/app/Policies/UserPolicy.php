<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Route;

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function access(User $user): Response
    {
        return $user->getAllAbilities->whereNotNull('ability')->pluck('ability')
            ->contains(Route::current()->getName())
            ? Response::allow()
            : Response::deny("You do not have permission to access this feature.");
    }

    public function isSuperAdmin(User $user): Response
    {
        return $user->isSuperAdmin()
            ? Response::allow()
            : Response::deny("Only superadministrators can access this feature.");
    }

    public function isManager(User $user): Response
    {
        return $user->isManager()
            ? Response::allow()
            : Response::deny("Only managers can access this feature.");
    }

    public function hasFullAccess(User $user): Response
    {
        return $user->hasFullAccess()
            ? Response::allow()
            : Response::deny("You can only manage your own data.");
    }

    public function canManageNestedData(User $user): Response
    {
        return $user->canManageNested()
            ? Response::allow()
            : Response::deny("You cannot manage nested data.");
    }






    public function fullAccess(User $user, User $userToEdit): Response
    {
        if (!$user->hasFullAccess()) {
            return $user->id === $userToEdit->id
                ? Response::allow()
                : Response::deny("You can only manage your own data.");
        } else {
            return true;
        }
    }

    public function allowedUnits(User $user, User $userToEdit): Response
    {
        if ($user->canManageNested()) {
            $allowedUnits = $user->units()->get()->flatten()->pluck('id')->union(
                $user->units()->get()->map->getAllChildren()->flatten()->pluck('id')
            );
        } else {
            $allowedUnits = $user->units->pluck('id');
        }

        return $allowedUnits->intersect($userToEdit->units->pluck('id'))->count() > 0
            ? Response::allow()
            : Response::deny("Your permission don't provide access to manage nested data.");
    }
}
