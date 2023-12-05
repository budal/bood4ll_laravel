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
                'forceDestroyRoute' => "apps.roles.forcedestroy",
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
                    'title' => 'Users',
                    'field' => 'users_count',
                ],
            ],
            'items' => $items
        ]);
    }
    
    public function __form(Request $request, Role $role): Array
    {
        $abilities = Ability::sort("name")->get();

        if ($request->all) {
            $roles = User::filter($request->all('search', 'sorted', 'trashed'))
                ->with("roles")
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
                ->through(function($item) use ($role) {
                    // $item->id = 2;
                    $item->checked = in_array($role->id, $item->roles->pluck("id")->toArray());
                    return $item;
                });
        } else {
            $roles = $role->users()
                ->filter($request->all('search', 'sorted', 'trashed'))
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
                ->through(function($item){
                    // $item->id = $item->pivot->role_id;
                    $item->checked = true;
                    return $item;
                });
        }

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
                            'colorOn' => "success",
                            'colorOff' => "danger",
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
                'fields' => [
                    [
                        [
                            'type' => "table",
                            'name' => "users",
                            'content' => [
                                'routes' => [
                                    'showCheckboxes' => true,
                                ],
                                'menu' => [
                                    [
                                        'icon' => "mdi:plus-circle-outline",
                                        'title' => "Authorize",
                                        'route' => [
                                            'route' => "apps.roles.authorization",
                                            'attributes' => [
                                                $role->id,
                                                "on",
                                            ]
                                        ],
                                        'method' => "post",
                                        'list' => 'checkboxes',
                                        'listCondition' => false,
                                        'modalTitle' => "Are you sure you want to authorize the selected users?|Are you sure you want to authorize the selected users?",
                                        'modalSubTitle' => "The selected user will have the rights to access this role. Do you want to continue?|The selected user will have the rights to access this role. Do you want to continue?",
                                        'buttonTitle' => "Authorize",
                                        'buttonIcon' => "mdi:plus-circle-outline",
                                        'buttonColor' => "success",
                                    ],
                                    [
                                        'icon' => "mdi:minus-circle-outline",
                                        'title' => "Deauthorize",
                                        'route' => [
                                            'route' => "apps.roles.authorization",
                                            'attributes' => [
                                                $role->id,
                                                "off",
                                            ]
                                        ],
                                        'method' => "post",
                                        'list' => 'checkboxes',
                                        'listCondition' => true,
                                        'modalTitle' => "Are you sure you want to deauthorize the selected users?|Are you sure you want to deauthorize the selected users?",
                                        'modalSubTitle' => "The selected user will lose the rights to access this role. Do you want to continue?|The selected users will lose the rights to access this role. Do you want to continue?",
                                        'buttonTitle' => "Deauthorize",
                                        'buttonIcon' => "mdi:minus-circle-outline",
                                        'buttonColor' => "danger",
                                
                                    ],
                                    [
                                        'title' => "-",
                                    ],
                    
                                    [
                                        'icon' => "mdi:format-list-checkbox",
                                        'title' => "List",
                                        'items' => [
                                            [
                                                'icon' => "mdi:account-key-outline",
                                                'title' => "Authorized users",
                                                'route' => [
                                                    'route' => "apps.roles.edit",
                                                    'attributes' => $role->id
                                                ],
                                            ],
                                            [
                                                'icon' => "mdi:account-multiple-outline",
                                                'title' => "All users",
                                                'route' => [
                                                    'route' => "apps.roles.edit",
                                                    'attributes' => [$role->id, 'all']
                                                ],
                                            ]

                                        ],
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'composite',
                                        'title' => 'Name',
                                        'field' => 'name',
                                        'fields' => ['name', 'email'],
                                    ],
                                    [
                                        'type' => 'toggle',
                                        'title' => 'Active',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => "apps.roles.authorization",
                                            'attributes' => [
                                                $role->id,
                                                "toggle",
                                            ],
                                        ],
                                        'method' => 'post',
                                        'color' => 'info',
                                    ],
                                ],
                                'items' => $roles
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
            report($e);
            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on add this item.",
            ]);
        }
        
        try {
            $role->abilities()->sync($request->abilities);
        } catch(\Exception $e) {
            report($e);
            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => "error",
                'toast_message' => "Error when syncing abilities to the role.",
            ]);
        }

        DB::commit();

        return Redirect::route('apps.roles.edit', $role->id)->with([
            'toast_type' => "success",
            'toast_message' => "{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.",
            'toast_count' => 1,
        ]);
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
            report($e);
            DB::rollback();
            
            return Redirect::back()->with('status', "Error when editing the role.");
        }
        
        try {
            $role->abilities()->sync($request->abilities);
        } catch (\Exception $e) {
            report($e);
            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => "error",
                'toast_message' => "Error when syncing abilities to the role.",
            ]);
        }

        DB::commit();

        return Redirect::back()->with([
            'toast_type' => "success",
            'toast_message' => "{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.",
            'toast_count' => 1,
        ]);
    }

    public function authorization(Request $request, Role $role, $mode): RedirectResponse
    {
        $hasRole = $role->users()->whereIn('user_id', $request->list)->first();

        try {
            if ($mode == "toggle") {
                $user = User::whereIn('id', $request->list)->first();
                $hasRole ? $role->users()->detach($request->list) : $role->users()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $hasRole 
                        ? "The user ':user' has been disabled in the ':role' role."
                        : "The user ':user' was enabled in the ':role' role." ,
                    'toast_replacements' => ['user' => $user->name, 'role' => $role->name]
                ]);
            } elseif ($mode == "on") {
                $total = $role->users()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => "{0} Nobody to authorize.|[1] User successfully authorized.|[2,*] :total users successfully authorized.",
                    'toast_count' => count($request->list),
                    'toast_replacements' => ['total' => count($request->list)]
                ]);
            } elseif ($mode == "off") {
                $total = $role->users()->detach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => "{0} Nobody to deauthorize.|[1] User successfully deauthorized.|[2,*] :total users successfully deauthorized.",
                    'toast_count' => $total,
                    'toast_replacements' => ['total' => $total]
                ]);
            }
        } catch (Throwable $e) {
            return Redirect::back()->with('status', "Error on edit selected item.|Error on edit selected items.");
        }
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
                'toast_message' => "{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
     
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on remove selected item.|Error on remove selected items.",
                'toast_count' => ($request->list),
            ]);
        }
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        try {
            $total = Role::whereIn('id', $request->list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->forceDelete();

            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
     
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on erase selected item.|Error on erase selected items.",
                'toast_count' => ($request->list),
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        try {
            $total = Role::whereIn('id', $request->list)->restore();

            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
     
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on restore selected item.|Error on restore selected items.",
                'toast_count' => ($request->list),
            ]);
        }
    }

    public function __formModal(Request $request, Role $role): Array
    {
        $abilities = Ability::sort("name")->get()->map->only(['id', 'name']);

        $users = User::filter($request->all('users_search', 'users_sorted', 'users_trashed'))
            ->sort($request->sorted ?? "name")
            ->paginate(5)
            ->onEachSide(1)
            ->appends($request->all('users_search', 'users_sorted', 'users_trashed'));

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
                'id' => "users",
                'fields' => [
                    [
                        [
                            'type' => "table",
                            'name' => "users",
                            'span' => 2,
                            'shortcutKey' => "a",
                            'content' => [
                                'menu' => [
                                    [
                                        'icon' => "mdi:book-cog-outline",
                                        'title' => "Abilities management",
                                        'route' => "apps.abilities.index"
                                    ],            
                                ],
                                'titles' => [
                                    [
                                        'type' => 'simple',
                                        'title' => 'Name',
                                        'field' => 'name',
                                    ],
                                ],
                                'items' => $users
                            ],
                        ],
                    ],
                ]
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
}
