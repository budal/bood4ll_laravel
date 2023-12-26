<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\Response;
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

    public function fullAccess(User $user, Role $role, Request $request): Response
    {
        if (!$user->hasFullAccess()) {
            return collect($request->list)->contains($user->id) && collect($request->list)->count() === 1
                ? Response::allow()
                : Response::deny("You can only manage your own data.");
        } else {
            $usersToEditUnits = User::join('unit_user', 'unit_user.user_id', '=', 'users.id')
                ->select('users.id', 'unit_user.unit_id')
                ->whereIn('users.id', $request->list)
                ->get()
                ->pluck('unit_id');

            return $usersToEditUnits->intersect($user->unitsIds())->count() === collect($request->list)->count()
                ? Response::allow()
                : Response::deny("You can only manage data from the location where you are classified.");
        }
    }

    public function isActive(User $user, Role $role): Response
    {
        return $role->deleted_at === null
            ? Response::allow()
            : Response::deny("This record does not exist.");
    }

    public function isOwner(User $user, Role $role): Response
    {
        return $user->id === $role->owner
            ? Response::allow()
            : Response::deny("You are not the owner of this record.");
    }

    public function canEdit(User $user, Role $role): Response
    {
        return $user->roles()
            ->where('roles.active', true)->where(function ($query) {
                $query->where('roles.lock_on_expire', true);
                $query->where('roles.expires_at', '>=', 'NOW()');
                $query->orwhere('roles.lock_on_expire', false);
            })->get()->pluck('id')->contains($role->id)
            ? Response::allow()
            : Response::deny("You cannot update this record.");
    }

    public function canEditManagementRoles(User $user, Role $role): Response
    {
        return $role->manager === false
            ? Response::allow()
            : Response::deny("You can't manage this record.");
    }

    public function canDestroyOrRestore(User $user, Request $request): Response
    {
        $roles = Role::whereIn('roles.id', $request->list)->withTrashed()->get();

        return $roles->pluck('owner')->intersect($user->id)->count() === collect($request->list)->count()
            ? Response::allow()
            : Response::deny("You are trying to destroy/restore items that don't belong to you.");
    }
}
