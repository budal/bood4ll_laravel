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
use Emargareten\InertiaModal\Modal;

use App\Models\User;
use App\Models\Role;
use App\Models\Ability;

use App\Http\Requests\RolesRequest;

class RolesController extends Controller
{
    public function index(Request $request): Response
    {
        $items = Role::filter($request->all('search', 'sorted', 'trashed'))
            ->sort($request->sorted ?? "name")
            ->withCount('abilities', 'users')
            ->paginate(20)
            ->onEachSide(2)
            ->appends($request->all('search', 'sorted', 'trashed'));
        
        return Inertia::render('Default/Index', [
            'title' => "Roles management",
            'subtitle' => "Define roles, grouping abilities to define specific access.",
            'routes' => [
                'createRoute' => "apps.roles.create",
                'editRoute' => "apps.roles.edit",
                'destroyRoute' => "apps.roles.destroy",
                'restoreRoute' => "apps.roles.restore",
            ],
            'menu' => [
                [
                    'icon' => "mdi:book-cog-outline",
                    'title' => "Abilities management",
                    'route' => "apps.abilities.index"
                ],            
            ],
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
                    'title' => 'Staff',
                    'field' => 'users_count',
                ],
            ],
            'items' => $items
        ]);
    }
    
    public function __form(Request $request, Role $role): Array
    {
        $abilities = Ability::sort("name")->get();

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
                        [
                            'type' => "toggle",
                            'name' => "active",
                            'title' => "Active",
                            'color' => "success",
                            'colorFalse' => "danger",
                        ],
                        [
                            'type' => "toggle",
                            'name' => "temporary",
                            'title' => "Temporary",
                            'color' => "info",
                        ],
                        [
                            'type' => "date",
                            'name' => "expires",
                            'title' => "Expires in",
                        ],
                        [
                            'type' => "toggle",
                            'name' => "full_access",
                            'title' => "Full access",
                            'color' => "info",
                        ],
                        [
                            'type' => "toggle",
                            'name' => "manage_nested",
                            'title' => "Manage nested data",
                            'color' => "info",
                        ],
                        [
                            'type' => "toggle",
                            'name' => "remove_on_change_unit",
                            'title' => "Remove on transfer",
                            'color' => "info",
                        ],
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
                            'span' => 2,
                            'content' => [
                                'routes' => [
                                    'createRoute' => [
                                        'route' => "apps.roles.edit.adduser",
                                        'attributes' => $role->id,
                                        'preserveScroll' => true
                                    ],

                                    'destroyRoute' => "apps.roles.destroy",
                                ],
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
        return Inertia::render('Default/Form', [
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
                'active' => $request->active,
                'temporary' => $request->temporary,
                'expires' => $request->expires,
                'full_access' => $request->full_access,
                'manage_nested' => $request->manage_nested,
                'remove_on_change_unit' => $request->remove_on_change_unit,
            ]);
        } catch(Throwable $e) {
            DB::rollback();

            report($e);
            return Redirect::back()->with('status', "Error when inserting a new role.");
        }
        
        try {
            $role->abilities()->sync($request->abilities);
        } catch(\Exception $e) {
            DB::rollback();
            dd($e);

            return Redirect::back()->with('status', "Error when syncing abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.roles.edit', $role->id)->with('status', 'Role created.');
    }
    
    public function edit(Request $request, Role $role): Response
    {
        $role['abilities'] = $role->abilities;

        return Inertia::render('Default/Form', [
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
        if ($request->temporary && !$request->expires) {
            return Redirect::back()->with('status', "Define the expiration date.");
        } elseif (!$request->temporary) {
            $request->expires = null;
        }
        
        DB::beginTransaction();

        try {
            Role::where('id', $role->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'active' => $request->active,
                    'temporary' => $request->temporary,
                    'expires' => $request->expires,
                    'full_access' => $request->full_access,
                    'manage_nested' => $request->manage_nested,
                    'remove_on_change_unit' => $request->remove_on_change_unit,
                ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return Redirect::back()->with('status', "Error when editing the role.");
        }
        
        try {
            $role->abilities()->sync($request->abilities);
        } catch (\Exception $e) {
            DB::rollback();

            return Redirect::back()->with('status', "Error when syncing abilities to the role.");
        }

        DB::commit();

        return Redirect::back()->with('status', 'Role edited.');
    }

    public function __formModal(Request $request, Role $role): Array
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
                'fields' => [
                    [
                        [
                            'type' => "select",
                            'name' => "abilities",
                            'title' => "Abilities",
                            'span' => 3,
                            'content' => $abilities,
                            'required' => true,
                            'multiple' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function adduser(Request $request, Role $role): Modal
    {
        $role['abilities'] = $role->abilities()->get()->map->only('id')->pluck('id');

        return Inertia::modal('Default/Form', [
            'form' => $this->__formModal($request, $role),
            'isModal' => true,
            'title' => "Define the users who have access to this authorization",
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $role
        ])
        ->baseRoute('apps.roles.edit', $role)
        // ->refreshBackdrop()
        ;
    }
    
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $total = Role::whereIn('id', $request->list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->delete();

            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to remove.|[1] A role was removed.|[2,*] :total roles were removed.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
     
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on remove selected items.",
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        try {
            $total = Role::whereIn('id', $request->list)->restore();

            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to restore.|[1] A role was restored.|[2,*] :total roles were restored.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
     
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on restore selected items.",
            ]);
        }
    }
}
