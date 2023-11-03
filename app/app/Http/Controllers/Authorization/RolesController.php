<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Role;
use App\Models\Ability;

use App\Http\Requests\RolesRequest;

class RolesController extends Controller
{
    public function index(Request $request): Response
    {
        $routes = [
            'editRoute' => "apps.roles.edit",
            'destroyRoute' => "apps.roles.destroy",
            'restoreRoute' => "apps.roles.restore",
        ];
        
        $titles = [
            [
                'type' => 'simple',
                'title' => 'Role',
                'field' => 'name',
            ],
        ];

        $menu = [
            [
                'icon' => "mdi:badge-account-horizontal-outline",
                'title' => "Add permission",
                'route' => "apps.roles.create"
            ],
            [
                'icon' => "mdi:list-status",
                'title' => "Abilities management",
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
        })->toArray();

        return [
            [
                'id' => "role",
                'title' => "Roles management",
                'subtitle' => "Role name and abilities",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                            'required' => true,
                        ],
                        [
                            'type' => "select",
                            'name' => "abilities",
                            'title' => "Abilities",
                            'content' => $abilities,
                            'required' => true,
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
            'form' => $this->__form(),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.store'),
                    'method' => 'post'
                ],
            ],
        ]);
    }

    public function store(RolesRequest $request): RedirectResponse
    {
        dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        return Redirect::route('apps.roles.index')->with('status', 'Role created.');
    }
    
    public function edit(Role $role): Response
    {
        $role['abilities'] = $role->listAbilities()->get()->map->only('ability_id')->pluck('ability_id');
        
        return Inertia::render('Default/Edit', [
            'form' => $this->__form(),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $role
        ]);
    }

    public function update(Request $request): RedirectResponse
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
