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

    public function access(User $user, string $route = null): Response
    {
        return $user->getAllAbilities->whereNotNull('ability')->pluck('ability')
            ->contains($route ?? Route::current()->getName())
            ? Response::allow()
            : Response::deny("You cannot access this feature.");
    }

    public function verify(User $user, string $attributes = null): Response
    {
        return $user->isSuperAdmin()
            ? Response::allow()
            : Response::deny("Only superadministrators can access this feature.");
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

    public function canRemoveOnChangeUnit(User $user): Response
    {
        return $user->canRemoveOnChangeUnit()
            ? Response::allow()
            : Response::deny("This role will be removed when user change unit.");
    }





    public function fullAccess(User $user, User $userToEdit): Response
    {
        if (!$user->hasFullAccess()) {
            return $user->id === $userToEdit->id
                ? Response::allow()
                : Response::deny("You can only manage your own data 2.");
        } else {
            return Response::allow();
        }
    }

    public function allowedUnits(User $user, User $userToEdit): Response
    {
        return $user->unitsIds()->intersect($userToEdit->units->pluck('id'))->count() > 0
            ? Response::allow()
            : Response::deny("You cannot manage nested data 2.");
    }
}
