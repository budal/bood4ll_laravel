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



use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Role;
use App\Models\Ability;

class RolesController extends Controller
{
    /**
     * Display the users list.
     */
    public function index(Request $request): Response
    {
        $titles = [
            [
                'type' => 'composite',
                'title' => 'Role',
                'field' => 'name',
                'fields' => ['name', 'email']
            ],
        ];

        $routes = [
            'createRoute' => "apps.roles.create",
            'editRoute' => "apps.roles.edit",
            'destroyRoute' => "apps.roles.destroy",
            'restoreRoute' => "apps.roles.restore",
        ];

        $menu = [
            [
                'icon' => "PlusIcon",
                'title' => "Add permission",
                'route' => "apps.roles.create"
            ],
            [
                'icon' => "ListBulletIcon",
                'title' => "Show all abilities",
                'route' => "apps.abilities.index"
            ],            
        ];

        return Inertia::render('Default/Index', [
            'title' => "Roles management",
            'subtitle' => "Define roles, grouping abilities to define specific access.",
            'softDelete' => Role::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => $routes,
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'menu' => $menu,
            'items' => Role::filter($request->all('search', 'sorted', 'trashed'))
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
        ]);
    }

    
    public function __form()
    {
        $abilities = Ability::sort("name")->get()->map(function ($ability) {
            $id = $ability['id'];
            $title = $ability['name'];

            return compact('id', 'title');
        })->values()->toArray();

        return [
            [
                'title' => "Roles management",
                'subtitle' => "Role name and abilities",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                        ],
                        [
                            'type' => "select",
                            'name' => "abilities",
                            'title' => "Ability",
                            'content' => $abilities,
                            'multiple' => true,
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create()
    {
        return Inertia::render('Default/Create', [
            'body' => $this->__form(),
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

        return Redirect::route('apps.roles')->with('status', 'Role created.');
    }
    
    /**
     * Display the user's profile form.
     */
    public function edit(Role $role): Response
    {
        return Inertia::render('Default/Edit', [
            'body' => $this->__form(),
            'data' => $role
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
        return Redirect::back()->with('status', 'Role edited.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = Role::whereIn('id', $items['ids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        return back()->with('status', 'Users removed succesfully!');
    }

    public function restore(Role $role)
    {
        $role->restore();

        return Redirect::back()->with('status', 'Role restored.');
    }
}
