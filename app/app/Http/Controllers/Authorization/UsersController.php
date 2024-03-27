<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{

    public function getUsersIndex(Request $request): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.index']);

        $users = User::with('unitsClassified', 'unitsWorking')
            ->withCount('roles')
            ->where(function ($query) use ($request) {
                $query->where('name', 'ilike', "%$request->search%");
                $query->orWhere('email', 'ilike', "%$request->search%");
            })
            ->orderBy('name')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->join('unit_user', 'unit_user.user_id', '=', 'users.id');
                $query->select('users.id', 'users.name', 'users.email');
                $query->groupBy('users.id', 'users.name', 'users.email');

                if ($request->user()->cannot('hasFullAccess', [User::class, 'apps.users.index'])) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds('apps.users.index'));
            })
            ->when($request->listItems ?? null, function ($query, $listItems) {
                if ($listItems == 'both') {
                    $query->withTrashed();
                } elseif ($listItems == 'trashed') {
                    $query->onlyTrashed();
                }
            })
            ->cursorPaginate(30)
            ->withQueryString();

        return response()->json($users);
    }

    public function getUserInfo(User $user): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.update', $user]);

        return response()->json($user);
    }

    public function postUserStore(Request $request): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.store']);

        // $request->validate([
        //     'name' => ['required', 'string', 'max:100', Rule::unique(Role::class)],
        //     'description' => ['nullable', 'string', 'max:255'],
        //     'active' => ['boolean'],
        //     'lock_on_expire' => ['boolean'],
        //     'expires_at' => ['nullable', 'date', 'required_if:lock_on_expire,true'],
        //     'full_access' => ['boolean', 'accepted_if:manage_nested,true'],
        //     'manage_nested' => ['boolean'],
        //     'remove_on_change_unit' => ['boolean'],
        // ], $messages = [
        //     // 'required' => 'The :attribute field is required.',
        // ], $attributes = [
        //     // 'email' => 'email address',
        // ],);

        // DB::beginTransaction();

        // try {
        //     $role = new Role();

        //     $role->name = $request->name;
        //     $role->description = $request->description;
        //     $role->owner = $request->user()->id;
        //     $role->active = $request->active ?? false;
        //     $role->lock_on_expire = $request->lock_on_expire ?? false;
        //     $role->expires_at = $request->expires_at;
        //     $role->full_access = $request->full_access ?? false;
        //     $role->manage_nested = $request->manage_nested ?? false;
        //     $role->remove_on_change_unit = $request->remove_on_change_unit ?? false;

        //     $role->save();

        //     $abilities = collect($request->abilities)->pluck('id');

        //     try {
        //         if ($request->user()->cannot('isSuperAdmin', User::class)) {
        //             $role->users()->attach($request->user()->id);
        //         }

        //         $role->abilities()->sync($abilities);
        //     } catch (\Exception $e) {
        //         report($e);

        //         DB::rollback();

        //         return response()->json([
        //             'type' => 'error',
        //             'message' => 'Error when syncing abilities to the role.',
        //         ]);
        //     }
        // } catch (\Throwable $e) {
        //     report($e);

        //     DB::rollback();

        //     return response()->json([
        //         'type' => 'error',
        //         'message' => 'Error on add this item.|Error on add the items.',
        //         'length' => 1,
        //     ]);
        // }

        // DB::commit();

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'length' => 1,
        ]);
    }

    public function patchUserUpdate(Request $request, User $user): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.update', $user]);

        // $this->authorize('access', User::class);
        // $this->authorize('isManager', User::class);
        // $this->authorize('isActive', $role);
        // $this->authorize('canEdit', $role);
        // $this->authorize('canEditManagementRoles', $role);
        // $this->authorize('isOwner', $role);

        // $request->validate([
        //     'name' => ['required', 'string', 'max:100', Rule::unique(Role::class)->ignore($role->id)],
        //     'description' => ['nullable', 'string', 'max:255'],
        //     'active' => ['boolean'],
        //     'lock_on_expire' => ['boolean'],
        //     'expires_at' => ['nullable', 'date', 'required_if:lock_on_expire,true'],
        //     'full_access' => ['boolean', 'accepted_if:manage_nested,true'],
        //     'manage_nested' => ['boolean'],
        //     'remove_on_change_unit' => ['boolean'],
        // ], $messages = [
        //     // 'required' => 'The :attribute field is required.',
        // ], $attributes = [
        //     // 'email' => 'email address',
        // ],);

        // DB::beginTransaction();

        // try {
        //     $role->name = $request->name;
        //     $role->description = $request->description;
        //     $role->active = $request->active;
        //     $role->lock_on_expire = $request->lock_on_expire ? $request->lock_on_expire : false;
        //     $role->expires_at = $request->lock_on_expire ? $request->expires_at : null;
        //     $role->full_access = $request->full_access;
        //     $role->manage_nested = $request->manage_nested;
        //     $role->remove_on_change_unit = $request->remove_on_change_unit;

        //     $role->save();

        //     $abilities = collect($request->abilities)->pluck('id');

        //     try {
        //         $role->abilities()->sync($abilities);
        //     } catch (\Exception $e) {
        //         report($e);

        //         DB::rollback();

        //         return response()->json([
        //             'type' => 'error',
        //             'message' => 'Error when syncing abilities to the role.',
        //         ]);
        //     }
        // } catch (\Exception $e) {
        //     report($e);

        //     DB::rollback();

        //     return response()->json([
        //         'type' => 'error',
        //         'message' => 'Error on edit selected item.|Error on edit selected items.',
        //         'length' => 1,
        //     ]);
        // }

        // DB::commit();

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'length' => 1,
        ]);
    }

    public function getUserUnits(Request $request, User $user, string $show = null): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.authorizeUnit', $user]);

        $units = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->select('units.id', 'units.parent_id', 'units.shortpath', 'units.active')
            ->groupBy('units.id', 'units.parent_id', 'units.shortpath', 'units.active')
            ->where('shortpath', 'ilike', "%$request->search%")
            ->orderBy('shortpath')
            ->when(
                $show == 'all',
                function ($query) use ($request, $user) {
                    $query->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request, $user) {
                        $query->whereIn('unit_user.unit_id', $request->user()->unitsIds('apps.users.authorizeUnit'));
                    });
                },
                function ($query) use ($user) {
                    $query->where('unit_user.user_id', $user->id);
                }
            )
            ->cursorPaginate(30)
            ->withQueryString()
            ->through(function ($item) use ($user) {
                $item->checked = $item->users->pluck('id')->contains($user->id);

                return $item;
            });

        return response()->json($units);
    }

    public function putAuthorizeUnit(Request $request, User $user, string $mode = null): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.authorizeUnit', $user]);

        $list = collect($request->list)->pluck('id');

        try {
            if ($mode == 'toggle') {
                $unit = Unit::whereIn('id', $list)->first();
                $hasUnit = $user->units()->whereIn('units.id', $list)->first();
                $hasUnit ? $user->units()->detach($list) : $user->units()->attach($list);

                return response()->json([
                    'type' => 'success',
                    'deactivate' => $hasUnit == true ? true : false,
                    'title' => $hasUnit ? 'Attach' : 'Authorize',
                    'message' => $hasUnit
                        ? "The user ':user' was detached in the unit ':unit'."
                        : "The user ':user' was attached in the unit ':unit'.",
                    'length' => 1,
                    'replacements' => ['user' => $user->name, 'unit' => $unit->name],
                ]);
            } elseif ($mode == 'on') {
                $user->units()->attach($list);

                return response()->json([
                    'type' => 'success',
                    'title' => 'Attach',
                    'message' => '{0} Nothing to attach.|[1] Item attached successfully.|[2,*] :total items successfully attached.',
                    'length' => count($list),
                    'replacements' => ['total' => count($list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $user->units()->detach($list);

                return response()->json([
                    'type' => 'success',
                    'title' => 'Detach',
                    'message' => '{0} Nothing to detach.|[1] Item detached successfully.|[2,*] :total items successfully detached.',
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

    public function getUserRoles(Request $request, User $user, string $show = null): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.authorizeRole', $user]);

        $roles = Role::leftjoin('role_user', 'role_user.role_id', '=', 'roles.id')
            ->select('roles.id', 'roles.name')
            ->groupBy('roles.id', 'roles.name')
            ->where('name', 'ilike', "%$request->search%")
            ->orderBy('name')
            ->when(
                $show == 'all',
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
            ->cursorPaginate(30)
            ->withQueryString()
            ->through(function ($item) use ($user) {
                $item->checked = $item->users->pluck('id')->contains($user->id);

                return $item;
            });

        return response()->json($roles);
    }

    public function putAuthorizeRole(Request $request, User $user, string $mode = null): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.authorizeRole', $user]);

        $list = collect($request->list)->pluck('id');

        try {
            if ($mode == 'toggle') {
                $role = Role::whereIn('id', $list)->first();
                $hasRole = $user->roles()->whereIn('roles.id', $list)->first();
                $hasRole ? $user->roles()->detach($list) : $user->roles()->attach($list);

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
            } elseif ($mode == 'on') {
                $user->roles()->attach($list);

                return response()->json([
                    'type' => 'success',
                    'title' => 'Authorize',
                    'message' => '{0} Nothing to authorize.|[1] Item authorized successfully.|[2,*] :total items successfully authorized.',
                    'length' => count($list),
                    'replacements' => ['total' => count($list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $user->roles()->detach($list);

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
                'length' => count($list),
            ]);
        }
    }



    public function changeUser(Request $request, User $user): JsonResponse
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

            if ($user->getAbilities->pluck('ability')->contains(Route::current()->getName())) {
                return response()->json([
                    'type' => 'warning',
                    'title' => 'Toggle user',
                    'message' => "Logged as ':user'.",
                    'replacements' => ['user' => $user->name],
                ]);
            } else {
                return response()->json([
                    'type' => 'warning',
                    'title' => 'Toggle user',
                    'message' => "Logged as ':user'.",
                    'replacements' => ['user' => $user->name],
                ]);
            }
        } else {
            return response()->json([
                'type' => 'error',
                'title' => 'Error',
                'message' => 'This action is unauthorized.',
            ]);
        }
    }

    public function returnToMyUser(Request $request): JsonResponse
    {
        $previousUser = $request->session()->all()['previousUser'];

        $request->session()->put('previousUser', [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
        ]);

        Auth::loginUsingId($previousUser['id'], true);

        $request->session()->forget('previousUser');

        return response()->json([
            'type' => 'info',
            'title' => 'Toggle user',
            'message' => "Logged as ':user'.",
            'replacements' => ['user' => $previousUser['name']],
        ]);
    }



    public function deleteUsersDestroy(Request $request): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.destroy']);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            $total = User::whereIn('id', $list)->delete();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($list),
            ]);
        }

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
            'length' => $total,
            'replacements' => ['total' => $total],
        ]);
    }

    public function postUsersRestore(Request $request): JsonResponse
    {
        // $this->authorize('access', [User::class, 'apps.users.restore']);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            $total = User::whereIn('id', $list)->restore();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($list),
            ]);
        }

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
            'length' => $total,
            'replacements' => ['total' => $total],
        ]);
    }

    public function deleteUsersForceDestroy(Request $request): JsonResponse
    {
        $this->authorize('access', [User::class, 'apps.users.forceDestroy']);
        $this->authorize('isSuperAdmin', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            $total = User::whereIn('id', $list)->forceDelete();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on erase selected item.|Error on erase selected items.',
                'length' => $list,
            ]);
        }

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
            'length' => $total,
            'replacements' => ['total' => $total],
        ]);
    }

    public function __fields(Request $request): array
    {
        return [
            [
                'type' => 'input',
                'name' => 'name',
                'label' => 'Name',
                'span' => 2,
            ],
            [
                'type' => 'toggle',
                'name' => 'active',
                'label' => 'Active',
                'colorOn' => 'success',
                'colorOff' => 'danger',
            ],
            [
                'type' => 'input',
                'name' => 'email',
                'label' => 'Email',
                'span' => 2,
            ],
            [
                'type' => 'text',
                'name' => 'email_verified_at',
                'label' => 'Email verified at',
                'readonly' => true,
            ],
        ];
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
                                'actions' => [
                                    'index' => [
                                        'source' => 'getUsersIndex',
                                        'visible' => $request->user()->can('access', User::class),
                                    ],
                                    'create' => [
                                        'visible' => $request->user()->can('access', [User::class, 'apps.users.store']),
                                        'components' => [
                                            [
                                                'label' => 'Main data',
                                                'description' => 'User account profile information.',
                                                'cols' => 3,
                                                'fields' => $this->__fields($request),
                                                'visible' => $request->user()->can('access', [User::class, 'apps.users.store']),
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
                                        'visible' => $request->user()->can('access', [User::class, 'apps.users.update']),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                        'components' => [
                                            [
                                                'source' => [
                                                    'route' => 'getUserInfo',
                                                    'transmute' => ['user' => 'id'],
                                                ],
                                                'label' => 'Main data',
                                                'description' => 'User account profile information.',
                                                'cols' => 3,
                                                'fields' => $this->__fields($request),
                                                'visible' => $request->user()->can('access', [User::class, 'apps.users.update']),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Edit',
                                                'toast' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.users.update',
                                                'method' => 'patch',
                                            ],
                                            [
                                                'label' => 'Units',
                                                'description' => 'User units management.',
                                                'visible' => $request->user()->can('access', [User::class, 'apps.users.authorizeUnit']),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'fields' => [
                                                    [
                                                        'type' => 'table',
                                                        'name' => 'users',
                                                        'structure' => [
                                                            'actions' => [
                                                                'index' => [
                                                                    'source' => [
                                                                        'route' => 'getUserUnits',
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                    'selectBoxes' => true,
                                                                ],
                                                            ],
                                                            'menu' => [
                                                                [
                                                                    'icon' => 'check',
                                                                    'label' => 'Authorize',
                                                                    'callback' => [
                                                                        'route' => 'apps.users.authorizeUnit',
                                                                        'attributes' => ['mode' => 'on'],
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'condition' => ['checked' => false],
                                                                    'badgeClass' => 'success',
                                                                ],
                                                                [
                                                                    'icon' => 'close',
                                                                    'label' => 'Deauthorize',
                                                                    'callback' => [
                                                                        'route' => 'apps.users.authorizeUnit',
                                                                        'attributes' => ['mode' => 'off'],
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'condition' => ['checked' => true],
                                                                    'badgeClass' => 'danger',
                                                                ],
                                                                [
                                                                    'separator' => true,
                                                                ],
                                                                [
                                                                    'icon' => 'house_with_shield',
                                                                    'label' => 'Authorized units',
                                                                    'source' => [
                                                                        'route' => 'getUserUnits',
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                ],
                                                                [
                                                                    'icon' => 'other_houses',
                                                                    'label' => 'All units',
                                                                    'source' => [
                                                                        'route' => 'getUserUnits',
                                                                        'attributes' => ['show' => 'all'],
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                ],
                                                            ],
                                                            'titles' => [
                                                                [
                                                                    'type' => 'text',
                                                                    'header' => 'Name',
                                                                    'field' => 'shortpath',
                                                                ],
                                                                [
                                                                    'type' => 'toggle',
                                                                    'header' => 'Active',
                                                                    'field' => 'checked',
                                                                    'disableSort' => true,
                                                                    'callback' => [
                                                                        'route' => 'apps.users.authorizeUnit',
                                                                        'attributes' => ['mode' => 'toggle'],
                                                                        'transmute' => ['user' => 'id'],
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
                                            [
                                                'label' => 'Roles',
                                                'description' => 'User roles management.',
                                                'visible' => $request->user()->can('access', [User::class, 'apps.users.authorizeRole']),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'fields' => [
                                                    [
                                                        'type' => 'table',
                                                        'name' => 'users',
                                                        'structure' => [
                                                            'actions' => [
                                                                'index' => [
                                                                    'source' => [
                                                                        'route' => 'getUserRoles',
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                    'selectBoxes' => true,
                                                                ],
                                                            ],
                                                            'menu' => [
                                                                [
                                                                    'icon' => 'check',
                                                                    'label' => 'Authorize',
                                                                    'callback' => [
                                                                        'route' => 'apps.users.authorizeRole',
                                                                        'attributes' => ['mode' => 'on'],
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'condition' => ['checked' => false],
                                                                    'badgeClass' => 'success',
                                                                ],
                                                                [
                                                                    'icon' => 'close',
                                                                    'label' => 'Deauthorize',
                                                                    'callback' => [
                                                                        'route' => 'apps.users.authorizeRole',
                                                                        'attributes' => ['mode' => 'off'],
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                    'method' => 'put',
                                                                    'condition' => ['checked' => true],
                                                                    'badgeClass' => 'danger',
                                                                ],
                                                                [
                                                                    'separator' => true,
                                                                ],
                                                                [
                                                                    'icon' => 'badge',
                                                                    'label' => 'Authorized roles',
                                                                    'source' => [
                                                                        'route' => 'getUserRoles',
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                ],
                                                                [
                                                                    'icon' => 'list_alt',
                                                                    'label' => 'All roles',
                                                                    'source' => [
                                                                        'route' => 'getUserRoles',
                                                                        'attributes' => ['show' => 'all'],
                                                                        'transmute' => ['user' => 'id'],
                                                                    ],
                                                                ],
                                                            ],
                                                            'titles' => [
                                                                [
                                                                    'type' => 'text',
                                                                    'header' => 'Name',
                                                                    'field' => 'name',
                                                                ],
                                                                [
                                                                    'type' => 'toggle',
                                                                    'header' => 'Active',
                                                                    'field' => 'checked',
                                                                    'disableSort' => true,
                                                                    'callback' => [
                                                                        'route' => 'apps.users.authorizeRole',
                                                                        'attributes' => ['mode' => 'toggle'],
                                                                        'transmute' => ['user' => 'id'],
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
                                        // 'visible' => (
                                        //     Gate::allows('apps.users.destroy')
                                        //     && $request->user()->can('isManager', User::class)
                                        //     && $request->user()->can('canManageNestedData', User::class)
                                        // ),
                                    ],
                                    'restore' => [
                                        'callback' => 'apps.users.restore',
                                        'method' => 'post',
                                        // 'visible' => (
                                        //     Gate::allows('apps.users.restore')
                                        //     && $request->user()->can('isManager', User::class)
                                        //     && $request->user()->can('canManageNestedData', User::class)
                                        // ),
                                    ],
                                    'forceDestroy' => [
                                        'callback' => 'apps.users.forceDestroy',
                                        'method' => 'delete',
                                        // 'visible' => (
                                        //     Gate::allows('apps.users.forceDestroy')
                                        //     && $request->user()->can('isManager', User::class)
                                        //     && $request->user()->can('canManageNestedData', User::class)
                                        // ),
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
    }
}
