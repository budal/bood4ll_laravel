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
use App\Models\AbilityRole;
use App\Models\RoleUser;


use App\Models\Unit;


use App\Http\Requests\RolesRequest;

class RolesController extends Controller
{
    public function index(Request $request): Response
    {
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
                    'field' => 'abilities',
                ],
                [
                    'type' => 'simple',
                    'title' => 'Users',
                    'field' => 'users',
                ],
            ],
            'items' => Role::filter($request->all('search', 'sorted', 'trashed'))
                ->addSelect([
                    'abilities' => AbilityRole::selectRaw('COUNT(*)')
                        ->whereColumn('role_id', 'roles.id')
                        ->take(1),
                    'users' => RoleUser::selectRaw('COUNT(*)')
                        ->whereColumn('role_id', 'roles.id')
                        ->take(1),
                ])
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
        ]);
    }
    
    public function __form(Request $request, Role $role): Array
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
                            'type' => "input",
                            'name' => "description",
                            'title' => "Description",
                            'required' => true,
                        ],
                        [
                            'type' => "select",
                            'name' => "abilities",
                            'title' => "Abilities",
                            'span' => 2,
                            'content' => $abilities,
                            'required' => true,
                            'multiple' => true,
                        ],
                    ],
                ],
            ],
            [
                'title' => "Authorization management",
                'subtitle' => "Define which users will have access to this permission",
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
                                'softDelete' => RoleUser::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
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
                                        'fields' => ['name', 'email'],
                                    ],
                                ],
                                'items' => RoleUser::filter($request->all('search', 'sorted', 'trashed'))
                                    ->sort($request->sorted ?? "name")
                                    ->where('role_id', $role->id)
                                    ->select('role_user.id', 'users.name', 'users.email')
                                    ->join('users', 'users.id', '=', 'role_user.user_id')
                                    ->paginate(20)
                                    ->onEachSide(2)
                                    ->appends($request->all('search', 'sorted', 'trashed'))
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
            foreach($request->abilities as $ability_id) {
                $abilities_role = AbilityRole::create([
                    'role_id' => $role->id,
                    'ability_id' => $ability_id,
                ]);
            }
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when linking abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.roles.edit', $role->id)->with('status', 'Role created.');
    }
    
    public function edit(Request $request, Role $role): Response
    {
        $role['abilities'] = $role->listAbilities()
            ->get()
            ->map
            ->only('ability_id')
            ->pluck('ability_id');
        
        return Inertia::render('Default/Edit', [
            'form' => $this->__form($request, $role),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch'
                ],
            ],
            'tablesRoute' => [
                'role' => route('apps.roles.edit.users', $role->id),
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
            $abilities_role_saved = AbilityRole::where('role_id', $role->id)
                ->get()
                ->map
                ->only('id', 'ability_id')
                ->pluck('ability_id', 'id');

            $abilities_role_to_delete = AbilityRole::where('role_id', $role->id)
                ->whereNotIn('ability_id', $request->abilities)
                ->get()
                ->map
                ->only('id', 'ability_id')
                ->pluck('ability_id', 'id')
                ->toArray();

            $abilities_role_mainteined = $abilities_role_saved->diff($abilities_role_to_delete);

            $abilities_role_deleted = AbilityRole::where('role_id', $role->id)
                ->whereIn('ability_id', $abilities_role_to_delete)
                ->delete();
                
            $abilities_role_to_insert = collect($request->abilities)->diff($abilities_role_mainteined);

            foreach($abilities_role_to_insert as $ability_id) {
                $ability_role = AbilityRole::create([
                    'role_id' => $role->id,
                    'ability_id' => $ability_id,
                ]);
            }
        } catch(\Exception $e) {
            DB::rollback();
            dd($e);
            return Redirect::back()->with('status', "Error when linking abilities to the role.");
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
