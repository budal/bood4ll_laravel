<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{

    public function getUsersIndex(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);

        $users = User::where(function ($query) use ($request) {
            $query->where('name', 'ilike', "%$request->search%");
            $query->orWhere('email', 'ilike', "%$request->search%");
        })
            ->orderBy('name')
            ->with('unitsClassified', 'unitsWorking')
            ->withCount('roles')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->join('unit_user', 'unit_user.user_id', '=', 'users.id');
                $query->select('users.id', 'users.name', 'users.email');
                $query->groupBy('users.id', 'users.name', 'users.email');

                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->cursorPaginate(30)
            ->withQueryString();

        return response()->json($users);
    }

    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Bood4ll', [
            'build' => [
                [
                    'label' => Route::current()->title,
                    'description' => Route::current()->description,
                    'fields' => [
                        [
                            'type' => 'table',
                            'structure' => [
                                // 'menu' => [
                                //     [
                                //         'icon' => 'account_tree',
                                //         'label' => 'Abilities management',
                                //         'source' => 'getAbilitiesIndex',
                                //         'dialog' => true,
                                //         'visible' => $request->user()->can('isSuperAdmin', User::class),
                                //         'components' => [
                                //             [
                                //                 'label' => 'Abilities',
                                //                 'description' => 'Define which abilities will be showed in the roles management.',
                                //                 'visible' => (
                                //                     Gate::allows('apps.roles.update')
                                //                     && $request->user()->can('isManager', User::class)
                                //                     && $request->user()->can('canManageNestedData', User::class)
                                //                 ),
                                //                 'fields' => [
                                //                     [
                                //                         'type' => 'table',
                                //                         'name' => 'users',
                                //                         'structure' => [
                                //                             'actions' => [
                                //                                 'index' => [
                                //                                     'source' => 'getAbilitiesIndex',
                                //                                     'selectBoxes' => true,
                                //                                     'visible' => true,
                                //                                     'disabled' => true,
                                //                                 ],
                                //                             ],
                                //                             'menu' => [
                                //                                 [
                                //                                     'icon' => 'check',
                                //                                     'label' => 'Authorize',
                                //                                     'callback' => [
                                //                                         'route' => 'putAbilitiesUpdate',
                                //                                         'attributes' => ['mode' => 'on']
                                //                                     ],
                                //                                     'method' => 'put',
                                //                                     'visible' => $request->user()->can('canManageNestedData', User::class),
                                //                                     'condition' => ['checked' => false],
                                //                                     'badgeClass' => 'success',
                                //                                     'dialogTitle' => 'Are you sure you want to authorize the selected users?|Are you sure you want to authorize the selected users?',
                                //                                     'dialogSubTitle' => 'The selected user will have the rights to access this role. Do you want to continue?|The selected user will have the rights to access this role. Do you want to continue?',


                                //                                 ],
                                //                                 [
                                //                                     'icon' => 'close',
                                //                                     'label' => 'Deauthorize',
                                //                                     'callback' => [
                                //                                         'route' => 'putAbilitiesUpdate',
                                //                                         'attributes' => ['mode' => 'off']
                                //                                     ],
                                //                                     'method' => 'put',
                                //                                     'visible' => $request->user()->can('canManageNestedData', User::class),
                                //                                     'condition' => ['checked' => true],
                                //                                     'badgeClass' => 'danger',
                                //                                     'dialogTitle' => 'Are you sure you want to deauthorize the selected users?|Are you sure you want to deauthorize the selected users?',
                                //                                     'dialogSubTitle' => 'The selected user will lose the rights to access this role. Do you want to continue?|The selected users will lose the rights to access this role. Do you want to continue?',
                                //                                 ],
                                //                             ],
                                //                             'titles' => [
                                //                                 [
                                //                                     'type' => 'composite',
                                //                                     'header' => 'Ability',
                                //                                     'field' => 'title',
                                //                                     'values' => [
                                //                                         [
                                //                                             'field' => 'title',
                                //                                         ],
                                //                                         [
                                //                                             'field' => 'command',
                                //                                             'class' => 'text-xs',
                                //                                         ],
                                //                                     ],
                                //                                 ],
                                //                                 [
                                //                                     'type' => 'toggle',
                                //                                     'header' => 'Active',
                                //                                     'field' => 'checked',
                                //                                     'disableSort' => true,
                                //                                     'callback' => [
                                //                                         'route' => 'putAbilitiesUpdate',
                                //                                         'attributes' => ['mode' => 'toggle']
                                //                                     ],
                                //                                     'method' => 'put',
                                //                                     'colorOn' => 'success',
                                //                                     'colorOff' => 'danger',
                                //                                 ],
                                //                             ],
                                //                         ],
                                //                     ],
                                //                 ],
                                //             ],
                                //         ],
                                //     ],
                                // ],
                                'actions' => [
                                    'index' => [
                                        'source' => 'getUsersIndex',
                                        'visible' => Gate::allows('apps.users.index'),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                    ],
                                    'create' => [
                                        'visible' => (
                                            Gate::allows('apps.users.store')
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
                                                    Gate::allows('apps.users.store')
                                                    && $request->user()->can('isManager', User::class)
                                                ),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Add',
                                                'toast' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.users.store',
                                                'method' => 'post',
                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'visible' => (
                                            Gate::allows('apps.users.update')
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
                                                    Gate::allows('apps.users.update')
                                                    && $request->user()->can('isManager', User::class)
                                                    && $request->user()->can('canManageNestedData', User::class)
                                                ),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Edit',
                                                'toast' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.users.update',
                                                'method' => 'patch',
                                            ],
                                            [
                                                'label' => 'Authorized users',
                                                'description' => 'Define which users will have access to this authorization.',
                                                'visible' => (
                                                    Gate::allows('apps.users.update')
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
                                                                        'route' => 'apps.users.authorize',
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
                                                                        'route' => 'apps.users.authorize',
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
                                                                        'route' => 'apps.users.authorize',
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
                                        'callback' => 'apps.users.destroy',
                                        'method' => 'delete',
                                        'visible' => (
                                            Gate::allows('apps.users.destroy')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                    'restore' => [
                                        'callback' => 'apps.users.restore',
                                        'method' => 'post',
                                        'visible' => (
                                            Gate::allows('apps.users.restore')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                    'forceDestroy' => [
                                        'callback' => 'apps.users.forceDestroy',
                                        'method' => 'delete',
                                        'visible' => (
                                            Gate::allows('apps.users.forceDestroy')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'avatar',
                                        'header' => 'Avatar',
                                        'field' => 'id',
                                        'fallback' => 'name',
                                        'disableSort' => true,
                                    ],
                                    [
                                        'type' => 'composite',
                                        'header' => 'User',
                                        'field' => 'name',
                                        'values' => [
                                            [
                                                'field' => 'name',
                                            ],
                                            [
                                                'field' => 'email',
                                                'class' => 'text-xs',
                                            ],
                                        ],
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
                                        'type' => 'text',
                                        'header' => 'Roles',
                                        'class' => 'collapse',
                                        'field' => 'roles_count',
                                    ],
                                    [
                                        'type' => 'button',
                                        'header' => 'Login as',
                                        'theme' => 'warning',
                                        'showIf' => Gate::allows('apps.users.change_user') && !$request->session()->has('previousUser'),
                                        'icon' => 'mdi:account-convert',
                                        'disableSort' => true,
                                        'preserveScroll' => true,
                                        'route' => 'apps.users.change_user',
                                        'method' => 'post',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ]);


        // return Inertia::render('Default', [
        //     'form' => [
        //         [
        //             'id' => 'users',
        //             'title' => Route::current()->title,
        //             'subtitle' => Route::current()->description,
        //             'fields' => [
        //                 [
        //                     [
        //                         'type' => 'table',
        //                         'name' => 'users',
        //                         'content' => [
        //                             'routes' => [
        //                                 'createRoute' => [
        //                                     'route' => 'apps.users.create',
        //                                     'showIf' => Gate::allows('apps.users.create'),
        //                                 ],
        //                                 'editRoute' => [
        //                                     'route' => 'apps.users.edit',
        //                                     'showIf' => Gate::allows('apps.users.edit'),
        //                                 ],
        //                                 'destroyRoute' => [
        //                                     'route' => 'apps.users.destroy',
        //                                     'showIf' => Gate::allows('apps.users.destroy'),
        //                                 ],
        //                                 'forceDestroyRoute' => [
        //                                     'route' => 'apps.users.forcedestroy',
        //                                     'showIf' => Gate::allows('apps.users.forcedestroy'),
        //                                 ],
        //                                 'restoreRoute' => [
        //                                     'route' => 'apps.users.restore',
        //                                     'showIf' => Gate::allows('apps.users.restore'),
        //                                 ],
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ],
        // ]);
    }

    public function __fields(Request $request): array
    {
        return [];
    }

    public function activate(): RedirectResponse
    {
        return Redirect::back()->with('status', 'Error on edit selected item.|Error on edit selected items.');
    }

    public function changeUser(Request $request, User $user): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        if (!$request->session()->has('previousUser')) {
            $request->session()->put('previousUser', [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
            ]);

            Auth::loginUsingId($user->id, true);

            if ($user->getAllAbilities->whereNotNull('ability')->pluck('ability')->contains(Route::current()->getName())) {
                return Redirect::back()->with([
                    'toast_type' => 'warning',
                    'toast_message' => "Logged as ':user'.",
                    'toast_replacements' => ['user' => $user->name],
                ]);
            } else {
                return Redirect::route('dashboard.index')->with([
                    'toast_type' => 'warning',
                    'toast_message' => "Logged as ':user'.",
                    'toast_replacements' => ['user' => $user->name],
                ]);
            }
        } else {
            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'This action is unauthorized.',
            ]);
        }
    }

    public function returnToMyUser(Request $request): RedirectResponse
    {
        $previousUser = $request->session()->all()['previousUser'];

        $request->session()->put('previousUser', [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
        ]);

        Auth::loginUsingId($previousUser['id'], true);

        $request->session()->forget('previousUser');

        return Redirect::back()->with([
            'toast_type' => 'info',
            'toast_message' => "Logged as ':user'.",
            'toast_replacements' => ['user' => $previousUser['name']],
        ]);
    }

    public function authorizeRole(Request $request, User $user, $mode): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('fullAccess', [$user, $request]);

        $hasRole = $user->roles()->whereIn('roles.id', $request->list)->first();

        try {
            if ($mode == 'toggle') {
                $role = Role::whereIn('id', $request->list)->first();
                $hasRole ? $user->roles()->detach($request->list) : $user->roles()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $hasRole
                        ? "The user ':user' has been disabled in the ':role' role."
                        : "The user ':user' was enabled in the ':role' role.",
                    'toast_replacements' => ['user' => $user->name, 'role' => $role->name],
                ]);
            } elseif ($mode == 'on') {
                $total = $user->roles()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nothing to authorize.|[1] Item authorized successfully.|[2,*] :total items successfully authorized.',
                    'toast_count' => count($request->list),
                    'toast_replacements' => ['total' => count($request->list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $user->roles()->detach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nothing to deauthorize.|[1] Item deauthorized successfully.|[2,*] :total items successfully deauthorized.',
                    'toast_count' => $total,
                    'toast_replacements' => ['total' => $total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on authorize selected item.|Error on authorize selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function authorizeUnit(Request $request, User $user, $mode): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('fullAccess', [$user, $request]);

        $hasUnit = $user->units()->whereIn('units.id', $request->list)->first();

        try {
            if ($mode == 'toggle') {
                $unit = Unit::whereIn('id', $request->list)->first();
                $hasUnit ? $user->units()->detach($request->list) : $user->units()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $hasUnit
                        ? "The user ':user' was detached in the ':unit'."
                        : "The user ':user' was attached in the ':unit'.",
                    'toast_replacements' => ['user' => $user->name, 'unit' => $unit->name],
                ]);
            } elseif ($mode == 'on') {
                $total = $user->units()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nothing to attach.|[1] Item attached successfully.|[2,*] :total items successfully attached.',
                    'toast_count' => count($request->list),
                    'toast_replacements' => ['total' => count($request->list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $user->units()->detach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nothing to detach.|[1] Item detached successfully.|[2,*] :total items successfully detached.',
                    'toast_count' => $total,
                    'toast_replacements' => ['total' => $total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on attach selected item.|Error on attach selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function __form(Request $request, User $user): array
    {
        $units = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->filter($request, 'units', [
                'where' => ['shortpath'],
                'order' => ['shortpath'],
            ])
            ->select('units.id', 'units.parent_id', 'units.shortpath', 'units.active')
            ->groupBy('units.id', 'units.parent_id', 'units.shortpath', 'units.active')
            ->when(
                $request->show == 'all_units',
                function ($query) use ($request, $user) {
                    $query->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request, $user) {
                        $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
                    });
                },
                function ($query) use ($user) {
                    $query->where('unit_user.user_id', $user->id);
                }
            )
            ->paginate($perPage = 20, $columns = ['*'], $pageName = 'units')
            ->onEachSide(2)
            ->withQueryString()
            ->through(function ($item) use ($user) {
                $item->checked = $item->users->pluck('id')->contains($user->id);

                return $item;
            });

        $roles = Role::filter($request, 'roles')
            ->leftjoin('role_user', 'role_user.role_id', '=', 'roles.id')
            ->select('roles.id', 'roles.name')
            ->groupBy('roles.id', 'roles.name')
            ->when(
                $request->show == 'all_roles',
                function ($query) use ($request) {
                    $query->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                        $query->whereIn(
                            'role_user.role_id',
                            $request->user()
                                ->roles()
                                ->where('roles.active', true)
                                ->where('roles.superadmin', false)
                                ->where('roles.manager', false)
                                ->where(function ($query) {
                                    $query->where('roles.lock_on_expire', false);
                                    $query->orWhere(function ($query) {
                                        $query->where('roles.lock_on_expire', true);
                                        $query->where('roles.expires_at', '>=', 'NOW()');
                                    });
                                })
                                ->pluck('roles.id')
                        );
                    });
                },
                function ($query) use ($user) {
                    $query
                        ->where('roles.active', true)
                        ->where('roles.superadmin', false)
                        ->where('roles.manager', false)
                        ->where('role_user.user_id', $user->id);
                }
            )
            ->paginate($perPage = 20, $columns = ['*'], $pageName = 'roles')
            ->onEachSide(2)
            ->withQueryString()
            ->through(function ($item) use ($user) {
                $item->checked = $item->users->pluck('id')->contains($user->id);

                return $item;
            });

        return [
            [
                'id' => 'profile',
                'title' => 'Main data',
                'subtitle' => 'User account profile information.',
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'span' => 2,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'email',
                            'title' => 'Email',
                            'span' => 2,
                        ],
                        [
                            'type' => 'text',
                            'name' => 'email_verified_at',
                            'title' => 'Email verified at',
                            'readonly' => true,
                        ],
                    ],
                    [
                        [
                            'type' => 'modal',
                            'title' => 'Reset password',
                            'description' => "By clicking the button below, an email will be sent to the user to reset their password.",
                            'span' => 3,
                        ],
                    ]
                ]
            ],
            [
                'id' => 'units',
                'title' => 'Units',
                'subtitle' => "User units management.",
                'showIf' => $user->id != null && $request->user()->can('canManageNestedData', User::class),
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'units',
                            'content' => [
                                'routes' => [
                                    'showCheckboxes' => true,
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus-circle-outline',
                                        'title' => 'Attach',
                                        'route' => [
                                            'route' => 'apps.users.authorize_unit',
                                            'attributes' => [
                                                $user->id,
                                                'on',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => false,
                                        'modalTitle' => 'Are you sure you want to attach the selected item?|Are you sure you want to attach the selected items?',
                                        'modalSubTitle' => 'The selected item will be attached. Do you want to continue?|The selected items will be attached. Do you want to continue?',
                                        'buttonTitle' => 'Attach',
                                        'buttonIcon' => 'mdi:plus-circle-outline',
                                        'buttonColor' => 'success',
                                    ],
                                    [
                                        'icon' => 'mdi:minus-circle-outline',
                                        'title' => 'Detach',
                                        'route' => [
                                            'route' => 'apps.users.authorize_unit',
                                            'attributes' => [
                                                $user->id,
                                                'off',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => true,
                                        'modalTitle' => 'Are you sure you want to detach the selected item?|Are you sure you want to detach the selected items?',
                                        'modalSubTitle' => 'The selected item will be detached. Do you want to continue?|The selected items will be detached. Do you want to continue?',
                                        'buttonTitle' => 'Detach',
                                        'buttonIcon' => 'mdi:minus-circle-outline',
                                        'buttonColor' => 'danger',
                                    ],
                                    [
                                        'title' => '-',
                                    ],

                                    [
                                        'icon' => 'mdi:format-list-checkbox',
                                        'title' => 'List',
                                        'items' => [
                                            [
                                                'icon' => 'mdi:home-lock',
                                                'title' => 'Authorized units',
                                                'route' => [
                                                    'route' => 'apps.users.edit',
                                                    'attributes' => [
                                                        $user->id,
                                                    ]
                                                ],
                                            ],
                                            [
                                                'icon' => 'mdi:home-city',
                                                'title' => 'All units',
                                                'route' => [
                                                    'route' => 'apps.users.edit',
                                                    'attributes' => [
                                                        $user->id,
                                                        'all_units'
                                                    ]
                                                ],
                                                'showIf' => $request->user()->can('canManageNestedData', User::class)
                                            ],
                                        ],
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Name',
                                        'field' => 'shortpath',
                                    ],
                                    [
                                        'type' => 'toggle',
                                        'title' => '',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => 'apps.users.authorize_unit',
                                            'attributes' => [
                                                $user->id,
                                                'toggle',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'colorOn' => 'info',
                                    ],

                                ],
                                'items' => $units,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'roles',
                'title' => 'Roles',
                'subtitle' => "User roles management.",
                'showIf' => $user->id != null && $request->user()->can('canManageNestedData', User::class),
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'subunits',
                            'content' => [
                                'routes' => [
                                    'showCheckboxes' => true,
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus-circle-outline',
                                        'title' => 'Authorize',
                                        'route' => [
                                            'route' => 'apps.users.authorize_role',
                                            'attributes' => [
                                                $user->id,
                                                'on',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => false,
                                        'modalTitle' => 'Are you sure you want to authorize the selected item?|Are you sure you want to authorize the selected items?',
                                        'modalSubTitle' => 'The selected item will be authorized. Do you want to continue?|The selected items will be authorized. Do you want to continue?',
                                        'buttonTitle' => 'Authorize',
                                        'buttonIcon' => 'mdi:plus-circle-outline',
                                        'buttonColor' => 'success',
                                    ],
                                    [
                                        'icon' => 'mdi:minus-circle-outline',
                                        'title' => 'Deauthorize',
                                        'route' => [
                                            'route' => 'apps.users.authorize_role',
                                            'attributes' => [
                                                $user->id,
                                                'off',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => true,
                                        'modalTitle' => 'Are you sure you want to deauthorize the selected item?|Are you sure you want to deauthorize the selected items?',
                                        'modalSubTitle' => 'The selected item will be deauthorized. Do you want to continue?|The selected items will be deauthorized. Do you want to continue?',
                                        'buttonTitle' => 'Deauthorize',
                                        'buttonIcon' => 'mdi:minus-circle-outline',
                                        'buttonColor' => 'danger',
                                    ],
                                    [
                                        'title' => '-',
                                    ],

                                    [
                                        'icon' => 'mdi:format-list-checkbox',
                                        'title' => 'List',
                                        'items' => [
                                            [
                                                'icon' => 'mdi:account-key-outline',
                                                'title' => 'Authorized roles',
                                                'route' => [
                                                    'route' => 'apps.users.edit',
                                                    'attributes' => $user->id,
                                                ],
                                            ],
                                            [
                                                'icon' => 'mdi:account-multiple-outline',
                                                'title' => 'All roles',
                                                'route' => [
                                                    'route' => 'apps.users.edit',
                                                    'attributes' => [
                                                        $user->id,
                                                        'all_roles'
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Name',
                                        'field' => 'name',
                                    ],
                                    [
                                        'type' => 'toggle',
                                        'title' => '',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => 'apps.users.authorize_role',
                                            'attributes' => [
                                                $user->id,
                                                'toggle',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'colorOn' => 'info',
                                    ],
                                ],
                                'items' => $roles,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function create(Request $request, User $user): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $user),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.store'),
                    'method' => 'post',
                ],
            ],
        ]);
    }

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authorize('access', User::class);

        // dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        return Redirect::route('apps.users.index')->with([
            'toast_type' => 'success',
            'toast_message' => 'Item added.|Items added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $user),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.edit', $user->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        DB::beginTransaction();

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->active = $request->active;

            // $user->fill($request->validated());

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
        } catch (\Throwable $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::back()->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->delete();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on remove selected item.|Error on remove selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->forceDelete();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on erase selected item.|Error on erase selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->restore();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on restore selected item.|Error on restore selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }
}
