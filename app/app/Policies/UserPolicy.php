<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Route;

class UserPolicy
{
    public function before(User $user): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
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

    public function access(User $user, string $route = null, User $userToEdit = null): Response
    {
        return $user->getAbilities
            ->pluck('ability')
            ->contains($route ?? Route::current()->getName())
            ? (
                $userToEdit == null || $user->id === $userToEdit->id
                ? Response::allow()
                : $this->hasFullAccess($user, $route, $userToEdit)
            )
            : Response::deny("You cannot access this feature.");
    }

    public function hasFullAccess(User $user, string $route = null): Response
    {
        return $user->getAbilities()
            ->where('full_access', true)
            ->pluck('ability')
            ->contains($route ?? Route::current()->getName())
            ? Response::allow()
            : Response::deny("You can only manage your own data.");
    }

    public function canManageNestedData(User $user, string $route = null): bool
    {
        return $user->getAbilities()
            ->where('manage_nested', true)
            ->pluck('ability')
            ->contains($route ?? Route::current()->getName())
            ? true
            : false;
    }

    public function canRemoveOnChangeUnit(User $user, string $route = null): Response
    {
        return $user->getAbilities()
            ->where('remove_on_change_unit', true)
            ->pluck('ability')
            ->contains($route ?? Route::current()->getName())
            ? Response::allow()
            : Response::deny("This role will be removed when user change unit.");
    }





    public function allowedUnits(User $user, User $userToEdit): Response
    {
        return $user->unitsIds()->intersect($userToEdit->units->pluck('id'))->count() > 0
            ? Response::allow()
            : Response::deny("You cannot manage nested data 2.");
    }
}
