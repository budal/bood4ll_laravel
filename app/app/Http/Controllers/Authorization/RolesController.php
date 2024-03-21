<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    public function getAbilities(Request $request): JsonResponse
    {
        $abilities = Ability::select('abilities.*')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->whereIn('name', $request->user()->getAllAbilities->whereNotNull('ability')->pluck('ability'));
            })
            ->orderBy('name')
            ->get();

        return response()->json($abilities);
    }

    public function getAbilitiesIndex(Request $request): JsonResponse
    {
        $this->authorize('isSuperAdmin', User::class);

        $prefixes = ['apps', 'reports'];

        $routes = collect(Route::getRoutes())->filter(function ($route) use ($request, $prefixes) {
            return Str::contains($route->uri, $prefixes)
                && ($request->search ? Str::contains($route->action['as'], $request->search) : true);
        });

        $abilitiesInDB = Ability::where('name', 'ilike', "%$request->search%")->get()->pluck('name');

        $validAbilities = $routes->map(function ($route) use ($abilitiesInDB) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $route = $route->uri;
            $command = end($actionSegments);
            $title = $id;
            $checked = $abilitiesInDB->contains($id);
            $deleteOnly = false;

            return compact('id', 'route', 'command', 'title', 'checked', 'deleteOnly');
        })->values();

        $invalidAbilities = $abilitiesInDB->diff(collect($validAbilities)->pluck('id'))->map(function ($zombie) {
            $id = $zombie;
            $route = $zombie;
            $command = "< delete only >";
            $title = $zombie;
            $checked = true;
            $deleteOnly = true;

            // if (Route::has($zombie))
            return compact('id', 'route', 'command', 'title', 'checked', 'deleteOnly');
        })->values();

        $abilities = [...$validAbilities, ...$invalidAbilities];

        usort($abilities, function ($a, $b) use ($request) {
            return $a['title'] <=> $b['title'];
        });

        return response()->json([
            'data' => $abilities,
            "next_page_url" => null
        ]);
    }

    public function putAbilitiesUpdate(Request $request, $mode): JsonResponse
    {
        $this->authorize('isSuperAdmin', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            if ($mode == 'toggle') {
                $name = $list[0];

                if ($ability = Ability::where('name', $name)->first()) {
                    if ($ability->delete()) {
                        $action = 'delete';
                    }
                } else {
                    if ($ability = Ability::updateOrCreate(['name' => $name])) {
                        $action = 'insert';
                    }
                }

                return response()->json([
                    'type' => 'success',
                    'deactivate' => $action == 'delete',
                    'title' => $action == 'delete' ? 'Deactivation' : 'Activation',
                    'message' => $action == 'delete'
                        ? "The ability ':ability' was deactivated."
                        : "The ability ':ability' was activated.",
                    'length' => 1,
                    'replacements' => ['ability' => $ability->name],
                ]);
            } elseif ($mode == 'on') {
                $total = 0;

                foreach ($list as $name) {
                    if (Ability::updateOrCreate(['name' => $name])) {
                        ++$total;
                    }
                }

                return response()->json([
                    'type' => 'success',
                    'title' => 'Activation',
                    'message' => '{0} Nothing to activate.|[1] Item activated successfully.|[2,*] :total items successfully activated.',
                    'length' => $total,
                    'replacements' => ['total' => $total],
                ]);
            } elseif ($mode == 'off') {
                $total = Ability::whereIn('name', $list)->delete();

                return response()->json([
                    'type' => 'success',
                    'title' => 'Deactivation',
                    'message' => '{0} Nothing to deactivate.|[1] Item deactivated successfully.|[2,*] :total items successfully deactivated.',
                    'length' => $total,
                    'replacements' => ['total' => $total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on edit selected item.|Error on edit selected items.',
                'length' => count($list),
            ]);
        }
    }

    public function getRolesIndex(Request $request): JsonResponse
    {
        // $this->authorize('access', User::class);

        $roles = Role::leftjoin('role_user', 'role_user.role_id', '=', 'roles.id')
            ->select('roles.id', 'roles.name', 'roles.description', 'roles.deleted_at')
            ->groupBy('roles.id', 'roles.name', 'roles.description', 'roles.deleted_at')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->where('roles.superadmin', false);
                $query->where('roles.manager', false);
                $query->where('roles.active', true);
                $query->where(function ($query) {
                    $query->where('roles.lock_on_expire', false);
                    $query->orWhere(function ($query) {
                        $query->where('roles.lock_on_expire', true);
                        $query->where('roles.expires_at', '>=', 'NOW()');
                    });
                });
                $query->where('role_user.user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->when($request->listItems ?? null, function ($query, $listItems) {
                if ($listItems == 'both') {
                    $query->withTrashed();
                } elseif ($listItems == 'trashed') {
                    $query->onlyTrashed();
                }
            })
            ->where('name', 'ilike', "%$request->search%")
            ->withCount([
                'abilities',
                'users' => function ($query) use ($request) {
                    $query->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                        $query->join('unit_user', function (JoinClause $join) use ($request, $query) {
                            $join->on('unit_user.user_id', '=', 'role_user.user_id')
                                ->where('unit_user.primary', true);

                            if ($request->user()->cannot('hasFullAccess', User::class)) {
                                $query->where('unit_user.user_id', $request->user()->id);
                            }

                            $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
                        });
                    });
                }
            ])
            ->cursorPaginate(30)
            ->withQueryString();

        return response()->json($roles);
    }

    public function getRoleAuthorizedUsers(Request $request, Role $role): JsonResponse
    {
        $users = User::leftjoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            ->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->with('unitsClassified', 'unitsWorking')
            ->where('name', 'ilike', "%$request->search%")
            ->orderBy("name")
            ->when(
                $request->show == 'all',
                function () {
                },
                function ($query) use ($role) {
                    $query->where('role_user.role_id', $role->id);
                }
            )
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }
                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->cursorPaginate(30)
            ->withQueryString()
            ->through(function ($item) use ($role) {
                $item->checked = $item->roles->pluck('id')->contains($role->id);

                return $item;
            });

        return response()->json($users);
    }

    public function getRoleInfo(Request $request, Role $role): JsonResponse
    {
        // $this->authorize('access', User::class);
        // $this->authorize('isActive', $role);
        // $this->authorize('canEdit', $role);
        // $this->authorize('canEditManagementRoles', $role);

        $role['abilities'] = $role->abilities;

        return response()->json($role);
    }

    public function index(Request $request): Response
    {
        // $this->authorize('access', User::class);

        return Inertia::render('Bood4ll', [
            'build' => [
                [
                    'label' => Route::current()->title,
                    'description' => Route::current()->description,
                    'fields' => [
                        [
                            'type' => 'table',
                            'structure' => [
                                'menu' => [
                                    [
                                        'icon' => 'account_tree',
                                        'label' => 'Abilities management',
                                        'source' => 'getAbilitiesIndex',
                                        'dialog' => true,
                                        'visible' => $request->user()->can('isSuperAdmin', User::class),
                                        'components' => [
                                            [
                                                'label' => 'Abilities',
                                                'description' => 'Define which abilities will be showed in the roles management.',
                                                'visible' => (
                                                    Gate::allows('apps.roles.update')
                                                    && $request->user()->can('isManager', User::class)
                                                    && $request->user()->can('canManageNestedData', User::class)
                                                ),
                                                'fields' => [
                                                    [
                                                        'type' => 'table',
                                                        'name' => 'users',
                                                        'structure' => [
                                                            'actions' => [
                                                                'index' => [
                                                                    'source' => 'getAbilitiesIndex',
                                                                    'selectBoxes' => true,
                                                                    'visible' => true,
                                                                    'disabled' => true,
                                                                ],
                                                            ],
                                                            'menu' => [
                                                                [
                                                                    'icon' => 'check',
                                                                    'label' => 'Authorize',
                                                                    'callback' => [
                                                                        'route' => 'putAbilitiesUpdate',
                                                                        'attributes' => ['mode' => 'on']
                                                                    ],
                                                                    'method' => 'put',
                                                                    'visible' => $request->user()->can('canManageNestedData', User::class),
                                                                    'condition' => ['checked' => false],
                                                                    'badgeClass' => 'success',
                                                                    'dialogTitle' => 'Are you sure you want to authorize the selected users?|Are you sure you want to authorize the selected users?',
                                                                    'dialogSubTitle' => 'The selected user will have the rights to access this role. Do you want to continue?|The selected user will have the rights to access this role. Do you want to continue?',


                                                                ],
                                                                [
                                                                    'icon' => 'close',
                                                                    'label' => 'Deauthorize',
                                                                    'callback' => [
                                                                        'route' => 'putAbilitiesUpdate',
                                                                        'attributes' => ['mode' => 'off']
                                                                    ],
                                                                    'method' => 'put',
                                                                    'visible' => $request->user()->can('canManageNestedData', User::class),
                                                                    'condition' => ['checked' => true],
                                                                    'badgeClass' => 'danger',
                                                                    'dialogTitle' => 'Are you sure you want to deauthorize the selected users?|Are you sure you want to deauthorize the selected users?',
                                                                    'dialogSubTitle' => 'The selected user will lose the rights to access this role. Do you want to continue?|The selected users will lose the rights to access this role. Do you want to continue?',
                                                                ],
                                                            ],
                                                            'titles' => [
                                                                [
                                                                    'type' => 'composite',
                                                                    'header' => 'Ability',
                                                                    'field' => 'title',
                                                                    'values' => [
                                                                        [
                                                                            'field' => 'title',
                                                                        ],
                                                                        [
                                                                            'field' => 'command',
                                                                            'class' => 'text-xs',
                                                                        ],
                                                                    ],
                                                                ],
                                                                [
                                                                    'type' => 'toggle',
                                                                    'header' => 'Active',
                                                                    'field' => 'checked',
                                                                    'disableSort' => true,
                                                                    'callback' => [
                                                                        'route' => 'putAbilitiesUpdate',
                                                                        'attributes' => ['mode' => 'toggle']
                                                                    ],
                                                                    'method' => 'put',
                                                                    'colorOn' => 'success',
                                                                    'colorOff' => 'danger',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'actions' => [
                                    'index' => [
                                        'source' => 'getRolesIndex',
                                        'visible' => Gate::allows('apps.roles.index'),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                    ],
                                    'create' => [
                                        'visible' => (
                                            Gate::allows('apps.roles.store')
                                            && $request->user()->can('isManager', User::class)
                                        ),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                        'components' => [
                                            [
                                                'label' => 'Main data',
                                                'description' => 'Role name, abilities and settings.',
                                                'cols' => 3,
                                                'fields' => $this->__fields($request),
                                                'visible' => (
                                                    Gate::allows('apps.roles.store')
                                                    && $request->user()->can('isManager', User::class)
                                                ),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Add',
                                                'toast' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.roles.store',
                                                'method' => 'post',
                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'visible' => (
                                            Gate::allows('apps.roles.update')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                        'components' => [
                                            [
                                                'source' => [
                                                    'route' => 'getRoleInfo',
                                                    'transmute' => ['role' => 'id'],
                                                ],
                                                'label' => 'Main data',
                                                'description' => 'Role name, abilities and settings.',
                                                'cols' => 3,
                                                'fields' => $this->__fields($request),
                                                'visible' => (
                                                    Gate::allows('apps.roles.update')
                                                    && $request->user()->can('isManager', User::class)
                                                    && $request->user()->can('canManageNestedData', User::class)
                                                ),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Edit',
                                                'toast' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.roles.update',
                                                'method' => 'patch',
                                            ],
                                            [
                                                'label' => 'Authorized users',
                                                'description' => 'Define which users will have access to this authorization.',
                                                'visible' => (
                                                    Gate::allows('apps.roles.update')
                                                    && $request->user()->can('isManager', User::class)
                                                    && $request->user()->can('canManageNestedData', User::class)
                                                ),

                                                // 'showIf' => $role->id === null || $request->user()->can('isOwner', $role),
                                                // 'disabledIf' => $role->inalterable == true || $role->id !== null && $request->user()->cannot('isOwner', $role),

                                                'fields' => [
                                                    [
                                                        'type' => 'table',
                                                        'name' => 'users',
                                                        'structure' => [
                                                            'actions' => [
                                                                'index' => [
                                                                    'source' => [
                                                                        'route' => 'getRoleAuthorizedUsers',
                                                                        'transmute' => ['role' => 'id'],
                                                                    ],
                                                                    'selectBoxes' => true,
                                                                    'visible' => true,
                                                                    'disabled' => true,
                                                                ],
                                                            ],
                                                            'menu' => [
                                                                [
                                                                    'icon' => 'check',
                                                                    'label' => 'Authorize',
                                                                    'callback' => [
                                                                        'route' => 'apps.roles.authorize',
                                                                        'attributes' => ['mode' => 'on'],
                                                                        'transmute' => ['role' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'visible' => $request->user()->can('canManageNestedData', User::class),
                                                                    'condition' => ['checked' => false],
                                                                    'badgeClass' => 'success',
                                                                ],
                                                                [
                                                                    'icon' => 'close',
                                                                    'label' => 'Deauthorize',
                                                                    'callback' => [
                                                                        'route' => 'apps.roles.authorize',
                                                                        'attributes' => ['mode' => 'off'],
                                                                        'transmute' => ['role' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'visible' => $request->user()->can('canManageNestedData', User::class),
                                                                    'condition' => ['checked' => true],
                                                                    'badgeClass' => 'danger',
                                                                ],
                                                                [
                                                                    'separator' => true,
                                                                ],
                                                                [
                                                                    'icon' => 'verified_user',
                                                                    'label' => 'Authorized users',
                                                                    'source' => [
                                                                        'route' => 'getRoleAuthorizedUsers',
                                                                        'transmute' => ['role' => 'id'],
                                                                    ],
                                                                    'visible' => $request->user()->can('canManageNestedData', User::class),
                                                                ],
                                                                [
                                                                    'icon' => 'group',
                                                                    'label' => 'All users',
                                                                    'source' => [
                                                                        'route' => 'getRoleAuthorizedUsers',
                                                                        'attributes' => ['show' => 'all'],
                                                                        'transmute' => ['role' => 'id'],
                                                                    ],
                                                                    'visible' => $request->user()->can('canManageNestedData', User::class)
                                                                ],
                                                            ],
                                                            'titles' => [
                                                                [
                                                                    'type' => 'avatar',
                                                                    'header' => 'Avatar',
                                                                    'field' => 'id',
                                                                    'fallback' => 'name',
                                                                ],
                                                                [
                                                                    'type' => 'text',
                                                                    'header' => 'User',
                                                                    'field' => 'name',
                                                                ],
                                                                [
                                                                    'type' => 'composite',
                                                                    'header' => 'Classified',
                                                                    'class' => 'collapse',
                                                                    'field' => 'units_classified',
                                                                    'options' => [
                                                                        [
                                                                            'field' => 'name',
                                                                        ],
                                                                    ],
                                                                ],
                                                                [
                                                                    'type' => 'composite',
                                                                    'header' => 'Working',
                                                                    'class' => 'collapse',
                                                                    'field' => 'units_working',
                                                                    'options' => [
                                                                        [
                                                                            'field' => 'name',
                                                                        ],
                                                                    ],
                                                                ],
                                                                [
                                                                    'type' => 'toggle',
                                                                    'header' => 'Active',
                                                                    'field' => 'checked',
                                                                    'disableSort' => true,
                                                                    'callback' => [
                                                                        'route' => 'apps.roles.authorize',
                                                                        'attributes' => ['mode' => 'toggle'],
                                                                        'transmute' => ['role' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'colorOn' => 'success',
                                                                    'colorOff' => 'danger',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'destroy' => [
                                        'callback' => 'apps.roles.destroy',
                                        'method' => 'delete',
                                        'visible' => (
                                            Gate::allows('apps.roles.destroy')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                    'restore' => [
                                        'callback' => 'apps.roles.restore',
                                        'method' => 'post',
                                        'visible' => (
                                            Gate::allows('apps.roles.restore')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                    'forceDestroy' => [
                                        'callback' => 'apps.roles.forceDestroy',
                                        'method' => 'delete',
                                        'visible' => (
                                            Gate::allows('apps.roles.forceDestroy')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'composite',
                                        'header' => 'Name',
                                        'field' => 'name',
                                        'values' => [
                                            [
                                                'field' => 'name',
                                            ],
                                            [
                                                'field' => 'description',
                                                'class' => 'text-xs',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'Abilities',
                                        'field' => 'abilities_count',
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'Users',
                                        'field' => 'users_count',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ]);
    }

    public function __fields(Request $request): array
    {
        return [
            [
                'type' => 'input',
                'name' => 'name',
                'label' => 'Name',
                'required' => true,
                'autofocus' => true,
            ],
            [
                'type' => 'input',
                'name' => 'description',
                'label' => 'Description',
                'span' => 2,
            ],
            [
                'type' => 'dropdown',
                'name' => 'abilities',
                'label' => 'Abilities',
                'source' => "getAbilities",
                'multiple' => true,
                'span' => 3,
                'required' => true,
            ],
            [
                'type' => 'toggle',
                'name' => 'active',
                'label' => 'Active',
                'colorOn' => 'success',
                'colorOff' => 'danger',
            ],
            [
                'type' => 'toggle',
                'name' => 'lock_on_expire',
                'label' => 'Lock on expire',
                'colorOn' => 'info',
            ],
            [
                'type' => 'calendar',
                'name' => 'expires_at',
                'label' => 'Expires at',
            ],
            [
                'type' => 'toggle',
                'name' => 'full_access',
                'label' => 'Full access',
                'disabled' => $request->user()->cannot('hasFullAccess', User::class),
                'colorOn' => 'info',
            ],
            [
                'type' => 'toggle',
                'name' => 'manage_nested',
                'label' => 'Nested data',
                'disabled' => $request->user()->cannot('canManageNestedData', User::class),
                'colorOn' => 'info',
            ],
            [
                'type' => 'toggle',
                'name' => 'remove_on_change_unit',
                'label' => 'Remove on transfer',
                'disabled' => $request->user()->can('canRemoveOnChangeUnit', User::class) && $request->user()->cannot('isSuperAdmin', User::class),
                'colorOn' => 'info',
            ],
        ];
    }

    public function putAuthorizeUserInRole(Request $request, Role $role, $mode): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', [$role, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            if ($mode == 'toggle') {
                $user = User::whereIn('id', $list)->first();
                $hasRole = $role->users()->whereIn('user_id', $list)->first();
                $hasRole ? $role->users()->detach($list) : $role->users()->attach($list);

                return response()->json([
                    'type' => 'success',
                    'deactivate' => $hasRole == true ? true : false,
                    'title' => $hasRole ? 'Deauthorize' : 'Authorize',
                    'message' => $hasRole
                        ? "The user ':user' has been disabled in the ':role' role."
                        : "The user ':user' was enabled in the ':role' role.",
                    'length' => 1,
                    'replacements' => ['user' => $user->name, 'role' => $role->name],
                ]);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $hasRole
                ]);
            } elseif ($mode == 'on') {
                $role->users()->attach($list);

                return response()->json([
                    'type' => 'success',
                    'title' => 'Authorize',
                    'message' => '{0} Nothing to authorize.|[1] Item authorized successfully.|[2,*] :total items successfully authorized.',
                    'length' => count($list),
                    'replacements' => ['total' => count($list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $role->users()->detach($list);

                return response()->json([
                    'type' => 'success',
                    'title' => 'Deauthorize',
                    'message' => '{0} Nothing to deauthorize.|[1] Item deauthorized successfully.|[2,*] :total items successfully deauthorized.',
                    'length' => $total,
                    'replacements' => ['total' => $total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on edit selected item.|Error on edit selected items.',
                'length' => count($request->list),
            ]);
        }
    }

    public function postStoreRole(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);

        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique(Role::class)],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
            'lock_on_expire' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'required_if:lock_on_expire,true'],
            'full_access' => ['boolean', 'accepted_if:manage_nested,true'],
            'manage_nested' => ['boolean'],
            'remove_on_change_unit' => ['boolean'],
        ], $messages = [
            // 'required' => 'The :attribute field is required.',
        ], $attributes = [
            // 'email' => 'email address',
        ],);

        DB::beginTransaction();

        try {
            $role = new Role();

            $role->name = $request->name;
            $role->description = $request->description;
            $role->owner = $request->user()->id;
            $role->active = $request->active;
            $role->lock_on_expire = $request->lock_on_expire;
            $role->expires_at = $request->expires_at;
            $role->full_access = $request->full_access;
            $role->manage_nested = $request->manage_nested;
            $role->remove_on_change_unit = $request->remove_on_change_unit;

            $role->save();

            try {
                $role->users()->attach($request->user()->id);
                $role->abilities()->sync($request->abilities);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return response()->json([
                    'type' => 'error',
                    'message' => 'Error when syncing abilities to the role.',
                ]);
            }
        } catch (\Throwable $e) {
            print_r($e);
            report($e);

            DB::rollback();

            return response()->json([
                'type' => 'error',
                'message' => 'Error on add this item.|Error on add the items.',
                'length' => 1,
            ]);
        }

        DB::commit();

        return response()->json([
            'type' => 'success',
            'title' => 'Add',
            'message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'length' => 1,
        ]);
    }

    public function patchUpdateRole(Request $request, Role $role): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('isActive', $role);
        $this->authorize('canEdit', $role);
        $this->authorize('canEditManagementRoles', $role);
        $this->authorize('isOwner', $role);

        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique(Role::class)->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
            'lock_on_expire' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'required_if:lock_on_expire,true'],
            'full_access' => ['boolean', 'accepted_if:manage_nested,true'],
            'manage_nested' => ['boolean'],
            'remove_on_change_unit' => ['boolean'],
        ], $messages = [
            // 'required' => 'The :attribute field is required.',
        ], $attributes = [
            // 'email' => 'email address',
        ],);

        DB::beginTransaction();

        try {
            $role->name = $request->name;
            $role->description = $request->description;
            $role->active = $request->active;
            $role->lock_on_expire = $request->lock_on_expire ? $request->lock_on_expire : false;
            $role->expires_at = $request->lock_on_expire ? $request->expires_at : null;
            $role->full_access = $request->full_access;
            $role->manage_nested = $request->manage_nested;
            $role->remove_on_change_unit = $request->remove_on_change_unit;

            $role->save();

            try {
                $role->abilities()->sync($request->abilities);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return response()->json([
                    'type' => 'error',
                    'message' => 'Error when syncing abilities to the role.',
                ]);
            }
        } catch (\Exception $e) {
            report($e);

            DB::rollback();

            return response()->json([
                'type' => 'error',
                'message' => 'Error on edit selected item.|Error on edit selected items.',
                'length' => 1,
            ]);
        }

        DB::commit();

        return response()->json([
            'type' => 'success',
            'title' => 'Edit',
            'message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'length' => 1,
        ]);
    }

    public function deleteDestroyRole(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            $total = Role::whereIn('id', $list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->delete();

            return response()->json([
                'type' => 'success',
                'title' => 'Remove',
                'message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
                'length' => $total,
                'replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($request->list),
            ]);
        }
    }

    public function postRestoreRole(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            $total = Role::whereIn('id', $list)->restore();

            return response()->json([
                'type' => 'success',
                'title' => 'Restore',
                'message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
                'length' => $total,
                'replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on restore selected item.|Error on restore selected items.',
                'length' => $request->list,
            ]);
        }
    }

    public function deleteForceDestroyRole(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isSuperAdmin', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            $total = Role::whereIn('id', $list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->forceDelete();

            return response()->json([
                'type' => 'success',
                'title' => 'Erase',
                'message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
                'length' => $total,
                'replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on erase selected item.|Error on erase selected items.',
                'length' => $request->list,
            ]);
        }
    }
}
