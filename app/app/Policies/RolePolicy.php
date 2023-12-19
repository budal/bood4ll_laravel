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
        if ($user->roles()->where('roles.id', $role->id)->count() === 0) {
            return false;
        }

        if (!$user->hasFullAccess()) {
            return collect($request->list)->contains($user->id) && collect($request->list)->count() === 1;
        } else {
            $usersToEditUnits = User::join('unit_user', 'unit_user.user_id', '=', 'users.id')
                ->select('users.id', 'unit_user.unit_id')
                ->whereIn('users.id', $request->list)
                ->get()
                ->pluck('unit_id');

            return $usersToEditUnits->intersect($user->unitsIds())->count() == collect($request->list)->count();
        }
    }

    public function allowedUnits(User $user, Role $role, Request $request): bool
    {
        if ($user->roles()->where('roles.id', $role->id)->count() === 0)
            return false;

        if (!$user->canManageNested()) {
            $allowedUnits = $user->units->pluck('id');
        } else {
            $allowedUnits = $user->units()->get()->flatten()->pluck('id')->union(
                $user->units()->get()->map->getAllChildren()->flatten()->pluck('id')
            );
        }

        $usersToEditUnits = User::join('unit_user', 'unit_user.user_id', '=', 'users.id')
            ->select('users.id', 'unit_user.unit_id')
            ->whereIn('users.id', $request->list)
            ->get()
            ->pluck('unit_id');

        return $usersToEditUnits->intersect($allowedUnits)->count() == collect($request->list)->count();
    }
}
