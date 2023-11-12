<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $items = Role::filter($request->all('search', 'sorted', 'trashed'))
            ->sort($request->sorted ?? "name")
            ->withCount(['abilities', 'users'])
            ->paginate(20)
            ->onEachSide(2)
            ->appends($request->all('search', 'sorted', 'trashed'));
        
        return Inertia::render('Default/Index', [
            'title' => "Roles management",
            'subtitle' => "Define roles, grouping abilities to define specific access.",
            'softDelete' => Role::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => [
                'editRoute' => "apps.roles.edit",
                'destroyRoute' => "apps.roles.destroy",
                'restoreRoute' => "apps.roles.restore",
            ],
            'menu' => [
                [
                    'icon' => "mdi:badge-account-horizontal-outline",
                    'title' => "Role creation",
                    'route' => "apps.roles.create"
                ],
                [
                    'icon' => "mdi:list-status",
                    'title' => "Abilities management",
                    'route' => "apps.abilities.index"
                ],            
            ],
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => [
                [
                    'type' => 'composite',
                    'title' => 'Role',
                    'field' => 'name',
                    'fields' => ['name', 'description'],
                ],
                [
                    'type' => 'simple',
                    'title' => 'Abilities',
                    'field' => 'abilities_count',
                ],
                [
                    'type' => 'simple',
                    'title' => 'Users',
                    'field' => 'users_count',
                ],
            ],
            'items' => $items
        ]);
    }
    
    public function __form(Request $request, Role $role): Array
    {
        $abilities = Ability::sort("name")->get()->map->only(['id', 'name']);

        $items = $role->users()
            ->filter($request->all('search', 'sorted', 'trashed'))
            ->paginate(20)
            ->onEachSide(2)
            ->appends($request->all('search', 'sorted', 'trashed'))
            ->through(function($item){
                $item->id = $item->pivot->role_id;
                return $item;
            });

        return [
            [
                'id' => "role",
                'title' => "Roles management",
                'subtitle' => "Role name, abilities and settings",
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                            'required' => true,
                        ],
                        [
                            'type' => "input",
                            'name' => "description",
                            'title' => "Description",
                            'span' => 2,
                        ],
                        [
                            'type' => "select",
                            'name' => "abilities",
                            'title' => "Abilities",
                            'span' => 3,
                            'content' => $abilities,
                            'required' => true,
                            'multiple' => true,
                        ],
                        // [
                        //     'type' => "switch",
                        //     'name' => "active",
                        //     'title' => "Active",
                        // ],
                        // [
                        //     'type' => "switch",
                        //     'name' => "full_access",
                        //     'title' => "Full Access",
                        // ],
                        // [
                        //     'type' => "switch",
                        //     'name' => "manage_nested",
                        //     'title' => "Manage nested",
                        // ],
                        // [
                        //     'type' => "switch",
                        //     'name' => "remove_on_change_unit",
                        //     'title' => "Remove on transfer",
                        // ],
                        // [
                        //     'type' => "switch",
                        //     'name' => "temporary",
                        //     'title' => "Temporary",
                        // ],
                        // [
                        //     'type' => "date",
                        //     'name' => "expires",
                        //     'title' => "Expires",
                        // ],
                    ],
                ],
            ],
            [
                'title' => "Authorizations management",
                'subtitle' => "Define which users will have access to this authorization",
                'condition' => $role->id <> null,
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "table",
                            'name' => "users",
                            'title' => "Authorized users",
                            'span' => 2,
                            'content' => [
                                'softDelete' => Role::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
                                'routes' => [
                                    'destroyRoute' => "apps.roles.destroy",
                                ],
                                'menu' => [
                                    [
                                        'icon' => "mdi:plus",
                                        'title' => "Role creation",
                                        'route' => "apps.roles.create",
                                        'modal' => true,
                                    ],
                                ],
                                'filters' => $request->all('search', 'sorted', 'trashed'),
                                'titles' => [
                                    [
                                        'type' => 'composite',
                                        'title' => 'Role',
                                        'field' => 'name',
                                        'fields' => ['name', 'email'],
                                    ],
                                ],
                                'items' => $items
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create(Request $request, Role $role): Response
    {
        return Inertia::render('Default/Create', [
            'form' => $this->__form($request, $role),
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
        DB::beginTransaction();

        try {
            $role = Role::firstOrCreate([
                'name' => $request->name,
                'description' => $request->description,
            ]);
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when inserting a new role.");
        }
        
        try {
            $role->abilities()->sync($request->abilities);
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when syncing abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.roles.edit', $role->id)->with('status', 'Role created.');
    }
    
    public function edit(Request $request, Role $role): Response
    {
        $role['abilities'] = $role->abilities()->get()->map->only('id')->pluck('id');
        // $role['abilities'] = $role->abilities()->get()->map->only('id', 'name');

        return Inertia::render('Default/Edit', [
            'form' => $this->__form($request, $role),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $role
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        DB::beginTransaction();

        try {
            Role::where('id', $role->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when editing the role.");
        }
        
        try {
            $role->abilities()->sync($request->abilities);
        } catch(\Exception $e) {
            DB::rollback();

            return Redirect::back()->with('status', "Error when syncing abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.roles.index')->with('status', 'Role edited.');
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

    public function restore(Role $role): RedirectResponse
    {
        $role->restore();

        return Redirect::back()->with('status', 'Role restored.');
    }
}
