<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

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

    public function edit(User $user, User $userToEdit): bool
    {
        if (!$user->hasFullAccess()) {
            return $user->id === $userToEdit->id;
        } else {
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

    public function canManageNested(User $user): bool
    {
        return $user->canManageNested() ? true : false;
    }

    public function units(User $user): bool
    {
        dd($user->hasFullAccess());

        // request()->route()->getName()
        // return $user->id === $post->user_id;

        // auth()->check()

        // return $user->id === $post->user_id
        //     ? Response::allow()
        //     : Response::deny('You do not own this post.');

        return true;

        // dd($user);

        // return Redirect::back()->with([
        //     'toast_type' => 'info',
        //     'toast_message' => "Logged as ':user'.",
        //     'toast_replacements' => ['user' => 'eu'],
        // ]);
    }
}
