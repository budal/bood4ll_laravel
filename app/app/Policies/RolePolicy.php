<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class RolePolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function fullAccess(User $user, Role $role, Request $request): bool
    {
        dd($user, $role, $request);
        // if (!$user->hasFullAccess()) {
        //     return $user->id === $userToEdit->id;
        // } else {
        //     return true;
        // }
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
