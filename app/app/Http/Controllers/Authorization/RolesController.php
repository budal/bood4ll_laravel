<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use App\Models\Ability;
use App\Models\Role;
use App\Models\User;
use Emargareten\InertiaModal\Modal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    public $title = 'Roles management';
    public $description = 'Define roles, grouping abilities to define specific access.';

    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $roles = Role::filter($request, 'roles')
            ->leftjoin('role_user', 'role_user.role_id', '=', 'roles.id')
            ->leftjoin('unit_user', 'unit_user.user_id', '=', 'role_user.user_id')
            ->select('roles.id', 'roles.name', 'roles.deleted_at')
            ->groupBy('roles.id', 'roles.name', 'roles.deleted_at')
            ->when(!$request->user()->isSuperAdmin(), function ($query) use ($request) {
                $query->where('roles.manager', false);

                if (!$request->user()->hasFullAccess()) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->withCount(['abilities', 'users' => function ($query) use ($request) {
                $query->when(!$request->user()->isSuperAdmin(), function ($query) use ($request) {
                    $query->leftjoin('unit_user', 'unit_user.user_id', '=', 'role_user.user_id');
                    $query->where('roles.manager', false);

                    if (!$request->user()->hasFullAccess()) {
                        $query->where('unit_user.user_id', $request->user()->id);
                    }

                    $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
                });
            }])
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'roles',
                    'title' => $this->title,
                    'subtitle' => $this->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'roles',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.roles.create',
                                            'showIf' => Gate::allows('apps.roles.create') && Gate::inspect('isManager', User::class)->allowed(),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.roles.edit',
                                            'showIf' => Gate::allows('apps.roles.edit'),
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.roles.destroy',
                                            'showIf' => Gate::allows('apps.roles.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.roles.forcedestroy',
                                            'showIf' => Gate::allows('apps.roles.forcedestroy') && Gate::inspect('isSuperAdmin', User::class)->allowed(),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.roles.restore',
                                            'showIf' => Gate::allows('apps.roles.restore'),
                                        ],
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:book-cog-outline',
                                            'title' => 'Abilities management',
                                            'route' => 'apps.abilities.index',
                                            'showIf' => Gate::inspect('isSuperAdmin', User::class)->allowed(),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'composite',
                                            'title' => 'Role',
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
                                            'title' => 'Abilities',
                                            'field' => 'abilities_count',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Users',
                                            'field' => 'users_count',
                                        ],
                                    ],
                                    'items' => $roles,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(Request $request, Role $role): array
    {
        $abilities = Ability::when(
            !$request->user()->isSuperAdmin(),
            function ($query) use ($request) {
                $query->whereIn('name', $request->user()->getAllAbilities->where('ability', '!=', null)->pluck('ability'));
            }
        )
            ->orderBy('name')
            ->get();

        $users = User::filter($request, 'users')
            ->leftjoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->when(!$request->all, function ($query) use ($role) {
                $query->join('role_user', 'role_user.user_id', '=', 'users.id');
                $query->where('role_user.role_id', $role->id);
            })
            ->when(!$request->user()->isSuperAdmin(), function ($query) use ($request) {
                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());

                if (!$request->user()->hasFullAccess()) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }
            })
            ->with('unitsClassified', 'unitsWorking')
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray())
            ->through(function ($item) use ($role) {
                $item->checked = $item->roles->pluck('id')->contains($role->id);

                return $item;
            });

        return [
            [
                'id' => 'role',
                'title' => 'Main data',
                'subtitle' => 'Role name, abilities and settings',
                'showIf' => $request->user()->isSuperAdmin() || $request->user()->isManager(),
                'disabledIf' => $role->inalterable == true
                    || $role->owner != $request->user()->id && !$request->user()->isSuperAdmin(),
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'description',
                            'title' => 'Description',
                            'span' => 2,
                        ],
                        [
                            'type' => 'select',
                            'name' => 'abilities',
                            'title' => 'Abilities',
                            'span' => 3,
                            'content' => $abilities,
                            'required' => true,
                            'multiple' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'lock_on_expire',
                            'title' => 'Lock on expire',
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'date',
                            'name' => 'expires_at',
                            'title' => 'Expires at',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'full_access',
                            'title' => 'Full access',
                            'disabled' => !$request->user()->isSuperAdmin() && !$request->user()->hasFullAccess(),
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'manage_nested',
                            'title' => 'Manage nested data',
                            'disabled' => !$request->user()->isSuperAdmin() && !$request->user()->canManageNested(),
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'remove_on_change_unit',
                            'title' => 'Remove on transfer',
                            'colorOn' => 'info',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'users',
                'title' => 'Authorizations',
                'subtitle' => 'Define which users will have access to this authorization',
                'showIf' => $role->id != null,
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'users',
                            'content' => [
                                'routes' => [
                                    'showCheckboxes' => true,
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus-circle-outline',
                                        'title' => 'Authorize',
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $role->id,
                                                'on',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => false,
                                        'modalTitle' => 'Are you sure you want to authorize the selected users?|Are you sure you want to authorize the selected users?',
                                        'modalSubTitle' => 'The selected user will have the rights to access this role. Do you want to continue?|The selected user will have the rights to access this role. Do you want to continue?',
                                        'buttonTitle' => 'Authorize',
                                        'buttonIcon' => 'mdi:plus-circle-outline',
                                        'buttonColor' => 'success',
                                    ],
                                    [
                                        'icon' => 'mdi:minus-circle-outline',
                                        'title' => 'Deauthorize',
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $role->id,
                                                'off',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => true,
                                        'modalTitle' => 'Are you sure you want to deauthorize the selected users?|Are you sure you want to deauthorize the selected users?',
                                        'modalSubTitle' => 'The selected user will lose the rights to access this role. Do you want to continue?|The selected users will lose the rights to access this role. Do you want to continue?',
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
                                                'title' => 'Authorized users',
                                                'route' => [
                                                    'route' => 'apps.roles.edit',
                                                    'attributes' => $role->id,
                                                ],
                                            ],
                                            [
                                                'icon' => 'mdi:account-multiple-outline',
                                                'title' => 'All users',
                                                'route' => [
                                                    'route' => 'apps.roles.edit',
                                                    'attributes' => [$role->id, 'all'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'composite',
                                        'title' => 'User',
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
                                        'title' => 'Classified',
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
                                        'title' => 'Working',
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
                                        'title' => 'Active',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $role->id,
                                                'toggle',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'colorOn' => 'info',
                                    ],
                                ],
                                'items' => $users,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function authorization(Request $request, Role $role, $mode): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', [$role, $request]);
        $this->authorize('allowedUnits', [$role, $request]);

        $hasRole = $role->users()->whereIn('user_id', $request->list)->first();

        try {
            if ($mode == 'toggle') {
                $user = User::whereIn('id', $request->list)->first();
                $hasRole ? $role->users()->detach($request->list) : $role->users()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $hasRole
                        ? "The user ':user' has been disabled in the ':role' role."
                        : "The user ':user' was enabled in the ':role' role.",
                    'toast_replacements' => ['user' => $user->name, 'role' => $role->name],
                ]);
            } elseif ($mode == 'on') {
                $total = $role->users()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nobody to authorize.|[1] User successfully authorized.|[2,*] :total users successfully authorized.',
                    'toast_count' => count($request->list),
                    'toast_replacements' => ['total' => count($request->list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $role->users()->detach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nobody to deauthorize.|[1] User successfully deauthorized.|[2,*] :total users successfully deauthorized.',
                    'toast_count' => $total,
                    'toast_replacements' => ['total' => $total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function create(Request $request, Role $role): Response
    {
        $this->authorize('isManager', User::class);
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $role),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.store'),
                    'method' => 'post',
                ],
            ],
        ]);
    }

    public function store(RolesRequest $request): RedirectResponse
    {
        $this->authorize('isManager', User::class);
        $this->authorize('access', User::class);

        $abilities = collect($request->abilities)->pluck('id');

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
                $role->abilities()->sync($abilities);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Error when syncing abilities to the role.',
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on add this item.|Error on add the items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::route('apps.roles.edit', $role->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Role $role): Response
    {
        $this->authorize('access', User::class);
        $this->authorize('isActive', $role);
        $this->authorize('canEdit', $role);

        $role['abilities'] = $role->abilities;

        return Inertia::render('Default', [
            'form' => $this->__form($request, $role),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch',
                    'reset' => true,
                    'fieldsToReset' => ['expires_at'],
                ],
            ],
            'data' => $role,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('isManager', User::class);
        $this->authorize('access', User::class);
        $this->authorize('isActive', $role);
        $this->authorize('isOwner', $role);
        $this->authorize('canEdit', $role);

        $abilities = collect($request->abilities)->pluck('id');

        DB::beginTransaction();

        try {
            if ($request->lock_on_expire && !$request->expires_at) {
                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Define the expiration date.',
                ]);
            }

            if ($request->manage_nested && !$request->full_access) {
                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => "It is impossible to manage nested data without enabling 'full access'.",
                ]);
            }

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
                $role->abilities()->sync($abilities);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Error when syncing abilities to the role.',
                ]);
            }
        } catch (\Exception $e) {
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
        $this->authorize('isManager', User::class);
        $this->authorize('access', User::class);
        $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        try {
            $total = Role::whereIn('id', $request->list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->delete();

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
        $this->authorize('isSuperAdmin', User::class);
        $this->authorize('access', User::class);

        try {
            $total = Role::whereIn('id', $request->list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->forceDelete();

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
                'toast_count' => $request->list,
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        $this->authorize('isManager', User::class);
        $this->authorize('access', User::class);
        $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        try {
            $total = Role::whereIn('id', $request->list)->restore();

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
                'toast_count' => $request->list,
            ]);
        }
    }

    public function __formModal(Request $request, Role $role): array
    {
        $abilities = Ability::sort('name')->get()->map->only(['id', 'name']);

        $users = User::paginate(5)
            ->onEachSide(1)
            ->appends($request->all('users_search', 'users_sorted', 'users_trashed'));

        return [
            [
                'id' => 'users',
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'users',
                            'span' => 2,
                            'content' => [
                                'menu' => [
                                    [
                                        'icon' => 'mdi:book-cog-outline',
                                        'title' => 'Abilities management',
                                        'route' => 'apps.abilities.index',
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Name',
                                        'field' => 'name',
                                    ],
                                ],
                                'items' => $users,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function adduser(Request $request, Role $role): Modal
    {
        $role['abilities'] = $role->getAllAbilities()->get()->map->only('id')->pluck('id');

        return Inertia::modal('Default', [
            'form' => $this->__formModal($request, $role),
            'isModal' => true,
            'title' => 'Define the users who have access to this authorization',
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $role,
        ])
            ->baseRoute('apps.roles.edit', $role)
            // ->refreshBackdrop()
        ;
    }
}
