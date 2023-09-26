<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\User;

class UsersController extends Controller
{
    /**
     * Display the users list.
     */
    public function index(Request $request): Response
    {
        $titles = [
            [
                'type' => 'avatar',
                'title' => 'Avatar',
                'field' => 'uuid',
                'fallback' => 'name',
                'disableSort' => true
            ],
            [
                'type' => 'composite',
                'title' => 'User',
                'field' => 'name',
                'fields' => ['name', 'email']
            ],
            [
                'type' => 'simple',
                'title' => 'Username',
                'field' => 'username'
            ],
            [
                'type' => 'simple',
                'title' => 'Active',
                'field' => 'active'
            ]
        ];

        $routes = [
            'createRoute' => "apps.users.create",
            'editRoute' => "apps.users.edit",
            'destroyRoute' => "apps.users.destroy",
            'restoreRoute' => "apps.users.restore",
        ];

        return Inertia::render('Default/Index', [
            'title' => "Users management",
            'subtitle' => "Manage users informations and authorizations.",
            'routes' => $routes,
            'status' => session('status'),
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'items' => User::filter($request->all('search', 'sorted', 'trashed'))
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'data' => $user
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = User::whereIn('uuid', $items['uuids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        return back()->withInput()->with('status', 'Users removed succesfully!');
    }

    public function restore(Request $request, $uuid)
    {
        User::where('uuid', $uuid)->restore();

        return Redirect::back()->with('status', 'User restored.');
    }
}
