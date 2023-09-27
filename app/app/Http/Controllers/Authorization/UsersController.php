<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    
    public function __form()
    {
        return [
            [
                'title' => "Profile Information",
                'subtitle' => "Update your account's profile information and email address",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                        ],
                        [
                            'type' => "input",
                            'name' => "email",
                            'title' => "Email",
                        ],
                    ],
                    [
                        [
                            'type' => "input",
                            'name' => "username",
                            'title' => "Username",
                            'span' => 2,
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create(User $user)
    {
        $collection = collect(DB::getSchemaBuilder()->getColumnListing($user->getTable()));
        $keyed = $collection->mapWithKeys(function ($value, $key) { return [$value => '']; });
         
        return Inertia::render('Default/Create', [
            'body' => $this->__form(),
            'data' => $keyed->all()
        ]);
    }

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        // return Redirect::route('profile.edit');
    }
    
    /**
     * Display the user's profile form.
     */
    public function edit(User $user): Response
    {
        // dd(DB::getSchemaBuilder()->getColumnListing('users'), $user->getTable(), $user->getFillable(), $user);
        
        return Inertia::render('Default/Edit', [
            'body' => $this->__form(),
            'data' => collect($user)->all()
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        dd($request);
        
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        // return Redirect::route('profile.edit');
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

    public function restore(User $user)
    {
        $user->restore();

        return Redirect::back()->with('status', 'User restored.');
    }
}
