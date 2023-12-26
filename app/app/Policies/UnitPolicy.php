<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class UnitPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function fullAccess(User $user, Unit $unit, Request $request): Response
    {
        if ($user->roles()->where('roles.id', $unit->id)->count() === 0) {
            return Response::deny("You do not have permission to access this feature.");
        }

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

            return $usersToEditUnits->intersect($user->unitsIds())->count() == collect($request->list)->count()
                ? Response::allow()
                : Response::deny("You can only manage data from the location where you are classified.");
        }
    }

    public function isActive(User $user, Unit $unit): Response
    {
        return $unit->deleted_at === null
            ? Response::allow()
            : Response::deny("This record does not exist.");
    }

    public function isOwner(User $user, Unit $unit): Response
    {
        return $user->id === $unit->owner
            ? Response::allow()
            : Response::deny("You are not the owner of this record.");
    }

    public function canEdit(User $user, Unit $unit): Response
    {
        return $user->unitsIds()->contains($unit->id)
            ? Response::allow()
            : Response::deny("You cannot update this record.");
    }

    public function canDestroyOrRestore(User $user, Request $request): Response
    {
        $units = Unit::whereIn('units.id', $request->list)->withTrashed()->get();

        return $units->pluck('owner')->intersect($user->id)->count() === collect($request->list)->count()
            ? Response::allow()
            : Response::deny("You are trying to destroy/restore items that don't belong to you.");
    }
}
